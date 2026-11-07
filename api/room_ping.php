<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
$code = preg_replace('/[^A-Z0-9]/','', strtoupper($_POST['code'] ?? ''));
$mid = intval($_POST['member_id'] ?? 0);
$peer = substr($_POST['peer_id'] ?? '', 0, 80);
if (!$code || !$mid || !db_ok()) { echo json_encode(['ok'=>false]); exit; }
global $conn;
$c = $conn->real_escape_string($code);
if ($peer !== '') $conn->query("UPDATE room_members SET peer_id='".$conn->real_escape_string($peer)."', last_seen=NOW() WHERE id=$mid AND room_code='$c'");
else $conn->query("UPDATE room_members SET last_seen=NOW() WHERE id=$mid AND room_code='$c'");
echo json_encode(['ok'=>true]);
