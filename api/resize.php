<?php
// Export ukuran preset: story 9:16, feed 1:1, 4:5, cetak 4R, HD asli. Center-crop.
require_once __DIR__ . '/../config.php';
$code = preg_replace('/[^A-Z0-9]/','', strtoupper($_GET['code'] ?? ''));
$size = strtolower($_GET['size'] ?? 'hd');
$presets = [
  'story' => [1080,1920,'Story 9:16'],
  'feed'  => [1080,1080,'Feed 1:1'],
  'potrait' => [1080,1350,'Portrait 4:5'],
  '4r'    => [1200,1800,'Cetak 4R'],
];
if (!$code || !db_ok()) { http_response_code(404); exit('Tidak ketemu'); }
global $conn;
$res = $conn->query("SELECT image_path FROM photos WHERE code='".$conn->real_escape_string($code)."' LIMIT 1");
$row = $res ? $res->fetch_assoc() : null;
if (!$row) { http_response_code(404); exit('Tidak ketemu'); }
$file = __DIR__ . '/../' . $row['image_path'];
if (!is_file($file)) { http_response_code(404); exit('File hilang'); }
if ($size==='hd' || $size==='original' || !isset($presets[$size])) {
  header('Content-Type: '.mime_content_type($file));
  header('Content-Disposition: attachment; filename="jarvisbooth-'.$code.'-hd'.(str_ends_with($file,'.png')?'.png':'.jpg').'"');
  readfile($file); exit;
}
[$tw,$th] = $presets[$size];
$info = getimagesize($file);
if (!$info) { http_response_code(500); exit('Gambar rusak'); }
[$sw,$sh,$type] = $info;
$src = $type===IMAGETYPE_PNG ? imagecreatefrompng($file) : imagecreatefromjpeg($file);
if (!$src) { http_response_code(500); exit('Gagal baca'); }
// center-crop ke aspek target
$scale = max($tw/$sw, $th/$sh);
$nw = (int)($sw*$scale); $nh = (int)($sh*$scale);
$tmp = imagecreatetruecolor($nw,$nh);
imagecopyresampled($tmp,$src,0,0,0,0,$nw,$nh,$sw,$sh);
$dst = imagecreatetruecolor($tw,$th);
imagecopy($dst,$tmp,(int)(($tw-$nw)/2),(int)(($th-$nh)/2),0,0,$nw,$nh);
imagedestroy($src); imagedestroy($tmp);
header('Content-Type: image/jpeg');
header('Content-Disposition: attachment; filename="jarvisbooth-'.$code.'-'.$size.'.jpg"');
imagejpeg($dst, null, 92); imagedestroy($dst);
