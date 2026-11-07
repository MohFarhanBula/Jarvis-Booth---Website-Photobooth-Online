<?php
require_once __DIR__ . '/../includes/auth_user.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false]); exit; }
$cred = $_POST['credential'] ?? '';
if (!$cred) { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>'Token kosong']); exit; }
$j = google_verify_token($cred);
if (!$j) { http_response_code(401); echo json_encode(['ok'=>false,'msg'=>'Token Google tidak valid / Client ID belum disetting']); exit; }
$id = user_upsert_google($j['sub'], $j['email'] ?? '', $j['name'] ?? 'Teman', $j['picture'] ?? '');
if (!$id) { http_response_code(500); echo json_encode(['ok'=>false,'msg'=>'Gagal simpan user']); exit; }
user_login_session($id, $j['name'] ?? 'Teman', $j['email'] ?? '', $j['picture'] ?? '');
echo json_encode(['ok'=>true,'nama'=>$j['name'] ?? 'Teman']);
