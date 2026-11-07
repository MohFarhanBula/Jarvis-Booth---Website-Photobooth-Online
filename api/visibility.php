<?php
// Toggle visibilitas foto: pemilik (kode ada di jb_mine / dikirim sebagai owner=1) atau admin.
require_once __DIR__ . '/../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../admin/auth.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false]); exit; }
$code = preg_replace('/[^A-Z0-9]/', '', strtoupper($_POST['code'] ?? ''));
$is_public = (($_POST['is_public'] ?? '1') === '0' || ($_POST['is_public'] ?? '') === 'false') ? 0 : 1;
$owner = !empty($_POST['owner']);
if (!$code || !db_ok()) { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>'Data kurang']); exit; }
if (!$owner && !admin_login()) { http_response_code(403); echo json_encode(['ok'=>false,'msg'=>'Hanya pemilik foto / admin']); exit; }
global $conn;
$c = $conn->real_escape_string($code);
$conn->query("UPDATE photos SET is_public=$is_public WHERE code='$c'");
echo json_encode(['ok'=>true,'code'=>$code,'is_public'=>$is_public]);
