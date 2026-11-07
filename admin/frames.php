<?php require_once 'auth.php'; admin_require(); require_once 'layout.php';
global $conn;
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save'])) {
  if (!csrf_check($_POST['csrf'] ?? '')) { flash_set('CSRF tidak valid, coba lagi!', 'danger'); header('Location: frames.php'); exit; }
  $code = preg_replace('/[^a-z0-9-]/','', strtolower($_POST['code'] ?? ''));
  $nama = substr(trim($_POST['nama']??''),0,80); $kat = substr($_POST['kategori']??'kartun',0,30);
  $layout = $_POST['layout']??'all'; if(!in_array($layout,['all','strip4','polaroid','single'],true))$layout='all';
  $bg = substr($_POST['bg']??'#FFD6E6',0,20); $fg = substr($_POST['fg']??'#111111',0,20);
  $deco = mb_substr(trim($_POST['deco']??'⭐'),0,8); $harga = max(0,intval($_POST['harga']??0));
  $active = isset($_POST['is_active'])?1:0;
  $overlay = $_POST['old_overlay'] ?? '';
  if (!empty($_FILES['overlay']['tmp_name'])) {
    $f = $_FILES['overlay'];
    $ext = strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));
    if (in_array($ext,['png','jpg','jpeg','webp','svg']) && $f['size'] < 2*1024*1024) {
      $dir = __DIR__.'/../assets/frames'; if(!is_dir($dir)) mkdir($dir,0777,true);
      $fn = 'overlay-'.$code.'-'.time().'.'.$ext;
      if (move_uploaded_file($f['tmp_name'], $dir.'/'.$fn)) {
        if ($overlay && str_starts_with($overlay,'assets/frames/')) @unlink(__DIR__.'/../'.$overlay);
        $overlay = 'assets/frames/'.$fn;
      } else flash_set('Upload gagal!', 'danger');
    } else flash_set('File harus PNG/JPG/WEBP/SVG < 2MB', 'danger');
  }
  if ($code && $nama) {
    $st = $conn->prepare("INSERT INTO frames (code,nama,kategori,layout,bg,fg,deco,overlay_path,harga,is_active) VALUES (?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE nama=VALUES(nama),kategori=VALUES(kategori),layout=VALUES(layout),bg=VALUES(bg),fg=VALUES(fg),deco=VALUES(deco),overlay_path=VALUES(overlay_path),harga=VALUES(harga),is_active=VALUES(is_active)");
    $st->bind_param("ssssssssii",$code,$nama,$kat,$layout,$bg,$fg,$deco,$overlay,$harga,$active);
    if($st->execute()) flash_set('Frame '.$nama.' kesimpen! 🎉'); else flash_set('DB error: '.$conn->error, 'danger');
    $st->close();
  } else flash_set('Kode + nama wajib! Contoh: kartun-ninja', 'danger');
  header('Location: frames.php'); exit;
}
if (isset($_GET['toggle'])) { $c=$conn->real_escape_string($_GET['toggle']); $conn->query("UPDATE frames SET is_active = 1-is_active WHERE code='$c'"); flash_set('Status frame diubah!'); header('Location: frames.php'); exit; }
// HAPUS via POST + CSRF, superadmin only, hapus file overlay, warning order PAID
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['del'])) {
  if (!csrf_check($_POST['csrf'] ?? '')) { flash_set('CSRF tidak valid!', 'danger'); header('Location: frames.php'); exit; }
  if (!admin_is_super()) { flash_set('Eits! Hapus frame khusus superadmin 👑', 'danger'); header('Location: frames.php'); exit; }
  $c = preg_replace('/[^a-z0-9-]/','', strtolower($_POST['del']));
  $ce = $conn->real_escape_string($c);
  $ord = $conn->query("SELECT COUNT(*) t, SUM(status='PAID') p FROM orders WHERE frame_code='$ce'");
  $ot = $ord?$ord->fetch_assoc():['t'=>0,'p'=>0];
  $fr = $conn->query("SELECT overlay_path, nama FROM frames WHERE code='$ce' LIMIT 1");
  $frow = $fr?$fr->fetch_assoc():null;
  if (!$frow) { flash_set('Frame tidak ketemu!', 'danger'); }
  elseif (intval($ot['p']??0) > 0 && empty($_POST['force'])) {
    flash_set('Frame '.$frow['nama'].' punya '.$ot['p'].' order PAID! Centang paksa atau nonaktifkan aja. 💰', 'warn');
  } else {
    if (!empty($frow['overlay_path']) && str_starts_with($frow['overlay_path'],'assets/frames/')) @unlink(__DIR__.'/../'.$frow['overlay_path']);
    $conn->query("DELETE FROM frames WHERE code='$ce'");
    flash_set('Frame '.$frow['nama'].' dihapus! 🗑️ ('.$ot['t'].' order jadi arsip)');
  }
  header('Location: frames.php'); exit;
}
$edit = null;
if (isset($_GET['edit'])) { $c=$conn->real_escape_string($_GET['edit']); $r=$conn->query("SELECT * FROM frames WHERE code='$c' LIMIT 1"); $edit=$r?$r->fetch_assoc():null; }
$list = $conn->query("SELECT f.*, (SELECT COUNT(*) FROM orders o WHERE o.frame_code=f.code) jml, (SELECT SUM(o.status='PAID') FROM orders o WHERE o.frame_code=f.code) paid FROM frames f ORDER BY harga, nama");
$rows=[]; if($list) while($x=$list->fetch_assoc()) $rows[]=$x;
$isSuper = admin_is_super();
layout_head('Frames', 'frames', 'Kelola bingkai foto. Semua frame termasuk sesi. Hapus khusus superadmin.');
?>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <h1 class="h3 mb-0 text-gray-800">Frames</h1>
  <span class="text-muted small">Ukuran wajib — strip4 600×1750 • polaroid 700×1050 • single 700×860</span>
