<?php
require_once 'config.php';
$code = preg_replace('/[^A-Z0-9]/','', strtoupper($_GET['code'] ?? ''));
$row = null;
if ($code && db_ok()) {
  global $conn;
  $res = $conn->query("SELECT * FROM photos WHERE code='".$conn->real_escape_string($code)."' LIMIT 1");
  if ($res) $row = $res->fetch_assoc();
}
// fallback file scan
if (!$row && $code) {
  $files = glob(__DIR__.'/uploads/*'.$code.'*') ?: [];
  if ($files) $row = ['code'=>$code,'image_path'=>'uploads/'.basename($files[0]),'caption'=>'','likes'=>0,'created_at'=>'','template'=>'-'];
}
if (!$row) { header('Location: galeri.php'); exit; }
$title = 'Hasil 📸 '.$row['code'].' - JarvisBooth';
include 'includes/header.php';
$imgUrl = base_url().'/'.$row['image_path'];
$pageUrl = base_url().'/result.php?code='.$row['code'];
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<div class="py-8 grid lg:grid-cols-[420px_1fr] gap-6 items-start">
  <div class="card-gemoy p-4 text-center rotate-[-1deg]">
    <img src="<?= htmlspecialchars($row['image_path']) ?>" class="rounded-2xl w-full border-4 border-white shadow" alt="hasil">
    <div class="mt-2 font-extrabold text-lg">Kode: <span class="bg-black text-white px-3 py-1 rounded-full tracking-widest"><?= htmlspecialchars($row['code']) ?></span></div>
    <div class="text-sm opacity-70 font-bold"><?= htmlspecialchars($row['caption'] ?: 'tanpa caption, tetap gemoy') ?> • ❤️ <span id="likes"><?= intval($row['likes']) ?></span></div>
    <div class="mt-2"><span id="visBadge" class="inline-block px-3 py-1 rounded-full text-xs font-extrabold <?= (intval($row['is_public'] ?? 1) ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700') ?>"><?= (intval($row['is_public'] ?? 1) ? '🌍 Publik — tampil di galeri' : '🔒 Privat — tidak tampil di galeri') ?></span>
    <div id="visInfo" class="text-[11px] font-bold opacity-60 mt-1"><?= (intval($row['is_public'] ?? 1) ? '' : 'Link ini tetap bisa dibuka siapa pun yang punya.') ?></div>
    <button id="visBtn" onclick="toggleVis()" class="hidden mt-1 px-3 py-1.5 rounded-full bg-white border-2 text-xs font-extrabold">Ubah privasi</button></div>
  </div>
  <div>
    <h1 class="text-5xl font-extrabold">Cakep Parah! 🤩</h1>
    <p class="font-semibold opacity-70 mt-1">Jarvis kasih nilai: <b>9.8/10</b> (0.2 dikurangi karena kurang traktir Jarvis 😌)</p>
    <div class="grid sm:grid-cols-2 gap-2 mt-4">
      <a href="<?= htmlspecialchars($row['image_path']) ?>" download="jarvisbooth-<?= htmlspecialchars($row['code']) ?>.jpg" class="btn-gemoy px-5 py-3.5 rounded-2xl bg-black text-white font-extrabold text-center">⬇️ Download HD</a>
      <button onclick="like()" class="btn-gemoy px-5 py-3.5 rounded-2xl bg-candy-lemon font-extrabold border-2">❤️ Like (<span id="likes2"><?= intval($row['likes']) ?></span>)</button>
      <a href="https://wa.me/?text=<?= urlencode('Lihat fotoku di JarvisBooth! 📸 '.$pageUrl) ?>" target="_blank" class="btn-gemoy px-5 py-3.5 rounded-2xl bg-[#25D366] text-white font-extrabold text-center">🟢 Share WA</a>
      <button onclick="copyLink()" class="btn-gemoy px-5 py-3.5 rounded-2xl bg-white border-2 font-extrabold">🔗 Copy Link</button>
      <button onclick="shareNative()" class="btn-gemoy px-5 py-3.5 rounded-2xl bg-purple-600 text-white font-extrabold">📤 Share HP</button>
      <a href="sesi.php" class="btn-gemoy px-5 py-3.5 rounded-2xl bg-pink-500 text-white font-extrabold text-center">📸 Foto Lagi!</a>
    </div>
    <div class="card-gemoy p-4 mt-3">
      <div class="font-extrabold text-sm">📐 Download ukuran lain <span class="text-xs opacity-60">(nyesuaiin portrait/landscape otomatis crop tengah)</span></div>
      <div class="flex flex-wrap gap-2 mt-2 text-xs font-extrabold">
        <a href="api/resize.php?code=<?= htmlspecialchars($row['code']) ?>&size=story" class="px-4 py-2 rounded-full bg-black text-white">📱 Story 9:16</a>
        <a href="api/resize.php?code=<?= htmlspecialchars($row['code']) ?>&size=feed" class="px-4 py-2 rounded-full bg-white border-2">⬛ Feed 1:1</a>
        <a href="api/resize.php?code=<?= htmlspecialchars($row['code']) ?>&size=potrait" class="px-4 py-2 rounded-full bg-white border-2">🧍 Portrait 4:5</a>
        <a href="api/resize.php?code=<?= htmlspecialchars($row['code']) ?>&size=4r" class="px-4 py-2 rounded-full bg-white border-2">🖨️ Cetak 4R</a>
      </div>
    </div>
    <div class="card-gemoy p-4 mt-4 flex gap-4 items-center">
      <div><div class="font-extrabold">📱 Scan buat buka di HP</div><div id="qr" class="mt-2 bg-white p-2 rounded-xl inline-block"></div><div id="cp" class="text-xs font-bold mt-1 h-4 text-green-600"></div></div>
      <div class="text-sm font-semibold opacity-80">Tunjukin QR ini ke temen tongkrongan biar mereka iri terus ikutan foto. Strategi marketing Jarvis: <b>iri = viral</b> 😌</div>
    </div>
    <div class="mt-4 flex gap-2 text-sm font-bold">
      <a href="galeri.php" class="px-4 py-2 rounded-full bg-white border-2">🏆 Lihat Hall of Fame</a>
      <a href="index.php" class="px-4 py-2 rounded-full bg-white border-2">🏠 Home</a>
    </div>
  </div>
