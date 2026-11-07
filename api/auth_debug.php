<?php
// Login debug localhost — dipakai selama Google Client ID belum disiapkan.
require_once __DIR__ . '/../includes/auth_user.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false]); exit; }
$nama = substr(trim($_POST['nama'] ?? ''), 0, 50) ?: 'Teman Debug';
$email = substr(trim($_POST['email'] ?? ''), 0, 120);
if (!db_ok()) { http_response_code(500); echo json_encode(['ok'=>false,'msg'=>'DB mati']); exit; }
$gid = 'debug_' . md5(strtolower($email ?: $nama));
$id = user_upsert_google($gid, $email, $nama, '');
if (!$id) { http_response_code(500); echo json_encode(['ok'=>false,'msg'=>'Gagal']); exit; }
user_login_session($id, $nama, $email, '');
echo json_encode(['ok'=>true,'nama'=>$nama]);
