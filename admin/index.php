<?php require_once 'auth.php'; admin_require(); require_once 'layout.php';
global $conn;
$stat = ['photos'=>0,'rooms'=>0,'orders'=>0,'omzet'=>0,'pending'=>0];
if (db_ok()) {
  $q = $conn->query("SELECT COUNT(*) c FROM photos"); $stat['photos'] = $q?$q->fetch_assoc()['c']:0;
  $q = $conn->query("SELECT COUNT(*) c FROM rooms"); $stat['rooms'] = $q?$q->fetch_assoc()['c']:0;
  $q = $conn->query("SELECT COUNT(*) c, COALESCE(SUM(CASE WHEN status='PAID' THEN amount ELSE 0 END),0) s, SUM(status='UNPAID') p FROM orders"); if($q&&$r=$q->fetch_assoc()){ $stat['orders']=$r['c']; $stat['omzet']=$r['s']; $stat['pending']=$r['p']; }
  $top = $conn->query("SELECT f.nama, COUNT(o.id) jml, SUM(CASE WHEN o.status='PAID' THEN o.amount ELSE 0 END) om FROM frames f LEFT JOIN orders o ON o.frame_code=f.code GROUP BY f.code ORDER BY jml DESC LIMIT 5");
  $recent = $conn->query("SELECT merchant_ref, frame_code, amount, status, created_at FROM orders ORDER BY id DESC LIMIT 8");
}
layout_head('Dashboard', 'index', 'Ringkasan performa photobooth');
?>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
  <div><a href="frames.php" class="btn btn-sm btn-primary shadow-sm"><i class="bi bi-plus-lg"></i> Tambah Frame</a>
  <a href="orders.php" class="btn btn-sm btn-outline-secondary shadow-sm">Lihat Pending</a></div>
</div>
<div class="row g-3">
  <?php foreach([['Foto','bi-camera-fill','primary',$stat['photos']],['Room LDR','bi-people-fill','info',$stat['rooms']],['Total Order','bi-receipt','warning',$stat['orders']],['Omzet PAID','bi-cash-stack','success','Rp'.number_format($stat['omzet'],0,',','.')],['Pending','bi-hourglass-split','danger',$stat['pending']]] as $s): ?>
  <div class="col-xl col-md-4 col-6"><div class="card border-left-<?= $s[2] ?> shadow h-100 py-2"><div class="card-body"><div class="row align-items-center"><div class="col"><div class="text-xs fw-bold text-<?= $s[2] ?> text-uppercase mb-1"><?= $s[0] ?></div><div class="h5 mb-0 fw-bold text-gray-800"><?= $s[3] ?></div></div><div class="col-auto"><i class="bi <?= $s[1] ?> fs-2 text-gray-300"></i></div></div></div></div></div>
  <?php endforeach; ?>
</div>
<div class="row g-3 mt-1">
  <div class="col-xl-7"><div class="card shadow mb-3"><div class="card-header py-3 d-flex align-items-center gap-2"><h6 class="m-0 fw-bold text-primary">Omzet</h6><select id="days" class="form-select form-select-sm" style="width:120px"><option value="7">7 hari</option><option value="14" selected>14 hari</option><option value="30">30 hari</option></select></div><div class="card-body"><canvas id="chOm" height="140"></canvas></div></div></div>
  <div class="col-xl-5"><div class="card shadow mb-3"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Status Order</h6></div><div class="card-body"><canvas id="chDon" height="140"></canvas></div></div></div>
</div>
<div class="row g-3">
  <div class="col-lg-6"><div class="card shadow mb-3"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Frame Terlaris</h6></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-bordered table-hover mb-0"><thead class="table-light"><tr><th>Frame</th><th>Order</th><th>Omzet</th></tr></thead><tbody>
    <?php if(isset($top)) while($r=$top->fetch_assoc()): ?><tr><td><?= htmlspecialchars($r['nama']) ?></td><td><?= $r['jml'] ?></td><td>Rp<?= number_format($r['om'],0,',','.') ?></td></tr><?php endwhile; ?>
  </tbody></table></div></div></div></div>
  <div class="col-lg-6"><div class="card shadow mb-3"><div class="card-header py-3 d-flex justify-content-between align-items-center"><h6 class="m-0 fw-bold text-primary">Order Terbaru</h6><a href="orders.php" class="small">Lihat semua →</a></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-bordered table-hover mb-0"><thead class="table-light"><tr><th>Referensi</th><th>Nominal</th><th>Status</th></tr></thead><tbody>
    <?php if(isset($recent)) while($r=$recent->fetch_assoc()): ?><tr><td><b><?= htmlspecialchars($r['merchant_ref']) ?></b><br><small class="text-muted"><?= htmlspecialchars($r['frame_code']==='__SESSION__'?'🎫 Sesi':$r['frame_code']) ?> • <?= htmlspecialchars($r['created_at']) ?></small></td><td>Rp<?= number_format($r['amount'],0) ?></td><td><span class="badge <?= $r['status']==='PAID'?'bg-success':'bg-warning text-dark' ?>"><?= $r['status'] ?></span></td></tr><?php endwhile; ?>
  </tbody></table></div></div></div></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
let ch1=null, ch2=null;
async function loadChart(days=14){
  try{
    const r=await fetch('api_stats.php?days='+days); const j=await r.json(); if(!j.ok) return;
    if(ch1) ch1.destroy(); if(ch2) ch2.destroy();
    ch1=new Chart(document.getElementById('chOm'),{type:'line',data:{labels:j.labels,datasets:[{label:'Omzet Rp',data:j.omzet,tension:.4,borderColor:'#4e73df',backgroundColor:'#4e73df22',fill:true},{label:'Order',data:j.orders,tension:.4,borderColor:'#1cc88a'}]},options:{plugins:{legend:{display:true}},scales:{y:{beginAtZero:true}}}});
    ch2=new Chart(document.getElementById('chDon'),{type:'doughnut',data:{labels:j.donut.labels,datasets:[{data:j.donut.data,backgroundColor:['#1cc88a','#f6c23e','#e74a3b','#858796']}]}});
  }catch(e){}
}
document.getElementById('days').onchange=e=>loadChart(e.target.value);
loadChart(14);
</script>
<?php layout_foot(); ?>
