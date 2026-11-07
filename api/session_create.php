<?php
// Buat sesi foto berbayar: 1 sesi = 1 room code. Host bayar, tamu join gratis.
require_once __DIR__ . '/../includes/auth_user.php';
require_once __DIR__ . '/../includes/tripay.php';
header('Content-Type: application/json');
$user = user_current();
if (!$user) { http_response_code(401); echo json_encode(['ok'=>false,'msg'=>'Login dulu!']); exit; }
if ($_SERVER['REQUEST_METHOD']!=='POST' || !db_ok()) { http_response_code(400); echo json_encode(['ok'=>false]); exit; }
global $conn;
$mode = $_POST['mode'] ?? 'solo';
if (!in_array($mode, ['solo','duo','squad'], true)) $mode = 'solo';
$multi = '0'; $rm=$conn->query("SELECT nilai FROM settings WHERE kunci='multidevice_enabled' LIMIT 1");
if ($rm&&$xm=$rm->fetch_assoc()) $multi=$xm['nilai'];
if ($mode!=='solo' && $multi!=='1') { http_response_code(403); echo json_encode(['ok'=>false,'msg'=>'Mode bareng lagi maintenance. Pilih solo ya! 🛠️']); exit; }
$orientation = ($_POST['orientation'] ?? 'portrait') === 'landscape' ? 'landscape' : 'portrait';
$channel = preg_replace('/[^A-Z]/','', strtoupper($_POST['channel'] ?? 'QRIS'));
$allowed = ['QRIS','QRISC','QRIS2','BRIVA','BNIVA','BCAVA','MANDIRIVA','DANA','SHOPEEPAY','OVO','ALFAMART','INDOMARET'];
if (!in_array($channel,$allowed)) $channel='QRIS';
$price = 5000; $r=$conn->query("SELECT nilai FROM settings WHERE kunci='session_price' LIMIT 1");
if ($r&&$x=$r->fetch_assoc()) $price=max(1000,intval($x['nilai']));
// kode sesi = kode room. Pakai ulang sesi 'created' (<24 jam, ori+mode sama) biar refresh tidak numpuk sesi.
$code = null; $member_id = 0;
$ru = $conn->query("SELECT code FROM sessions WHERE owner_id=".intval($user['id'])." AND orientation='".$conn->real_escape_string($orientation)."' AND mode='".$conn->real_escape_string($mode)."' AND status='created' AND created_at > NOW() - INTERVAL 1 DAY ORDER BY id DESC LIMIT 1");
if ($ru && $rx = $ru->fetch_assoc()) {
  $code = $rx['code'];
  $rm = $conn->query("SELECT id FROM room_members WHERE room_code='".$conn->real_escape_string($code)."' AND is_host=1 LIMIT 1");
  if ($rm && $mx = $rm->fetch_assoc()) $member_id = intval($mx['id']);
}
if (!$code) {
for ($t=0;$t<5;$t++) {
  $code = substr(str_shuffle('ABCDEFGHJKMNPQRSTUVWXYZ23456789'), 0, 5);
  $st = $conn->prepare("INSERT INTO sessions (code, owner_id, orientation, mode, status) VALUES (?,?,?,?,'created')");
  $st->bind_param("siss", $code, $user['id'], $orientation, $mode);
  if ($st->execute()) { $st->close(); break; }
  $st->close(); $code = null;
}
if (!$code) { http_response_code(500); echo json_encode(['ok'=>false,'msg'=>'Gagal bikin sesi']); exit; }
$tpl = $mode==='squad' ? 'strip4' : ($mode==='duo' ? 'polaroid' : 'single');
// room langsung dibuat tapi DIKUNCI sampai bayar
$st = $conn->prepare("INSERT INTO rooms (code, mode, status, template, session_code, orientation) VALUES (?,?, 'locked',?,?,?)");
$st->bind_param("sssss", $code, $mode, $tpl, $code, $orientation); $st->execute(); $st->close();
// host auto-join slot 0
$st = $conn->prepare("INSERT INTO room_members (room_code, nama, slot, is_host) VALUES (?,?,0,1)");
$st->bind_param("ss", $code, $user['nama']); $st->execute(); $member_id = $st->insert_id; $st->close();
} // end if (!$code) — pakai ulang sesi lama kalau ada
// order Tripay sesi
$ref = 'SESI-'.date('ymdHis').'-'.substr(str_shuffle('ABCDEFGHJKMNPQRSTUVWXYZ23456789'),0,4);
$proto = (!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http';
$host = $_SERVER['HTTP_HOST']??'localhost';
$dir = rtrim(str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']??'/')),'/');
if(str_ends_with($dir,'/api')) $dir=substr($dir,0,-4);
$return_url = $proto.'://'.$host.$dir.'/sesi.php?code='.$code;
$items = [['sku'=>'SESI-10MIN','name'=>'Sesi foto 10 menit ('.$mode.'/'.$orientation.')','price'=>$price,'quantity'=>1,'subtotal'=>$price]];
$t = tripay_create_transaction($ref,$price,$user['nama'],'',$channel,$items,$return_url);
if (!$t['ok']) {
  // order gagal dibuat (misal Tripay belum disetting): sesi tetap ada, user bisa coba lagi
  echo json_encode(['ok'=>false,'msg'=>$t['msg'],'code'=>$code,'member_id'=>$member_id]);
  exit;
}
$d = $t['data'];
$st = $conn->prepare("INSERT INTO orders (merchant_ref,frame_code,amount,customer_nama,customer_hp,channel,tripay_ref,pay_code,pay_url,qr_url,checkout_url,status,session_code) VALUES (?,?,?,?,?,?,?,?,?,?,?,'UNPAID',?)");
$fc='__SESSION__'; $hp=''; $pay_code=$d['pay_code']??''; $pay_url=$d['pay_url']??''; $qr=$d['qr_url']??''; $co=$d['checkout_url']??'';
$st->bind_param("ssisssssssss",$ref,$fc,$price,$user['nama'],$hp,$channel,$d['reference'],$pay_code,$pay_url,$qr,$co,$code);
$st->execute(); $st->close();
echo json_encode(['ok'=>true,'code'=>$code,'member_id'=>$member_id,'ref'=>$ref,'amount'=>$price,
  'pay_code'=>$pay_code,'pay_url'=>$pay_url,'qr_url'=>$qr,'checkout_url'=>$co]);
