<?php
// Auth USER (pengunjung). Terpisah dari auth admin.
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';

function user_current() {
  if (empty($_SESSION['user_id'])) return null;
  return [
    'id' => intval($_SESSION['user_id']),
    'nama' => $_SESSION['user_nama'] ?? 'Tamu',
    'email' => $_SESSION['user_email'] ?? '',
    'avatar' => $_SESSION['user_avatar'] ?? '',
  ];
}
function user_require() {
  if (!user_current()) {
    $next = urlencode($_SERVER['REQUEST_URI'] ?? 'index.php');
    header('Location: login.php?next=' . $next);
    exit;
  }
}
function user_login_session($id, $nama, $email='', $avatar='') {
  session_regenerate_id(true);
  $_SESSION['user_id'] = intval($id);
  $_SESSION['user_nama'] = $nama;
  $_SESSION['user_email'] = $email;
  $_SESSION['user_avatar'] = $avatar;
}
// Upsert user Google. Return id atau 0.
function user_upsert_google($google_id, $email, $nama, $avatar='') {
  if (!db_ok() || !$google_id) return 0;
  global $conn;
  $gid = $conn->real_escape_string($google_id);
  $res = $conn->query("SELECT id, nama, email, avatar FROM users WHERE google_id='$gid' LIMIT 1");
  if ($res && $row = $res->fetch_assoc()) return intval($row['id']);
  $uname = 'g_' . substr(preg_replace('/[^a-z0-9]/','', strtolower($google_id)), 0, 20) . '_' . rand(100,999);
  $st = $conn->prepare("INSERT INTO users (username, google_id, email, nama, avatar, role) VALUES (?,?,?,?,?,'user')");
  $st->bind_param("sssss", $uname, $google_id, $email, $nama, $avatar);
  if ($st->execute()) { $id = $st->insert_id; $st->close(); return $id; }
  $st->close(); return 0;
}
// Verifikasi Google ID token via tokeninfo. Return payload atau null.
function google_verify_token($id_token) {
  $ch = curl_init();
  curl_setopt_array($ch, [
    CURLOPT_URL => 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($id_token),
    CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10,
  ]);
  $resp = curl_exec($ch); curl_close($ch);
  $j = json_decode($resp ?: '', true);
  if (!$j || empty($j['sub'])) return null;
  // pastikan aud = client id kita (kalau disetting)
  $cfg = '';
  if (db_ok()) { global $conn; $r=$conn->query("SELECT nilai FROM settings WHERE kunci='google_client_id' LIMIT 1"); if($r&&$x=$r->fetch_assoc()) $cfg=$x['nilai']; }
  if ($cfg && ($j['aud'] ?? '') !== $cfg) return null;
  return $j;
}
