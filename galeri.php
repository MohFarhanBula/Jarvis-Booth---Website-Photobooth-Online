<?php $title='Galeri 🏆 JarvisBooth'; include 'includes/header.php'; ?>
<div class="py-6">
  <div class="flex flex-wrap items-end justify-between gap-2">
    <div><h1 class="text-4xl font-extrabold">Hall of Fame Terkocak 🏆</h1><p class="font-semibold opacity-70">Like yang paling absurd biar jadi legenda.</p></div>
    <div class="flex gap-2 text-sm font-bold">
      <button onclick="load('baru')" id="bBaru" class="px-4 py-2 rounded-full bg-black text-white">🆕 Terbaru</button>
      <button onclick="load('top')" id="bTop" class="px-4 py-2 rounded-full bg-white border-2">🔥 Paling Disuka</button>
      <a href="booth.php" class="px-4 py-2 rounded-full bg-pink-500 text-white">+ Ikutan Foto 📸</a>
    </div>
  </div>
  <div id="grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 mt-4"></div>
  <div id="empty" class="hidden card-gemoy p-10 text-center mt-4"><div class="text-6xl">🕸️</div><div class="font-extrabold text-xl mt-2">Masih kosong melompong!</div><div class="text-sm opacity-70">Jadilah yang pertama mengabadikan kegemoyan.</div><a href="booth.php" class="inline-block mt-3 px-6 py-3 rounded-2xl bg-black text-white font-extrabold">Gas Foto Pertama 🚀</a></div>
</div>
<script>
async function load(sort='baru'){
  document.getElementById('bBaru').className = 'px-4 py-2 rounded-full '+(sort==='baru'?'bg-black text-white':'bg-white border-2');
  document.getElementById('bTop').className = 'px-4 py-2 rounded-full '+(sort==='top'?'bg-black text-white':'bg-white border-2');
  const g=document.getElementById('grid'); g.innerHTML='<div class="col-span-full text-center py-10 font-bold opacity-60">⏳ Jarvis lagi ngeload aib...</div>';
  try{
    const r=await fetch('api/list.php?sort='+sort+'&limit=40'); const j=await r.json();
    if(!j.data.length){ document.getElementById('empty').classList.remove('hidden'); g.innerHTML=''; return; }
    document.getElementById('empty').classList.add('hidden');
    g.innerHTML=j.data.map((p,i)=>`
      <div class="card-gemoy p-2 ${i%3===1?'rotate-[1deg]':i%3===2?'rotate-[-1deg]':''}">
        <a href="result.php?code=${p.code}"><img loading="lazy" src="${p.image_path}" class="rounded-xl w-full aspect-[3/4] object-cover hover:scale-[1.02] transition"></a>
        <div class="flex items-center justify-between px-1 py-1.5 text-xs font-extrabold">
          <span class="truncate">${p.caption?escapeHtml(p.caption):'#'+p.code}</span>
          <button onclick="like('${p.code}',this)" class="shrink-0 px-2 py-1 rounded-full bg-pink-100 hover:bg-pink-200">❤️ ${p.likes}</button>
        </div>
      </div>`).join('');
  }catch(e){ g.innerHTML='<div class="col-span-full text-center">Gagal load. Cek XAMPP MySQL udah nyala belum?</div>'; }
}
function escapeHtml(s){return s.replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
async function like(code,btn){ try{ const fd=new FormData(); fd.append('code',code); const r=await fetch('api/like.php',{method:'POST',body:fd}); const j=await r.json(); if(j.ok){ btn.textContent='❤️ '+j.likes; boom(); } }catch(e){} }
load('baru');
</script>
<?php include 'includes/footer.php'; ?>
