<?php
require_once __DIR__ . '/../includes/auth_user.php';
header('Content-Type: application/json');
$user = user_current();
if (!$user) { http_response_code(401); echo json_encode(['ok'=>false,'msg'=>'Login dulu!']); exit; }
$code = preg_replace('/[^A-Z0-9]/','', strtoupper($_POST['code'] ?? $_GET['code'] ?? ''));
$nama = substr(trim($_POST['nama'] ?? $user['nama']), 0, 50) ?: $user['nama'];
if (!$code || !db_ok()) { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>'Kode salah / DB mati']); exit; }
global $conn;
$res = $conn->query("SELECT code, mode, status FROM rooms WHERE code='".$conn->real_escape_string($code)."' LIMIT 1");
$room = $res ? $res->fetch_assoc() : null;
if (!$room) { http_response_code(404); echo json_encode(['ok'=>false,'msg'=>'Room nggak ketemu. Salah kode?']); exit; }
if (($room['status'] ?? '')==='locked') { http_response_code(402); echo json_encode(['ok'=>false,'msg'=>'Room belum dibayar host. Minta host selesaikan pembayaran dulu! 💰']); exit; }
if (($room['status'] ?? '')==='expired') { http_response_code(410); echo json_encode(['ok'=>false,'msg'=>'Sesi 10 menit sudah habis. Bikin sesi baru ya! ⏰']); exit; }
$max = $room['mode']==='squad' ? 4 : ($room['mode']==='duo' ? 2 : 1);
// hapus member basi (>60 dtk) biar slot bisa dipakai lagi — khusus mode rame
$conn->query("DELETE FROM room_members WHERE room_code='".$conn->real_escape_string($code)."' AND last_seen < NOW() - INTERVAL 60 SECOND");
$res2 = $conn->query("SELECT slot FROM room_members WHERE room_code='".$conn->real_escape_string($code)."' ORDER BY slot");
$taken = []; if ($res2) while($r=$res2->fetch_assoc()) $taken[] = intval($r['slot']);
$slot = null; for ($i=0;$i<$max;$i++) if (!in_array($i,$taken)) { $slot=$i; break; }
if ($slot===null) { http_response_code(403); echo json_encode(['ok'=>false,'msg'=>'Room penuh! Max '.$max.' orang. Bikin room baru gih.']); exit; }
$st = $conn->prepare("INSERT INTO room_members (room_code, nama, slot) VALUES (?,?,?)");
$st->bind_param("ssi", $code, $nama, $slot); $st->execute();
$mid = $st->insert_id; $st->close();
echo json_encode(['ok'=>true,'code'=>$code,'member_id'=>$mid,'slot'=>$slot,'mode'=>$room['mode']]);