</div>
<div class="row g-3 align-items-start">
<div class="col-lg-4">
<div class="card shadow mb-4"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><?= $edit?'Edit Frame':'Tambah Frame Baru' ?></h6></div>
<div class="card-body">
  <p class="text-muted small">Isi nama + harga, upload PNG transparan, simpan → cek preview.</p>
  <form method="POST" enctype="multipart/form-data" class="row g-2">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <div class="col-6"><label class="form-label small fw-bold">Kode unik *</label><input name="code" placeholder="kartun-ninja" value="<?= htmlspecialchars($edit['code']??'') ?>" <?= $edit?'readonly':'' ?> class="form-control form-control-sm" required></div>
      <div class="col-6"><label class="form-label small fw-bold">Nama tampil *</label><input name="nama" placeholder="Ninja Gemoy" value="<?= htmlspecialchars($edit['nama']??'') ?>" class="form-control form-control-sm" required></div>
      <div class="col-6"><label class="form-label small fw-bold">Kategori</label><select name="kategori" class="form-select form-select-sm"><option>kartun</option><option>basic</option><option>event</option><option>premium</option></select></div>
      <div class="col-6"><label class="form-label small fw-bold">Layout</label><select name="layout" class="form-select form-select-sm"><option value="all">Semua layout</option><option value="strip4">Strip4 (600×1750)</option><option value="polaroid">Polaroid (700×1050)</option><option value="single">Single (700×860)</option></select></div>
      <div class="col-4"><label class="form-label small fw-bold">Background</label><input type="color" id="fBg" name="bg" value="<?= htmlspecialchars($edit['bg']??'#FFD6E6') ?>" class="form-control form-control-color w-100" style="height:38px"></div>
      <div class="col-4"><label class="form-label small fw-bold">Teks</label><input type="color" name="fg" value="<?= htmlspecialchars($edit['fg']??'#111111') ?>" class="form-control form-control-color w-100" style="height:38px"></div>
      <div class="col-4"><label class="form-label small fw-bold">Deco</label><input id="fDeco" name="deco" value="<?= htmlspecialchars($edit['deco']??'⭐') ?>" class="form-control form-control-sm"></div>
    <div class="col-12"><label class="form-label small fw-bold">Overlay <span class="text-muted">(PNG transparan disarankan)</span></label><input id="fFile" type="file" name="overlay" accept=".png,.jpg,.jpeg,.webp,.svg" class="form-control form-control-sm"></div>
    <div class="col-12 small text-muted">Preview: <span id="pv" class="badge fs-6" style="background:#FFD6E6">⭐ Contoh</span> <span id="pvInfo"></span></div>
    <?php if(!empty($edit['overlay_path'])): ?><div class="col-12"><img src="../<?= htmlspecialchars($edit['overlay_path']) ?>" class="img-thumbnail" style="height:80px"><input type="hidden" name="old_overlay" value="<?= htmlspecialchars($edit['overlay_path']) ?>"></div><?php endif; ?>
    <div class="col-12"><label class="form-label small fw-bold">Harga Rp <span class="text-muted">(arsip — tidak dipakai, yang dibayar sesi)</span></label><input type="number" name="harga" min="0" step="500" value="<?= intval($edit['harga']??0) ?>" class="form-control form-control-sm"></div>
    <div class="col-12 form-check"><input type="checkbox" class="form-check-input" id="ak" name="is_active" <?= (!isset($edit)||$edit['is_active'])?'checked':'' ?>><label class="form-check-label small fw-bold" for="ak">Aktif (tampil di booth)</label></div>
    <div class="col-12 d-grid"><button name="save" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Frame</button></div>
    <?php if($edit): ?><div class="col-12 text-center"><a href="frames.php" class="small">Batal edit</a></div><?php endif; ?>
  </form>
