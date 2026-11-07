<?php
// Riwayat sesi + transaksi milik user yang login.
require_once __DIR__ . '/../includes/auth_user.php';
header('Content-Type: application/json');
$user = user_current();
if (!$user || !db_ok()) { http_response_code(401); echo json_encode(['ok'=>false]); exit; }
global $conn;
$uid = intval($user['id']);
$res = $conn->query("SELECT code,orientation,mode,status,paid_at,expires_at,TIMESTAMPDIFF(SECOND,NOW(),expires_at) AS sisa,created_at FROM sessions WHERE owner_id=$uid ORDER BY id DESC LIMIT 20");
$out = [];
if ($res) while ($s = $res->fetch_assoc()) {
  if ($s['status']==='active' && intval($s['sisa'] ?? 0) <= 0) {
    $c = $conn->real_escape_string($s['code']);
    $conn->query("UPDATE sessions SET status='expired' WHERE code='$c'");
    $conn->query("UPDATE rooms SET status='expired' WHERE code='$c'");
    $s['status']='expired'; $s['sisa']=0;
  }
  $c = $conn->real_escape_string($s['code']);
  $o = null;
  $ro = $conn->query("SELECT merchant_ref,amount,channel,status FROM orders WHERE session_code='$c' ORDER BY id DESC LIMIT 1");
  if ($ro) $o = $ro->fetch_assoc();
  $nFoto = 0; $thumbs = [];
  $rp = $conn->query("SELECT code,image_path FROM photos WHERE session_code='$c' ORDER BY id DESC LIMIT 3");
  if ($rp) { while ($p = $rp->fetch_assoc()) $thumbs[] = $p; }
  $rc = $conn->query("SELECT COUNT(*) t FROM photos WHERE session_code='$c'");
  if ($rc && $x = $rc->fetch_assoc()) $nFoto = intval($x['t']);
  $s['order'] = $o; $s['nFoto'] = $nFoto; $s['thumbs'] = $thumbs;
  $out[] = $s;
}
echo json_encode(['ok'=>true,'data'=>$out]);
