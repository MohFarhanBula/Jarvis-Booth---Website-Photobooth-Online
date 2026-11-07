<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
if (!db_ok()) { echo json_encode(['ok'=>false]); exit; }
global $conn;
$res = $conn->query("SELECT code,nama,kategori,layout,bg,fg,deco,overlay_path,overlays,harga,is_active FROM frames WHERE is_active=1 ORDER BY harga, nama");
$out = []; if($res) while($r=$res->fetch_assoc()) $out[]=$r;
echo json_encode(['ok'=>true,'data'=>$out]);
