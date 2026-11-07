<?php require_once 'auth.php'; admin_require(); require_once 'layout.php';
global $conn;
if (isset($_GET['delphoto'])) { $c=$conn->real_escape_string($_GET['delphoto']); $r=$conn->query("SELECT image_path FROM photos WHERE code='$c' LIMIT 1"); if($r&&$row=$r->fetch_assoc()){ @unlink(__DIR__.'/../'.$row['image_path']); } $conn->query("DELETE FROM photos WHERE code='$c'"); header('Location: photos.php'); exit; }
if (isset($_GET['delroom'])) { $c=$conn->real_escape_string($_GET['delroom']); $conn->query("DELETE FROM rooms WHERE code='$c'"); header('Location: photos.php'); exit; }
if (isset($_GET['vis'])) {
  $c=$conn->real_escape_string($_GET['vis']); $v = ($_GET['v'] ?? '0')==='1' ? 1 : 0;
  $conn->query("UPDATE photos SET is_public=$v WHERE code='$c'");
  header('Location: photos.php'); exit;
}
$filter = $_GET['f'] ?? 'all';
if (!in_array($filter, ['all','pub','priv'], true)) $filter = 'all';
$where = $filter==='pub' ? "WHERE is_public=1" : ($filter==='priv' ? "WHERE is_public=0" : "");
$photos = $conn->query("SELECT code, image_path, template, caption, likes, is_public, created_at FROM photos $where ORDER BY id DESC LIMIT 60");
$rooms = $conn->query("SELECT r.code, r.mode, r.status, r.created_at, COUNT(m.id) jml FROM rooms r LEFT JOIN room_members m ON m.room_code=r.code GROUP BY r.code ORDER BY r.created_at DESC LIMIT 30");
layout_head('Foto & Room', 'photos', 'Moderasi galeri dan room LDR');
?>
<div class="d-sm-flex align-items-center justify-content-between mb-3"><h1 class="h3 mb-0 text-gray-800">Foto & Room</h1></div>
<div class="card shadow mb-4"><div class="card-header py-3 d-flex gap-2 align-items-center flex-wrap"><h6 class="m-0 fw-bold text-primary">Galeri (admin lihat semua)</h6><input id="pq" class="form-control form-control-sm ms-auto" style="max-width:200px" placeholder="Cari caption/kode...">
<div class="btn-group btn-group-sm"><a href="?f=all" class="btn <?= $filter==='all'?'btn-primary':'btn-outline-primary' ?>">Semua</a><a href="?f=pub" class="btn <?= $filter==='pub'?'btn-primary':'btn-outline-primary' ?>">🌍 Publik</a><a href="?f=priv" class="btn <?= $filter==='priv'?'btn-primary':'btn-outline-primary' ?>">🔒 Privat</a></div></div>
<div class="card-body"><div class="row g-3" id="pg">
<?php while($p=$photos->fetch_assoc()): ?>
<div class="col-6 col-md-3" data-name="<?= htmlspecialchars(strtolower($p['caption'].' '.$p['code'])) ?>">
<div class="card h-100"><div class="position-relative"><img src="../<?= htmlspecialchars($p['image_path']) ?>" loading="lazy" onclick="admLightbox(this.src)" class="card-img-top" style="aspect-ratio:3/4;object-fit:cover;cursor:zoom-in"><span class="badge position-absolute top-0 start-0 m-1 <?= intval($p['is_public'])?'bg-success':'bg-dark' ?>"><?= intval($p['is_public'])?'🌍':'🔒' ?></span></div>
<div class="card-body p-2"><h6 class="card-title mb-0 small"><?= htmlspecialchars(mb_strimwidth($p['caption']?:$p['code'],0,24,'…')) ?></h6><small class="text-muted">❤️ <?= $p['likes'] ?> • <?= htmlspecialchars($p['created_at']) ?></small>
<div class="mt-1 d-flex gap-1"><a href="?vis=<?= $p['code'] ?>&v=<?= intval($p['is_public'])?'0':'1' ?>&f=<?= $filter ?>" class="btn btn-sm btn-outline-secondary" title="Toggle privasi"><?= intval($p['is_public'])?'🔒 Privat':'🌍 Publik' ?></a><a href="?delphoto=<?= $p['code'] ?>" onclick="return confirm('Hapus permanen?')" class="btn btn-sm btn-outline-danger">Hapus</a></div></div></div></div>
<?php endwhile; ?></div></div></div>
<div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Room LDR</h6></div>
<div class="card-body p-0"><div class="table-responsive"><table class="table table-bordered table-hover mb-0" id="admtbl" data-enhance data-per="10"><thead class="table-light"><tr><th>Kode</th><th>Mode</th><th>Anggota</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php while($r=$rooms->fetch_assoc()): ?><tr><td><b><?= $r['code'] ?></b><br><small class="text-muted"><?= $r['created_at'] ?></small></td><td><?= $r['mode'] ?></td><td><?= $r['jml'] ?> orang</td><td><span class="badge bg-info text-dark"><?= $r['status'] ?></span></td><td><a href="?delroom=<?= $r['code'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tutup room?')">Tutup</a></td></tr><?php endwhile; ?>
</tbody></table></div></div></div>
<script>
document.getElementById('pq').addEventListener('input', e=>{
  const q=e.target.value.toLowerCase();
  document.querySelectorAll('#pg > div').forEach(d=>d.style.display=!q||d.dataset.name.includes(q)?'':'none');
});
</script>
<?php layout_foot(); ?>
