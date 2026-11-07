<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
$code = preg_replace('/[^A-Z0-9]/','', strtoupper($_POST['code'] ?? ''));
$slot = intval($_POST['slot'] ?? -1);
$img = $_POST['image'] ?? '';
if (!$code || $slot<0 || strpos($img,'data:image/')!==0 || !db_ok()) { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>'Data kurang']); exit; }
if (!preg_match('/^data:image\/(png|jpeg);base64,/', $img, $m)) { http_response_code(400); echo json_encode(['ok'=>false]); exit; }
$data = base64_decode(preg_replace('/^data:image\/(png|jpeg);base64,/', '', $img));
if (!$data || strlen($data) > 6*1024*1024) { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>'Foto rusak/kegedean']); exit; }
$dir = __DIR__ . '/../uploads';
if (!is_dir($dir)) mkdir($dir, 0777, true);
$fname = 'room-'.$code.'-slot'.$slot.'.jpg';
file_put_contents($dir.'/'.$fname, $data);
$path = 'uploads/'.$fname;
global $conn;
$c = $conn->real_escape_string($code); $p = $conn->real_escape_string($path);
$conn->query("INSERT INTO room_photos (room_code, slot, image_path) VALUES ('$c',$slot,'$p') ON DUPLICATE KEY UPDATE image_path='$p', created_at=NOW()");
echo json_encode(['ok'=>true,'path'=>$path]);
