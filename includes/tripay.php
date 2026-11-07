<?php
// Helper Tripay Closed Payment (tripay.co.id)
require_once __DIR__ . '/../config.php';

function setting_get($k, $def='') {
  if (!db_ok()) return $def;
  global $conn;
  $res = $conn->query("SELECT nilai FROM settings WHERE kunci='".$conn->real_escape_string($k)."' LIMIT 1");
  if ($res && $row = $res->fetch_assoc()) return $row['nilai'];
  return $def;
}
function tripay_base() {
  return setting_get('tripay_mode','sandbox') === 'production'
    ? 'https://tripay.co.id/api'
    : 'https://tripay.co.id/api-sandbox';
}
function tripay_create_transaction($merchant_ref, $amount, $customer_nama, $customer_hp, $channel, $order_items, $return_url='') {
  $merchant_code = setting_get('tripay_merchant_code','');
  $api_key = setting_get('tripay_api_key','');
  $private_key = setting_get('tripay_private_key','');
  if (!$merchant_code || !$api_key || !$private_key) return ['ok'=>false,'msg'=>'Tripay belum disetting di panel admin.'];
  $signature = hash_hmac('sha256', $merchant_code.$merchant_ref.$amount, $private_key);
  $callback = setting_get('tripay_callback_url','');
  if (!$callback) {
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') ? 'https':'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')),'/');
    if (str_ends_with($dir,'/api')) $dir = substr($dir,0,-4);
    $callback = $proto.'://'.$host.$dir.'/api/tripay_callback.php';
  }
  $data = [
    'method' => $channel,
    'merchant_ref' => $merchant_ref,
    'amount' => (int)$amount,
    'customer_name' => substr($customer_nama,0,80) ?: 'Tamu',
    'customer_email' => 'tamu@jarvisbooth.local',
    'customer_phone' => substr(preg_replace('/[^0-9+]/','',$customer_hp),0,20) ?: '08123456789',
    'order_items' => $order_items,
    'return_url' => $return_url,
    'expired_time' => time() + 60*60,
    'signature' => $signature,
    'callback_url' => $callback,
  ];
  $ch = curl_init();
  curl_setopt_array($ch, [
    CURLOPT_URL => tripay_base().'/transaction/create',
    CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($data),
    CURLOPT_HTTPHEADER => ['Authorization: Bearer '.$api_key],
    CURLOPT_TIMEOUT => 20,
  ]);
  $resp = curl_exec($ch); $err = curl_error($ch); curl_close($ch);
  if ($err) return ['ok'=>false,'msg'=>'Tripay error: '.$err];
  $j = json_decode($resp, true);
  if (!$j || empty($j['success'])) return ['ok'=>false,'msg'=>'Tripay: '.($j['message'] ?? 'gagal')];
  return ['ok'=>true,'data'=>$j['data']];
}
function tripay_verify_callback($raw, $header_sig) {
  $private = setting_get('tripay_private_key','');
  if (!$private) return false;
  $calc = hash_hmac('sha256', $raw, $private);
  return hash_equals($calc, strtolower(trim($header_sig)));
}
