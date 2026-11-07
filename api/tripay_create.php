<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/tripay.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD']!=='POST') { http_response_code(405); echo json_encode(['ok'=>false]); exit; }
$frame = preg_replace('/[^a-z0-9-]/','', strtolower($_POST['frame']??''));
$channel = preg_replace('/[^A-Z]/','', strtoupper($_POST['channel']??'QRIS'));
$nama = substr(trim($_POST['nama']??'Tamu'),0,80) ?: 'Tamu';
$hp = substr(trim($_POST['hp']??''),0,20);
if (!$frame || !db_ok()) { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>'Frame salah']); exit; }
global $conn;
$fc = $conn->real_escape_string($frame);
$res = $conn->query("SELECT * FROM frames WHERE code='$fc' AND is_active=1 LIMIT 1");
$fr = $res?$res->fetch_assoc():null;
if (!$fr) { http_response_code(404); echo json_encode(['ok'=>false,'msg'=>'Frame tidak ada']); exit; }
$amount = intval($fr['harga']);
if ($amount<=0) { echo json_encode(['ok'=>true,'free'=>true,'frame'=>$fr]); exit; }
$allowed = ['QRIS','QRISC','QRIS2','BRIVA','BNIVA','BCAVA','MANDIRIVA','DANA','SHOPEEPAY','OVO','ALFAMART','INDOMARET'];
if (!in_array($channel,$allowed)) $channel='QRIS';
$ref = 'JB-'.date('ymdHis').'-'.substr(str_shuffle('ABCDEFGHJKMNPQRSTUVWXYZ23456789'),0,4);
$proto = (!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http';
$host = $_SERVER['HTTP_HOST']??'localhost';
$dir = rtrim(str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']??'/')),'/');
if(str_ends_with($dir,'/api')) $dir=substr($dir,0,-4);
$return_url = $proto.'://'.$host.$dir.'/bayar.php?ref='.$ref;
$items = [['sku'=>$fr['code'],'name'=>substr($fr['nama'],0,80),'price'=>$amount,'quantity'=>1,'subtotal'=>$amount]];
$t = tripay_create_transaction($ref,$amount,$nama,$hp,$channel,$items,$return_url);
if (!$t['ok']) { http_response_code(502); echo json_encode(['ok'=>false,'msg'=>$t['msg']]); exit; }
$d = $t['data'];
$st = $conn->prepare("INSERT INTO orders (merchant_ref,frame_code,amount,customer_nama,customer_hp,channel,tripay_ref,pay_code,pay_url,qr_url,checkout_url,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,'UNPAID')");
$pay_code=$d['pay_code']??''; $pay_url=$d['pay_url']??''; $qr=$d['qr_url']??''; $co=$d['checkout_url']??'';
$st->bind_param("ssissssssss",$ref,$frame,$amount,$nama,$hp,$channel,$d['reference'],$pay_code,$pay_url,$qr,$co);
$st->execute(); $st->close();
echo json_encode(['ok'=>true,'ref'=>$ref,'amount'=>$amount,'channel'=>$channel,'pay_code'=>$pay_code,'pay_url'=>$pay_url,'qr_url'=>$qr,'checkout_url'=>$co,'expired'=>(time()+3600)]);
