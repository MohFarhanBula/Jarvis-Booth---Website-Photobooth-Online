<?php require_once 'auth.php'; admin_require(); require_once 'layout.php';
global $conn;
if (isset($_GET['simulate'])) {
  $c=$conn->real_escape_string($_GET['simulate']);
  $conn->query("UPDATE orders SET status='PAID', paid_at=NOW() WHERE merchant_ref='$c'");
  header('Location: orders.php'); exit;
}
if (isset($_GET['csv'])) {
  header('Content-Type: text/csv'); header('Content-Disposition: attachment; filename=orders.csv');
  $o=fopen('php://output','w'); fputcsv($o,['ref','frame','amount','channel','status','nama','created']);
  $r=$conn->query("SELECT merchant_ref,frame_code,amount,channel,status,customer_nama,created_at FROM orders ORDER BY id DESC");
  while($r&&$x=$r->fetch_assoc()) fputcsv($o,$x);
  exit;
}
$list = $conn->query("SELECT o.*, f.nama FROM orders o LEFT JOIN frames f ON f.code=o.frame_code ORDER BY o.id DESC LIMIT 200");
$rows=[]; while($list&&$x=$list->fetch_assoc()) $rows[]=$x;
$cb = (!empty($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].str_replace('/admin/orders.php','/api/tripay_callback.php',$_SERVER['SCRIPT_NAME']);
layout_head('Orders', 'orders', 'Kelola transaksi Tripay');
?>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <h1 class="h3 mb-0 text-gray-800">Orders</h1>
  <a href="?csv=1" class="btn btn-sm btn-outline-primary shadow-sm"><i class="bi bi-download"></i> Export CSV</a>
</div>
<div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Filter</h6></div>
<div class="card-body">
  <div class="row g-2">
    <div class="col-md-4"><input id="oQ" class="form-control form-control-sm" placeholder="Cari ref / nama / frame..."></div>
    <div class="col-md-3"><select id="oS" class="form-select form-select-sm"><option value="">Semua status</option><option>UNPAID</option><option>PAID</option><option>EXPIRED</option><option>FAILED</option></select></div>
    <div class="col-md-3"><select id="oC" class="form-select form-select-sm"><option value="">Semua channel</option><option>QRIS</option><option>QRISC</option><option>DANA</option><option>SHOPEEPAY</option><option>OVO</option><option>BRIVA</option><option>BNIVA</option><option>BCAVA</option></select></div>
    <div class="col-md-2"><button class="btn btn-sm btn-outline-secondary w-100" onclick="navigator.clipboard.writeText(document.getElementById('cbTxt').textContent)"><i class="bi bi-clipboard"></i> Callback</button></div>
  </div>
  <small class="text-muted">Callback URL: <code id="cbTxt"><?= htmlspecialchars($cb) ?></code></small>
</div></div>
<div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Data Order (<?= count($rows) ?>)</h6></div>
<div class="card-body p-0"><div class="table-responsive"><table class="table table-bordered table-hover mb-0" id="admtbl" data-enhance data-per="12">
<thead class="table-light"><tr><th>Referensi</th><th>Frame & Pembeli</th><th>Nominal</th><th>Channel</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php foreach($rows as $r): ?>
<tr data-st="<?= $r['status'] ?>" data-ch="<?= $r['channel'] ?>">
  <td><b><?= htmlspecialchars($r['merchant_ref']) ?></b><br><small class="text-muted"><?= htmlspecialchars($r['tripay_ref']) ?> • <?= htmlspecialchars($r['created_at']) ?></small></td>
  <td><?= htmlspecialchars($r['frame_code']==='__SESSION__' ? '🎫 Sesi foto 10 mnt'.($r['session_code']?' ('.$r['session_code'].')':'') : ($r['nama']??$r['frame_code'])) ?><br><small class="text-muted"><?= htmlspecialchars($r['customer_nama']) ?></small></td>
  <td><b>Rp<?= number_format($r['amount'],0,',','.') ?></b></td><td><?= htmlspecialchars($r['channel']) ?></td>
  <td><span class="badge <?= $r['status']==='PAID'?'bg-success':'bg-warning text-dark' ?>"><?= $r['status'] ?></span></td>
  <td class="text-nowrap"><button class="btn btn-sm btn-outline-primary" onclick='detail(<?= json_encode($r) ?>)'>Detail</button>
  <?php if($r['checkout_url']): ?><a class="btn btn-sm btn-outline-secondary" target="_blank" href="<?= htmlspecialchars($r['checkout_url']) ?>">Checkout</a><?php endif; ?>
  <a class="btn btn-sm btn-outline-warning" href="?simulate=<?= $r['merchant_ref'] ?>">Simulasi Paid</a></td></tr>
<?php endforeach; ?>
</tbody></table></div></div></div>
<div class="modal fade" id="orderModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Detail Order</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" id="mBody"></div></div></div></div>
<script>
function detail(o){
  document.getElementById('mBody').innerHTML =
    `<div>Ref: <b>${o.merchant_ref}</b></div><div>Tripay: ${o.tripay_ref||'-'}</div><div>Frame: <b>${o.frame_code}</b> • Rp${Number(o.amount).toLocaleString('id-ID')}</div><div>Channel: ${o.channel} • Status: <b>${o.status}</b></div>`+
    (o.pay_code?`<div class="mt-2">Pay code: <b>${o.pay_code}</b></div>`:'')+
    (o.qr_url?`<img src="${o.qr_url}" class="img-thumbnail mt-2" style="width:160px">`:'')+
    (o.checkout_url?`<div><a class="btn btn-sm btn-primary mt-2" target="_blank" href="${o.checkout_url}">Buka checkout Tripay</a></div>`:'');
  bootstrap.Modal.getOrCreateInstance(document.getElementById('orderModal')).show();
}
const q=document.getElementById('oQ'), s=document.getElementById('oS'), c=document.getElementById('oC');
function applyF(){
  document.querySelectorAll('#admtbl tbody tr').forEach(tr=>{
    const okQ=!q.value||tr.innerText.toLowerCase().includes(q.value.toLowerCase());
    const okS=!s.value||tr.dataset.st===s.value, okC=!c.value||tr.dataset.ch===c.value;
    tr.style.display=(okQ&&okS&&okC)?'':'none';
  });
}
q.oninput=applyF; s.onchange=applyF; c.onchange=applyF;
</script>
<?php layout_foot(); ?>
