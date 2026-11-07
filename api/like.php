<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
$code = preg_replace('/[^A-Z0-9]/', '', strtoupper($_POST['code'] ?? $_GET['code'] ?? ''));
if (!$code) { echo json_encode(['ok'=>false]); exit; }
if (!db_ok()) { echo json_encode(['ok'=>true,'likes'=>rand(9,99),'offline'=>true]); exit; }
global $conn;
$conn->query("UPDATE photos SET likes = likes + 1 WHERE code = '" . $conn->real_escape_string($code) . "'");
$res = $conn->query("SELECT likes FROM photos WHERE code = '" . $conn->real_escape_string($code) . "' LIMIT 1");
$row = $res ? $res->fetch_assoc() : null;
echo json_encode(['ok'=>true,'likes'=>intval($row['likes'] ?? 0)]);
