<?php require_once 'auth.php'; admin_require(); require_once 'layout.php';
global $conn;
if (isset($_GET['expire'])) { $c=$conn->real_escape_string($_GET['expire']); $conn->query("UPDATE sessions SET status='expired' WHERE code='$c'"); $conn->query("UPDATE rooms SET status='expired' WHERE code='$c'"); header('Location: sessions.php'); exit; }
$list = $conn->query("SELECT s.*, u.nama AS owner FROM sessions s LEFT JOIN users u ON u.id=s.owner_id ORDER BY s.id DESC LIMIT 100");
layout_head('Sessions', 'sessions', 'Sesi foto berbayar 5rb/10 menit');
?>
<div class="d-sm-flex align-items-center justify-content-between mb-3"><h1 class="h3 mb-0 text-gray-800">Sessions</h1></div>
<div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Daftar Sesi</h6></div>
<div class="card-body p-0"><div class="table-responsive"><table class="table table-bordered table-hover mb-0" id="admtbl" data-enhance><thead class="table-light"><tr><th>Kode</th><th>Owner</th><th>Mode/Ori</th><th>Status</th><th>Expire</th><th>Aksi</th></tr></thead><tbody>
<?php while($r=$list->fetch_assoc()): ?><tr>
<td><b><?= htmlspecialchars($r['code']) ?></b><br><small class="text-muted"><?= htmlspecialchars($r['created_at']) ?></small></td>
<td><?= htmlspecialchars($r['owner']??'-') ?></td>
<td><?= htmlspecialchars($r['mode']) ?> / <?= htmlspecialchars($r['orientation']) ?></td>
<td><span class="badge <?= $r['status']==='active'?'bg-success':($r['status']==='expired'?'bg-secondary':'bg-warning text-dark') ?>"><?= $r['status'] ?></span></td>
<td><small class="text-muted"><?= htmlspecialchars($r['expires_at']??'-') ?></small></td>
<td><a class="btn btn-sm btn-outline-danger" href="?expire=<?= $r['code'] ?>" onclick="return confirm('Expire-kan sesi ini?')">Expire</a></td>
</tr><?php endwhile; ?>
</tbody></table></div></div></div>
<?php layout_foot(); ?>