</div>
</div>
</div>
<div class="col-lg-8">
<div class="card shadow mb-4"><div class="card-header py-3 d-flex flex-wrap gap-2 align-items-center"><h6 class="m-0 fw-bold text-primary">Daftar Frame (<?= count($rows) ?>)</h6>
    <select id="fKat" class="form-select form-select-sm" style="width:160px"><option value="">Semua kategori</option><option>kartun</option><option>basic</option><option>event</option><option>premium</option></select>
    <input id="fQ" class="form-control form-control-sm" style="max-width:220px" placeholder="Cari nama/kode...">
    <?php if(!$isSuper): ?><span class="badge bg-warning text-dark">Hapus khusus superadmin</span><?php endif; ?>
  </div>
  <div class="card-body"><div id="fGrid" class="row g-3"></div></div>
</div>
</div>
</div>
<script>
const FR = <?= json_encode($rows) ?>;
const IS_SUPER = <?= $isSuper?'true':'false' ?>;
const CSRF = "<?= csrf_token() ?>";
function draw(){
  const kat=document.getElementById('fKat').value, q=document.getElementById('fQ').value.toLowerCase();
  const r=FR.filter(f=>(!kat||f.kategori===kat)&&(!q||(f.nama+f.code).toLowerCase().includes(q)));
  document.getElementById('fGrid').innerHTML = r.map(f=>{
    const safeCode = f.code.replace(/"/g,'');
    const delBtn = IS_SUPER
      ? `<form method="POST" id="del-${safeCode}" class="d-inline"><input type="hidden" name="csrf" value="${CSRF}"><input type="hidden" name="del" value="${safeCode}"><input type="hidden" name="force" value="1"><button type="button" class="btn btn-sm btn-outline-danger" data-code="${safeCode}" onclick="askDel(this.dataset.code)"><i class="bi bi-trash3"></i></button></form>`
      : `<button class="btn btn-sm btn-outline-secondary" disabled title="Khusus superadmin"><i class="bi bi-trash3"></i></button>`;
    return `<div class="col-6 col-md-4"><div class="card shadow-sm h-100">`
    +`<div class="d-flex justify-content-center align-items-center" style="height:110px;font-size:48px;background:${f.bg};color:${f.fg};position:relative;border-radius:.35rem .35rem 0 0">${f.deco||'🎨'}${f.overlay_path?'<span class="badge bg-dark position-absolute bottom-0 end-0 m-2">overlay</span>':''}</div>`
    +`<div class="card-body p-2"><h6 class="card-title mb-0" style="font-size:13px">${f.nama}</h6><div class="text-muted small">${f.code} • ${f.layout} • ${f.jml||0} order</div>`
    +`<div class="fw-bold my-1"><span class="badge bg-success">Termasuk sesi</span></div>`
    +`<div class="btn-group btn-group-sm"><a class="btn btn-outline-primary" href="?edit=${f.code}" title="Edit"><i class="bi bi-pencil"></i></a><a class="btn btn-outline-secondary" href="?toggle=${f.code}" title="Aktif/Nonaktif">${f.is_active==1?'Off':'On'}</a>${delBtn}</div></div></div></div>`;
  }).join('') || '<div class="col-12"><div class="alert alert-light text-center">Data tidak ditemukan.</div></div>';
}
function askDel(code){
  const f = FR.find(x=>x.code===code); if(!f) return;
  const paid = parseInt(f.paid||0), total = parseInt(f.jml||0);
  const div = document.createElement('div'); div.textContent = f.nama; const nama = div.innerHTML;
  askConfirm('Hapus frame?', `Hapus <b>${nama}</b> (<code>${code}</code>)?<br>${total} order terkait${paid?`, <b class="text-danger">${paid} di antaranya PAID</b>`:''}.<br>File overlay ikut terhapus permanen.`, document.getElementById('del-'+code));
}
document.getElementById('fKat').onchange=draw; document.getElementById('fQ').oninput=draw; draw();
const b=document.getElementById('fBg'), d=document.getElementById('fDeco'), pv=document.getElementById('pv');
function upd(){ pv.style.background=b.value; pv.textContent=(d.value||'⭐')+' Contoh'; }
b.oninput=upd; d.oninput=upd; upd();
document.getElementById('fFile').onchange=e=>{
  const f=e.target.files[0]; if(!f) return;
  document.getElementById('pvInfo').textContent = f.name+' • '+Math.round(f.size/1024)+'KB';
  if(f.type==='image/jpeg') document.getElementById('pvInfo').innerHTML+=' <b class="text-danger">⚠️ JPG nutupin foto! Pakai PNG.</b>';
};
</script>
<?php layout_foot(); ?>
