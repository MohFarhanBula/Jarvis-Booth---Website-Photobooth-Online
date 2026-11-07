<?php
// Status sesi: paid? sisa waktu? + order terakhir. Dipolling tiap detik.
require_once __DIR__ . '/../includes/auth_user.php';
header('Content-Type: application/json');
$code = preg_replace('/[^A-Z0-9]/','', strtoupper($_GET['code'] ?? ''));
if (!$code || !db_ok()) { echo json_encode(['ok'=>false]); exit; }
global $conn;
$c = $conn->real_escape_string($code);
$res = $conn->query("SELECT code,owner_id,orientation,mode,status,paid_at,expires_at,TIMESTAMPDIFF(SECOND,NOW(),expires_at) AS sisa FROM sessions WHERE code='$c' LIMIT 1");
$s = $res ? $res->fetch_assoc() : null;
if (!$s) { echo json_encode(['ok'=>false]); exit; }
// auto-expire
if ($s['status']==='active' && intval($s['sisa'] ?? 0) <= 0) {
  $conn->query("UPDATE sessions SET status='expired' WHERE code='$c'");
  $conn->query("UPDATE rooms SET status='expired' WHERE code='$c'");
  $s['status']='expired'; $s['sisa']=0;
}
$order = null;
$is_owner = user_current() && intval(user_current()['id'])===intval($s['owner_id']);
// detail order (QR/pay_code) hanya untuk owner — tamu cukup tahu status+timer
if ($is_owner) {
  $ro = $conn->query("SELECT merchant_ref,amount,channel,pay_code,qr_url,checkout_url,status FROM orders WHERE session_code='$c' ORDER BY id DESC LIMIT 1");
  if ($ro) $order = $ro->fetch_assoc();
} else {
  $ro = $conn->query("SELECT status FROM orders WHERE session_code='$c' ORDER BY id DESC LIMIT 1");
  if ($ro && $ox = $ro->fetch_assoc()) $order = ['status'=>$ox['status']];
}
echo json_encode(['ok'=>true,'session'=>$s,'order'=>$order,'now'=>round(microtime(true)*1000),
  'is_owner'=>$is_owner]);
