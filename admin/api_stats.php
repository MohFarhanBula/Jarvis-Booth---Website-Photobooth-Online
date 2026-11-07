<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
if (!admin_login()) { http_response_code(401); echo json_encode(['ok'=>false]); exit; }
if (!db_ok()) { echo json_encode(['ok'=>false]); exit; }
global $conn;
$days = min(30, max(7, intval($_GET['days'] ?? 14)));
$labels=[]; $omzet=[]; $orders=[];
for ($i=$days-1;$i>=0;$i--) {
  $d = date('Y-m-d', strtotime("-$i days"));
  $labels[] = date('d M', strtotime($d));
  $r = $conn->query("SELECT COUNT(*) c, COALESCE(SUM(CASE WHEN status='PAID' THEN amount ELSE 0 END),0) s FROM orders WHERE DATE(created_at)='$d'");
  $row = $r?$r->fetch_assoc():['c'=>0,'s'=>0];
  $orders[] = intval($row['c']); $omzet[] = intval($row['s']);
}
$st = $conn->query("SELECT status, COUNT(*) c FROM orders GROUP BY status");
$donut = ['labels'=>[],'data'=>[]]; if($st) while($r=$st->fetch_assoc()){ $donut['labels'][]=$r['status']; $donut['data'][]=intval($r['c']); }
echo json_encode(['ok'=>true,'labels'=>$labels,'omzet'=>$omzet,'orders'=>$orders,'donut'=>$donut]);
