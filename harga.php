<?php $title='Harga Sesi 💰 JarvisBooth'; include 'includes/header.php';
require_once 'config.php';
$frames=[]; if(db_ok()){ global $conn; $r=$conn->query("SELECT * FROM frames WHERE is_active=1 ORDER BY nama"); if($r) while($row=$r->fetch_assoc()) $frames[]=$row; }
$price=5000; $mins=10;
if(db_ok()){ global $conn; $r=$conn->query("SELECT kunci,nilai FROM settings WHERE kunci IN ('session_price','session_minutes')"); while($r&&$x=$r->fetch_assoc()){ if($x['kunci']==='session_price')$price=max(1000,intval($x['nilai'])); if($x['kunci']==='session_minutes')$mins=max(1,intval($x['nilai'])); } }
?>
<div class="py-8 text-center">
  <div class="inline-block px-4 py-1.5 rounded-full bg-white border-2 border-pink-200 font-bold text-sm shadow">💰 1 HARGA SESI — SEMUA FRAME TERMASUK 💰</div>
  <h1 class="text-4xl sm:text-5xl font-extrabold mt-3">Rp<?= number_format($price,0,',','.') ?> / <?= $mins ?> Menit 🤪</h1>
  <p class="font-semibold opacity-70">Foto <b>sepuasnya</b> selama timer jalan • Semua frame kartun <b class="text-green-600">termasuk</b> • Bayar via Tripay (QRIS/VA/e-wallet).</p>
  <a href="sesi.php" class="btn-gemoy inline-block mt-4 px-10 py-4 rounded-2xl bg-black text-white text-xl font-extrabold">🚀 Bikin Sesi Sekarang</a>
  <h2 class="text-2xl font-extrabold mt-8">Frame yang bisa dipakai (semua termasuk) 🎨</h2>
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 mt-4 text-left">
  <?php foreach($frames as $f): ?>
    <div class="card-gemoy p-3 ring-2 ring-green-300">
      <div class="rounded-2xl h-28 grid place-items-center text-5xl" style="background:<?= htmlspecialchars($f['bg']) ?>;color:<?= htmlspecialchars($f['fg']) ?>"><?= htmlspecialchars($f['deco']) ?></div>
      <div class="font-extrabold mt-2 text-sm"><?= htmlspecialchars($f['nama']) ?></div>
      <div class="text-xs opacity-60 font-bold"><?= htmlspecialchars($f['kategori']) ?> • <?= htmlspecialchars($f['layout']) ?></div>
      <div class="mt-1 font-extrabold text-green-600">✅ Termasuk</div>
    </div>
  <?php endforeach; ?>
  </div>
  <div class="card-gemoy p-4 mt-6 text-sm font-bold text-left">🤖 Kata Jarvis: yang dibayar cuma sesinya. Frame, filter, stiker, ukuran export — semua bebas!</div>
</div>
<?php include 'includes/footer.php'; ?>
