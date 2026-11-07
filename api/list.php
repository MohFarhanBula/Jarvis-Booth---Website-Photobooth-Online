<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
$limit = min(60, max(1, intval($_GET['limit'] ?? 24)));
$sort = ($_GET['sort'] ?? 'baru') === 'top' ? 'likes DESC, id DESC' : 'id DESC';
$out = [];
if (db_ok()) {
  global $conn;
  // Hanya foto publik yang tampil di galeri. Foto privat tetap bisa dibuka via link langsung.
  $has_pub = false;
  try { $r=$conn->query("SHOW COLUMNS FROM photos LIKE 'is_public'"); $has_pub = $r && $r->num_rows>0; } catch(Throwable $e){}
  $where = $has_pub ? "WHERE is_public=1" : "";
  $res = $conn->query("SELECT code,image_path,template,caption,likes,created_at FROM photos $where ORDER BY $sort LIMIT $limit");
  if ($res) while ($r = $res->fetch_assoc()) $out[] = $r;
} else {
  // DB mati: JANGAN fallback scan folder (bisa membocorkan foto privat). Kembalikan kosong.
  $out = [];
}
echo json_encode(['ok'=>true,'data'=>$out]);
