<?php
require_once __DIR__ . '/../includes/auth_user.php';
header('Content-Type: application/json');
$user = user_current();
if (!$user) { http_response_code(401); echo json_encode(['ok'=>false,'msg'=>'Login dulu!']); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok'=>false,'msg'=>'POST only']);
  exit;
}

$img = $_POST['image'] ?? '';
$template = substr($_POST['template'] ?? 'strip4', 0, 50);
$filter_name = substr($_POST['filter'] ?? 'normal', 0, 50);
$caption = substr($_POST['caption'] ?? '', 0, 255);
$frame_code = substr($_POST['frame_code'] ?? '', 0, 30);
$is_public = (($_POST['is_public'] ?? '1') === '0' || ($_POST['is_public'] ?? '') === 'false') ? 0 : 1;

if (!$img || strpos($img, 'data:image/') !== 0) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'msg'=>'Gambar kosong. Foto dulu gih!']);
  exit;
}

// parse dataURL
if (!preg_match('/^data:image\/(png|jpeg);base64,/', $img, $m)) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'msg'=>'Format gambar aneh']);
  exit;
}
$ext = $m[1] === 'jpeg' ? 'jpg' : 'png';
$data = base64_decode(preg_replace('/^data:image\/(png|jpeg);base64,/', '', $img));
if (!$data || strlen($data) > 8*1024*1024) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'msg'=>'Gambar kegedean / rusak']);
  exit;
}

$code = substr(str_shuffle('ABCDEFGHJKMNPQRSTUVWXYZ23456789'), 0, 6);
$dir = __DIR__ . '/../uploads';
if (!is_dir($dir)) mkdir($dir, 0777, true);
$fname = date('Ymd-His') . '-' . $code . '.' . $ext;
file_put_contents($dir . '/' . $fname, $data);
$path = 'uploads/' . $fname;

if (db_ok()) {
  global $conn;
  $has_frame = false;
  try { $r=$conn->query("SHOW COLUMNS FROM photos LIKE 'frame_code'"); $has_frame = $r && $r->num_rows>0; } catch(Throwable $e){}
  if ($has_frame) {
    $has_pub = false;
    try { $r=$conn->query("SHOW COLUMNS FROM photos LIKE 'is_public'"); $has_pub = $r && $r->num_rows>0; } catch(Throwable $e){}
    if ($has_pub) {
      $sess = substr($_POST['session_code'] ?? '', 0, 8);
      $stmt = $conn->prepare("INSERT INTO photos (code,image_path,template,filter_name,caption,frame_code,is_public,user_id,session_code) VALUES (?,?,?,?,?,?,?, ?,?)");
      for ($i=0;$i<3;$i++) {
        $stmt->bind_param("ssssssiis", $code, $path, $template, $filter_name, $caption, $frame_code, $is_public, $user['id'], $sess);
        if ($stmt->execute()) break;
        $code = substr(str_shuffle('ABCDEFGHJKMNPQRSTUVWXYZ23456789'), 0, 6);
      }
    } else {
      $stmt = $conn->prepare("INSERT INTO photos (code,image_path,template,filter_name,caption,frame_code) VALUES (?,?,?,?,?,?)");
      for ($i=0;$i<3;$i++) {
        $stmt->bind_param("ssssss", $code, $path, $template, $filter_name, $caption, $frame_code);
        if ($stmt->execute()) break;
        $code = substr(str_shuffle('ABCDEFGHJKMNPQRSTUVWXYZ23456789'), 0, 6);
      }
    }
  } else {
    $stmt = $conn->prepare("INSERT INTO photos (code,image_path,template,filter_name,caption) VALUES (?,?,?,?,?)");
    // jika code tabrakan, retry 3x
    for ($i=0;$i<3;$i++) {
      $stmt->bind_param("sssss", $code, $path, $template, $filter_name, $caption);
      if ($stmt->execute()) break;
      $code = substr(str_shuffle('ABCDEFGHJKMNPQRSTUVWXYZ23456789'), 0, 6);
    }
  }
  $stmt->close();
}

echo json_encode(['ok'=>true,'code'=>$code,'path'=>$path,'is_public'=>$is_public,'url'=> base_url() . '/result.php?code=' . $code]);
