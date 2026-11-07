<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
$ref = preg_replace('/[^A-Za-z0-9-]/','', $_GET['ref'] ?? '');
if (!$ref || !db_ok()) { echo json_encode(['ok'=>false]); exit; }
global $conn;
$c=$conn->real_escape_string($ref);
$res=$conn->query("SELECT merchant_ref,frame_code,amount,channel,pay_code,pay_url,qr_url,checkout_url,status FROM orders WHERE merchant_ref='$c' LIMIT 1");
$row=$res?$res->fetch_assoc():null;
if(!$row){ echo json_encode(['ok'=>false]); exit; }
echo json_encode(['ok'=>true,'order'=>$row]);
