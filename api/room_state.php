<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
$code = preg_replace('/[^A-Z0-9]/','', strtoupper($_GET['code'] ?? ''));
if (!$code || !db_ok()) { echo json_encode(['ok'=>false]); exit; }
global $conn;
$c = $conn->real_escape_string($code);
$res = $conn->query("SELECT * FROM rooms WHERE code='$c' LIMIT 1");
$room = $res ? $res->fetch_assoc() : null;
if (!$room) { echo json_encode(['ok'=>false,'msg'=>'no room']); exit; }
// info sesi (timer) — diambil dari sessions via session_code (fallback: code sama)
$sess = null;
$scode = $room['session_code'] ?: $code;
$r0 = $conn->query("SELECT status, orientation, TIMESTAMPDIFF(SECOND,NOW(),expires_at) AS sisa FROM sessions WHERE code='".$conn->real_escape_string($scode)."' LIMIT 1");
if ($r0) $sess = $r0->fetch_assoc();
// auto-expire sinkron
if ($sess && $sess['status']==='active' && intval($sess['sisa'] ?? 0) <= 0) {
  $conn->query("UPDATE sessions SET status='expired' WHERE code='".$conn->real_escape_string($scode)."'");
  $conn->query("UPDATE rooms SET status='expired' WHERE code='$c'");
  $room['status']='expired'; $sess['status']='expired'; $sess['sisa']=0;
}
$members = []; $r2 = $conn->query("SELECT id, nama, slot, peer_id, is_host FROM room_members WHERE room_code='$c' AND last_seen > NOW() - INTERVAL 20 SECOND ORDER BY slot");
if ($r2) while($m=$r2->fetch_assoc()) $members[] = $m;
$photos = []; $r3 = $conn->query("SELECT slot, image_path FROM room_photos WHERE room_code='$c' ORDER BY slot");
if ($r3) while($p=$r3->fetch_assoc()) $photos[] = $p;
echo json_encode(['ok'=>true,'room'=>$room,'members'=>$members,'photos'=>$photos,'session'=>$sess,'now'=>round(microtime(true)*1000)]);
