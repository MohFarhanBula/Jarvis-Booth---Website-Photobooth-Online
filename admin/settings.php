<?php require_once 'auth.php'; admin_require(); require_once 'layout.php';
global $conn;
$groups = [
  'Umum' => ['site_name'=>'Nama situs'],
  'Login Google' => ['google_client_id'=>'Google Client ID (kosong = mode debug)'],
  'Sesi Foto' => ['session_price'=>'Harga sesi Rp','session_minutes'=>'Durasi menit'],
  'Tripay' => ['tripay_mode'=>'Mode','tripay_merchant_code'=>'Merchant Code','tripay_api_key'=>'API Key','tripay_private_key'=>'Private Key','tripay_callback_url'=>'Callback URL (kosong = otomatis)'],
];
$keys = ['site_name'=>'JarvisBooth','google_client_id'=>'','session_price'=>'5000','session_minutes'=>'10','tripay_mode'=>'sandbox','tripay_merchant_code'=>'','tripay_api_key'=>'','tripay_private_key'=>'','tripay_callback_url'=>''];
if ($_SERVER['REQUEST_METHOD']==='POST') {
  foreach($keys as $k=>$d){ $v=trim($_POST[$k]??''); $st=$conn->prepare("INSERT INTO settings (kunci,nilai) VALUES (?,?) ON DUPLICATE KEY UPDATE nilai=VALUES(nilai)"); $st->bind_param("ss",$k,$v); $st->execute(); $st->close(); }
  flash_set('Pengaturan disimpan.'); header('Location: settings.php'); exit;
}
$cur=[]; $r=$conn->query("SELECT kunci,nilai FROM settings"); while($r&&$row=$r->fetch_assoc()) $cur[$row['kunci']]=$row['nilai'];
$cb = (!empty($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].str_replace('/admin/settings.php','/api/tripay_callback.php',$_SERVER['SCRIPT_NAME']);
layout_head('Setting', 'settings', 'Konfigurasi umum dan kredensial Tripay');
?>
<div class="d-sm-flex align-items-center justify-content-between mb-3"><h1 class="h3 mb-0 text-gray-800">Setting</h1></div>
<form method="POST" class="row g-3">
<?php foreach($groups as $g=>$fields): ?>
<div class="col-lg-6"><div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><?= $g ?></h6></div><div class="card-body">
<?php foreach($fields as $k=>$label): ?>
<div class="mb-2"><label class="form-label small fw-bold"><?= htmlspecialchars($label) ?></label>
<?php if($k==='tripay_mode'): ?><select name="tripay_mode" class="form-select form-select-sm"><option value="sandbox" <?= ($cur['tripay_mode']??'sandbox')==='sandbox'?'selected':'' ?>>sandbox — testing</option><option value="production" <?= ($cur['tripay_mode']??'')==='production'?'selected':'' ?>>production — live</option></select>
<?php else: ?><input name="<?= $k ?>" value="<?= htmlspecialchars($cur[$k]??'') ?>" class="form-control form-control-sm"><?php endif; ?></div>
<?php endforeach; ?></div></div></div>
<?php endforeach; ?>
<div class="col-12"><button class="btn btn-primary"><i class="bi bi-save"></i> Simpan Semua</button></div>
</form>
<div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Callback Tripay</h6></div>
<div class="card-body"><p class="text-muted small">Tempel URL ini ke dashboard Tripay → Merchant → Callback URL</p>
<div class="input-group"><code id="cb" class="form-control"><?= htmlspecialchars($cur['tripay_callback_url']?:$cb) ?></code><button class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText(document.getElementById('cb').textContent)"><i class="bi bi-clipboard"></i></button></div>
<p class="text-muted small mt-2 mb-0">Sandbox: Tripay → API & Integrasi → Simulator → Merchant → Detail. Aktifkan channel QRIS. Localhost gunakan ngrok agar callback masuk.</p></div></div>
<?php layout_foot(); ?>