</div>
<script>
new QRCode(document.getElementById('qr'), {text:"<?= $pageUrl ?>", width:120, height:120});
function copyLink(){ navigator.clipboard.writeText("<?= $pageUrl ?>"); document.getElementById('cp').textContent='✅ Link ke-copy!'; boom(); }
async function shareNative(){ const u="<?= $pageUrl ?>"; if(navigator.share){ try{ await navigator.share({title:'JarvisBooth', text:'Lihat fotoku! 📸', url:u}); }catch(e){} } else copyLink(); }
async function like(){ try{ const fd=new FormData(); fd.append('code',"<?= $row['code'] ?>"); const r=await fetch('api/like.php',{method:'POST',body:fd}); const j=await r.json(); if(j.ok){ document.getElementById('likes').textContent=j.likes; document.getElementById('likes2').textContent=j.likes; boom(); } }catch(e){} }
// visibilitas: tombol hanya untuk pemilik (kode ada di jb_mine)
let isPub = <?= intval($row['is_public'] ?? 1) ?> ? 1 : 0;
try{
  const mine = JSON.parse(localStorage.getItem('jb_mine')||'[]');
  if(mine.includes("<?= $row['code'] ?>")){
    const b=document.getElementById('visBtn'); b.classList.remove('hidden');
    b.textContent = isPub ? '🔒 Jadikan privat' : '🌍 Tampilkan di galeri';
  }
}catch(e){}
async function toggleVis(){
  const fd=new FormData(); fd.append('code',"<?= $row['code'] ?>"); fd.append('is_public', isPub? '0':'1'); fd.append('owner','1');
  try{
    const r=await fetch('api/visibility.php',{method:'POST',body:fd}); const j=await r.json();
    if(j.ok){
      isPub=j.is_public?1:0;
      const badge=document.getElementById('visBadge');
      badge.textContent = isPub? '🌍 Publik — tampil di galeri' : '🔒 Privat — tidak tampil di galeri';
      badge.className = 'inline-block px-3 py-1 rounded-full text-xs font-extrabold ' + (isPub? 'bg-green-100 text-green-700':'bg-gray-200 text-gray-700');
      document.getElementById('visInfo').textContent = isPub? '' : 'Link ini tetap bisa dibuka siapa pun yang punya.';
      document.getElementById('visBtn').textContent = isPub? '🔒 Jadikan privat' : '🌍 Tampilkan di galeri';
      boom();
    }
  }catch(e){}
}
boom();
</script>
<?php include 'includes/footer.php'; ?>
