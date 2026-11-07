<?php
require_once 'includes/auth_user.php'; user_require();
$title = 'Transaksiku 💳 JarvisBooth';
include 'includes/header.php';
$me = user_current();
?>
<div class="py-8 max-w-3xl mx-auto">
  <div class="text-center">
    <h1 class="text-4xl font-extrabold">Transaksiku 💳</h1>
    <p class="font-semibold opacity-70 text-sm mt-1">Halo <b><?= htmlspecialchars($me['nama']) ?></b>! Semua sesi + status bayar + hasil fotomu ada di sini. Refresh aman 😌</p>
  </div>
  <div id="list" class="grid gap-3 mt-5"><div class="card-gemoy p-6 text-center font-bold opacity-60">⏳ Loading...</div></div>
  <div class="text-center mt-4"><a href="sesi.php" class="btn-gemoy inline-block px-8 py-3 rounded-2xl bg-black text-white font-extrabold">+ Sesi Baru 🚀</a></div>
</div>
<script>
async function load(){
  const box=document.getElementById('list');
  try{
    const r=await fetch('api/my_transactions.php'); const j=await r.json();
    if(!j.ok||!j.data.length){ box.innerHTML='<div class="card-gemoy p-8 text-center"><div class="text-5xl">🕸️</div><div class="font-extrabold mt-2">Belum ada transaksi</div><a href="sesi.php" class="inline-block mt-2 px-6 py-2.5 rounded-full bg-pink-500 text-white text-sm font-extrabold">Bikin sesi pertama →</a></div>'; return; }
    box.innerHTML=j.data.map(s=>{
      const st = s.status==='active' ? `<span class="px-2.5 py-1 rounded-full bg-green-100 text-green-700 text-xs font-extrabold">✅ Aktif • sisa ${fmtSisa(s.sisa)}</span>`
        : s.status==='expired' ? `<span class="px-2.5 py-1 rounded-full bg-gray-200 text-gray-600 text-xs font-extrabold">⏰ Habis</span>`
        : `<span class="px-2.5 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-extrabold">💰 Belum bayar</span>`;
      const o = s.order ? `<div class="text-xs font-bold opacity-70 mt-1">${s.order.merchant_ref} • Rp${Number(s.order.amount).toLocaleString('id-ID')} • ${s.order.channel} • ${s.order.status}</div>` : '';
      const th = (s.thumbs||[]).map(p=>`<a href="result.php?code=${p.code}"><img src="${p.image_path}" class="w-full aspect-square object-cover rounded-xl border" loading="lazy"></a>`).join('');
      const aksi = s.status==='active' ? `<a href="booth.php?session=${s.code}" class="px-4 py-2 rounded-full bg-black text-white text-xs font-extrabold">📸 Masuk Booth</a>`
        : s.status==='expired' ? `<a href="sesi.php" class="px-4 py-2 rounded-full bg-gradient-to-r from-pink-500 to-purple-500 text-white text-xs font-extrabold">+ Perpanjang</a>`
        : `<a href="sesi.php?code=${s.code}" class="px-4 py-2 rounded-full bg-pink-500 text-white text-xs font-extrabold">💰 Lanjut Bayar</a>`;
      return `<div class="card-gemoy p-4"><div class="flex flex-wrap items-center gap-2"><b class="tracking-widest">${s.code}</b>${st}<span class="text-xs font-bold opacity-60">${s.orientation} • ${s.mode} • 📷 ${s.nFoto} foto</span><span class="ml-auto flex gap-2">${aksi}</span></div>${o}${th?`<div class="grid grid-cols-3 gap-2 mt-2">${th}</div>`:''}</div>`;
    }).join('');
  }catch(e){ box.innerHTML='<div class="card-gemoy p-6 text-center font-bold">❌ Gagal load. Refresh ya!</div>'; }
}
function fmtSisa(s){ s=Math.max(0,parseInt(s||0)); return Math.floor(s/60)+':'+String(s%60).padStart(2,'0'); }
load(); setInterval(load,10000);
</script>
<?php include 'includes/footer.php'; ?>
