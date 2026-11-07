<?php require_once 'auth.php'; admin_require(); if(!admin_is_super()){ die('Khusus superadmin!'); } require_once 'layout.php';
global $conn; $msg='';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add'])) {
  $u=trim($_POST['username']??''); $p=$_POST['password']??''; $role=$_POST['role']??'admin'; if(!in_array($role,['admin','superadmin'],true))$role='admin';
  if($u && $p){ $h=password_hash($p,PASSWORD_DEFAULT); $st=$conn->prepare("INSERT INTO users (username,password_hash,role) VALUES (?,?,?)"); $st->bind_param("sss",$u,$h,$role); if($st->execute()) flash_set('Admin baru ditambahkan.'); else flash_set('Username sudah dipakai.', 'danger'); $st->close(); header('Location: users.php'); exit; }
}
if(isset($_GET['reset'])){ $id=intval($_GET['reset']); $h=password_hash('jarvis123',PASSWORD_DEFAULT); $conn->query("UPDATE users SET password_hash='$h' WHERE id=$id"); flash_set('Password direset menjadi jarvis123.'); header('Location: users.php'); exit; }
if(isset($_GET['del'])){ $id=intval($_GET['del']); if($id!==$_SESSION['admin_id']) $conn->query("DELETE FROM users WHERE id=$id"); header('Location: users.php'); exit; }
$list=$conn->query("SELECT id,username,role,created_at FROM users ORDER BY id");
layout_head('Users', 'users', 'Kelola akun admin. Hanya superadmin.');
?>
<div class="d-sm-flex align-items-center justify-content-between mb-3"><h1 class="h3 mb-0 text-gray-800">Users</h1></div>
<div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Tambah Admin</h6></div>
<div class="card-body"><form method="POST" class="row g-2">
<input name="username" placeholder="Username baru" class="form-control col" style="max-width:200px" required>
<input name="password" placeholder="Password" class="form-control col" style="max-width:200px" required>
<select name="role" class="form-select" style="max-width:160px"><option>admin</option><option>superadmin</option></select>
<button name="add" class="btn btn-primary btn-sm col-auto">Tambah</button></form></div></div>
<div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Daftar Admin</h6></div>
<div class="card-body p-0"><div class="table-responsive"><table class="table table-bordered table-hover mb-0" id="admtbl" data-enhance><thead class="table-light"><tr><th>Username</th><th>Role</th><th>Dibuat</th><th>Aksi</th></tr></thead><tbody>
<?php while($r=$list->fetch_assoc()): ?><tr><td><b><?= htmlspecialchars($r['username']) ?></b></td><td><span class="badge <?= $r['role']==='superadmin'?'bg-dark':'bg-info text-dark' ?>"><?= $r['role'] ?></span></td><td><small class="text-muted"><?= $r['created_at'] ?></small></td><td><?php if($r['id']!=$_SESSION['admin_id']): ?><a class="btn btn-sm btn-outline-secondary" href="?reset=<?= $r['id'] ?>" onclick="return confirm('Reset ke jarvis123?')">Reset PW</a> <a class="btn btn-sm btn-outline-danger" href="?del=<?= $r['id'] ?>" onclick="return confirm('Hapus?')">Hapus</a><?php else: ?><small class="text-muted">Akun Anda</small><?php endif; ?></td></tr><?php endwhile; ?>
</tbody></table></div></div></div>
<?php layout_foot(); ?>
