<?php
// Sync state room: anggota mana aja boleh ganti frame/template. Last-write-wins.
require_once __DIR__ . '/../includes/auth_user.php';
header('Content-Type: application/json');
$user = user_current();
if (!$user || !db_ok()) { http_response_code(401); echo json_encode(['ok'=>false]); exit; }
$code = preg_replace('/[^A-Z0-9]/','', strtoupper($_POST['code'] ?? ''));
$frame = preg_replace('/[^a-z0-9-]/','', strtolower($_POST['frame'] ?? ''));
$template = $_POST['template'] ?? '';
if (!in_array($template, ['strip4','polaroid','single'], true)) $template = '';
if (!$code || (!$frame && !$template)) { http_response_code(400); echo json_encode(['ok'=>false]); exit; }
global $conn;
$c = $conn->real_escape_string($code);
$res = $conn->query("SELECT code, status FROM rooms WHERE code='$c' LIMIT 1");
$room = $res ? $res->fetch_assoc() : null;
if (!$room) { http_response_code(404); echo json_encode(['ok'=>false]); exit; }
if (in_array($room['status'] ?? '', ['locked','expired'])) { http_response_code(403); echo json_encode(['ok'=>false,'msg'=>'Sesi tidak aktif']); exit; }
$sets = ["state_rev = state_rev + 1", "state_by = '".$conn->real_escape_string(mb_substr($user['nama'],0,50))."'"];
if ($frame) {
  $fc = $conn->real_escape_string($frame);
  $rf = $conn->query("SELECT code FROM frames WHERE code='$fc' AND is_active=1 LIMIT 1");
  if (!$rf || !$rf->fetch_assoc()) { http_response_code(404); echo json_encode(['ok'=>false,'msg'=>'Frame tidak ada']); exit; }
  $sets[] = "frame_code = '$fc'";
}
if ($template) $sets[] = "template = '$template'";
$conn->query("UPDATE rooms SET ".implode(', ', $sets)." WHERE code='$c'");
$r2 = $conn->query("SELECT frame_code, template, orientation, state_rev, state_by FROM rooms WHERE code='$c' LIMIT 1");
echo json_encode(['ok'=>true,'state'=>$r2?$r2->fetch_assoc():null]);
