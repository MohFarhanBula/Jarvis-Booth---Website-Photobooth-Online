<?php $title='Bayar 💰 JarvisBooth'; include 'includes/header.php';
$ref = preg_replace('/[^A-Za-z0-9-]/','', $_GET['ref'] ?? '');
?>
<div class="py-8 max-w-lg mx-auto">
  <h1 class="text-3xl font-extrabold text-center">💰 Bayar via Tripay</h1>
  <div id="box" class="card-gemoy p-5 mt-4 text-center">⏳ Loading order <?= htmlspecialchars($ref) ?>...</div>
  <div class="text-center mt-3"><a href="harga.php" class="text-sm font-bold opacity-60">← balik ke harga</a></div>
</div>
<script>
const REF="<?= $ref ?>";
async function load(){
  if(!REF){ document.getElementById('box').textContent='Ref kosong!'; return; }
  try{
    const r=await fetch('api/order_status.php?ref='+REF); const j=await r.json();
    if(!j.ok){ document.getElementById('box').textContent='Order tidak ketemu 😭'; return; }
    const o=j.order;
    let h=`<div class="font-extrabold text-lg">${REF}</div><div class="text-sm opacity-70">Frame: ${o.frame_code} • Rp${Number(o.amount).toLocaleString('id-ID')} • ${o.channel}</div>`;
    h+=`<div class="mt-2 inline-block px-4 py-1.5 rounded-full font-extrabold text-sm ${o.status==='PAID'?'bg-green-500 text-white':'bg-orange-100 text-orange-700'}">${o.status}</div>`;
    if(o.qr_url) h+=`<div><img src="${o.qr_url}" class="w-52 h-52 mx-auto rounded-2xl border mt-3"></div><div class="text-xs font-bold mt-1">Scan QRIS pakai e-wallet / m-banking</div>`;
    else if(o.pay_code) h+=`<div class="mt-3 text-2xl font-extrabold tracking-widest bg-gray-100 rounded-2xl py-3">${o.pay_code}</div>`;
    if(o.checkout_url && o.status!=='PAID') h+=`<a href="${o.checkout_url}" target="_blank" class="inline-block mt-3 px-6 py-2.5 rounded-full bg-pink-500 text-white font-extrabold text-sm">Buka halaman bayar Tripay →</a>`;
    if(o.status==='PAID') h+=`<div class="mt-3 font-extrabold text-green-600">✅ Lunas! Frame kebuka di HP ini. <a href="booth.php" class="underline">Gas foto →</a></div><script>try{const p=JSON.parse(localStorage.getItem('jb_paid')||'[]');if(!p.includes('${o.frame_code}')){p.push('${o.frame_code}');localStorage.setItem('jb_paid',JSON.stringify(p));}}catch(e){}<\/script>`;
    else h+=`<div class="text-xs opacity-60 font-bold mt-2">Auto-refresh tiap 5 detik...</div>`;
    document.getElementById('box').innerHTML=h;
    if(o.status!=='PAID') setTimeout(load,5000);
    else boom();
  }catch(e){ document.getElementById('box').textContent='Gagal load. Coba refresh!'; }
}
load();
</script>
<?php include 'includes/footer.php'; ?>
