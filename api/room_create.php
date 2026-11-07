<?php
require_once __DIR__ . '/../includes/auth_user.php';
header('Content-Type: application/json');
$user = user_current();
if (!$user) { http_response_code(401); echo json_encode(['ok'=>false,'msg'=>'Login dulu!']); exit; }
function max_slot($mode){ return $mode==='squad' ? 4 : ($mode==='duo' ? 2 : 1); }

$mode = $_POST['mode'] ?? 'duo';
if (!in_array($mode, ['solo','duo','squad'], true)) $mode = 'duo';
$nama = substr(trim($_POST['nama'] ?? $user['nama']), 0, 50) ?: $user['nama'];
if (!db_ok()) { http_response_code(500); echo json_encode(['ok'=>false,'msg'=>'DB mati. Nyalain MySQL!']); exit; }
global $conn;
for ($t=0;$t<5;$t++) {
  $code = substr(str_shuffle('ABCDEFGHJKMNPQRSTUVWXYZ23456789'), 0, 5);
  $tpl = $mode==='squad' ? 'strip4' : ($mode==='duo' ? 'polaroid' : 'single');
  $st = $conn->prepare("INSERT INTO rooms (code, mode, template) VALUES (?,?,?)");
  $st->bind_param("sss", $code, $mode, $tpl);
  if ($st->execute()) { $st->close(); break; }
  $st->close(); $code = null;
}
if (!$code) { http_response_code(500); echo json_encode(['ok'=>false,'msg'=>'Gagal bikin room']); exit; }
$st = $conn->prepare("INSERT INTO room_members (room_code, nama, slot, is_host) VALUES (?,?,0,1)");
$st->bind_param("ss", $code, $nama); $st->execute();
$mid = $st->insert_id; $st->close();
echo json_encode(['ok'=>true,'code'=>$code,'member_id'=>$mid,'slot'=>0,'mode'=>$mode]);
