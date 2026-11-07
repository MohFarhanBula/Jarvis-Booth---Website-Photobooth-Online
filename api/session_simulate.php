<?php
// Simulasi bayar sesi (localhost/dev). Host only.
require_once __DIR__ . '/../includes/auth_user.php';
header('Content-Type: application/json');
$user = user_current();
if (!$user || !db_ok()) { http_response_code(401); echo json_encode(['ok'=>false]); exit; }
$code = preg_replace('/[^A-Z0-9]/','', strtoupper($_POST['code'] ?? ''));
if (!$code) { http_response_code(400); echo json_encode(['ok'=>false]); exit; }
global $conn;
$c = $conn->real_escape_string($code);
$res = $conn->query("SELECT owner_id, status FROM sessions WHERE code='$c' LIMIT 1");
$s = $res ? $res->fetch_assoc() : null;
if (!$s || intval($s['owner_id']) !== intval($user['id'])) { http_response_code(403); echo json_encode(['ok'=>false,'msg'=>'Hanya host!']); exit; }
$mins = 10; $r2=$conn->query("SELECT nilai FROM settings WHERE kunci='session_minutes' LIMIT 1");
if ($r2&&$x=$r2->fetch_assoc()) $mins=max(1,intval($x['nilai']));
$conn->query("UPDATE orders SET status='PAID', paid_at=NOW() WHERE session_code='$c' AND status!='PAID'");
$conn->query("UPDATE sessions SET status='active', paid_at=NOW(), expires_at=DATE_ADD(NOW(), INTERVAL $mins MINUTE) WHERE code='$c'");
$conn->query("UPDATE rooms SET status='lobby' WHERE code='$c' AND status IN ('locked','expired')");
echo json_encode(['ok'=>true,'code'=>$code]);
