<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/tripay.php';
// Tripay callback: POST JSON, header X-Callback-Signature + X-Callback-Event
$raw = file_get_contents('php://input');
$sig = $_SERVER['HTTP_X_CALLBACK_SIGNATURE'] ?? '';
$event = $_SERVER['HTTP_X_CALLBACK_EVENT'] ?? '';
if (!$raw || !tripay_verify_callback($raw, $sig)) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Invalid signature']); exit; }
$j = json_decode($raw, true);
$ref = $j['merchant_ref'] ?? ''; $status = strtoupper($j['status'] ?? '');
if (!$ref) { echo json_encode(['success'=>false]); exit; }
if (db_ok()) {
  global $conn;
  $c = $conn->real_escape_string($ref);
  if ($status==='PAID') {
    $conn->query("UPDATE orders SET status='PAID', paid_at=NOW() WHERE merchant_ref='$c'");
    // Kalau order sesi: aktifkan sesi 10 menit + buka room
    $ro = $conn->query("SELECT session_code FROM orders WHERE merchant_ref='$c' LIMIT 1");
    if ($ro && $ow = $ro->fetch_assoc() && !empty($ow['session_code'])) {
      $sc = $conn->real_escape_string($ow['session_code']);
      $mins = 10; $r2=$conn->query("SELECT nilai FROM settings WHERE kunci='session_minutes' LIMIT 1");
      if ($r2&&$x=$r2->fetch_assoc()) $mins=max(1,intval($x['nilai']));
      $conn->query("UPDATE sessions SET status='active', paid_at=NOW(), expires_at=DATE_ADD(NOW(), INTERVAL $mins MINUTE) WHERE code='$sc' AND status!='active'");
      $conn->query("UPDATE rooms SET status='lobby' WHERE code='$sc' AND status='locked'");
    }
  }
  else $conn->query("UPDATE orders SET status='".$conn->real_escape_string($status)."' WHERE merchant_ref='$c'");
}
echo json_encode(['success'=>true]);
