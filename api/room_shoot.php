<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
$code = preg_replace('/[^A-Z0-9]/','', strtoupper($_POST['code'] ?? ''));
$delay = min(10, max(3, intval($_POST['delay'] ?? 5)));
if (!$code || !db_ok()) { echo json_encode(['ok'=>false]); exit; }
global $conn;
$c = $conn->real_escape_string($code);
$shoot_at = round(microtime(true)*1000) + $delay*1000;
$conn->query("UPDATE rooms SET shoot_at=$shoot_at, status='shooting' WHERE code='$c'");
$conn->query("DELETE FROM room_photos WHERE room_code='$c'");
echo json_encode(['ok'=>true,'shoot_at'=>$shoot_at]);
