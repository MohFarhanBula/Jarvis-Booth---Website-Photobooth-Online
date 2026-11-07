<?php
require_once 'includes/auth_user.php'; user_require();
$code = preg_replace('/[^A-Z0-9]/','', strtoupper($_GET['code'] ?? ''));
$title = $code ? "Sesi $code 💰 JarvisBooth" : 'Sesi Foto 5rb/10 mnt 💰 JarvisBooth';
include 'includes/header.php';
$me = user_current();
$multi = '0';
if (db_ok()) { global $conn; $r=$conn->query("SELECT nilai FROM settings WHERE kunci='multidevice_enabled' LIMIT 1"); if($r&&$x=$r->fetch_assoc()) $multi=$x['nilai']; }
$multi_on = $multi === '1';
?>
<?php if (!$code): ?>
<div class="py-8 max-w-6xl mx-auto">
  <div class="text-center">
    <div class="inline-block px-4 py-1.5 rounded-full bg-white border-2 border-pink-200 font-bold text-sm shadow floaty">💰 5RB / 10 MENIT • FOTO SEPUASNYA • SEMUA FRAME TERMASUK 💰</div>
    <h1 class="text-4xl sm:text-5xl font-extrabold mt-3">Sesi Foto 📸</h1>
    <p class="font-semibold opacity-70 mt-2">Halo <b><?= htmlspecialchars($me['nama']) ?></b>! Pilih orientasi, pilih frame, kamera langsung nyala. Klik <b>CEKREK</b> → bayar → langsung motret!</p>
  </div>

  <div class="grid lg:grid-cols-[1fr_380px] gap-4 mt-5 items-start">
  <div class="card-gemoy p-4 min-w-0">
    <div class="font-extrabold">📷 Kamera <span class="text-xs opacity-60">(langsung nyala — frame langsung nempel ✨)</span></div>
    <div id="camBox" class="relative rounded-2xl overflow-hidden bg-black mt-2 mx-auto w-full" style="aspect-ratio:3/4;max-height:72vh">
      <video id="cam" autoplay playsinline muted class="mirror absolute inset-0 w-full h-full object-cover"></video>
      <div id="pvTop" class="absolute top-0 left-0 right-0 text-center font-extrabold py-1.5 pointer-events-none" style="font-size:13px"></div>
      <img id="pvOverlay" class="absolute inset-0 w-full h-full pointer-events-none" style="object-fit:fill;display:none" alt="">
      <div id="pvBottom" class="absolute bottom-0 left-0 right-0 text-center font-bold py-1 pointer-events-none" style="font-size:11px"></div>
      <div id="flash" class="absolute inset-0 bg-white opacity-0 pointer-events-none" style="z-index:30"></div>
      <div id="count" class="absolute inset-0 hidden place-items-center" style="z-index:30"><div class="count-big font-extrabold text-white" id="countNum">3</div></div>
      <div class="absolute bottom-3 left-3 right-3 flex gap-2 text-sm font-bold" style="z-index:40">
        <select id="camSel" class="flex-1 min-w-0 px-3 py-2 rounded-full bg-white/95"></select>
        <button onclick="startCam(true)" class="px-3 py-2 rounded-full bg-white/95">🔄</button>
      </div>
    </div>
    <div id="err" class="hidden mt-2 p-3 rounded-2xl bg-red-50 border-2 border-red-200 text-xs font-bold"></div>
    <button id="cekrekBtn" onclick="cekrek()" class="btn-gemoy mt-3 w-full px-6 py-4 rounded-2xl bg-gradient-to-r from-pink-500 to-purple-500 text-white text-xl font-extrabold">📸 CEKREK!</button>
    <div id="thumbs" class="grid grid-cols-4 gap-2 mt-2"></div>
    <button id="editBtn" onclick="goEdit()" class="hidden btn-gemoy mt-2 w-full px-5 py-3 rounded-2xl bg-black text-white font-extrabold">✨ Gas ke Editor →</button>
    <a id="roomBtn" href="#" class="hidden btn-gemoy mt-2 w-full px-5 py-3 rounded-2xl bg-black text-white font-extrabold text-center">📸 MASUK ROOM →</a>
  </div>

  <div class="card-gemoy p-4 min-w-0">
    <div class="font-extrabold text-sm">1️⃣ Orientasi <span class="text-xs opacity-60">(dikunci setelah bayar)</span></div>
    <div class="grid grid-cols-2 gap-2 mt-2">
      <button id="oP" onclick="setOri('portrait')" class="px-3 py-2.5 rounded-2xl border-2 font-extrabold text-sm">📱 Portrait</button>
      <button id="oL" onclick="setOri('landscape')" class="px-3 py-2.5 rounded-2xl border-2 font-extrabold text-sm">🖥️ Landscape</button>
    </div>
    <div class="font-extrabold mt-3 text-sm">2️⃣ Mode</div>
    <div class="grid grid-cols-3 gap-2 mt-2 text-sm">
      <button data-m="solo" class="mdBtn px-2 py-2.5 rounded-2xl border-2 font-extrabold text-xs">🧍 Solo</button>
      <button data-m="duo" class="mdBtn px-2 py-2.5 rounded-2xl border-2 font-extrabold text-xs opacity-60" data-locked="<?= $multi_on?'0':'1' ?>">💘 Duo<br><span class="text-[10px]">🔜 Segera Hadir</span></button>
      <button data-m="squad" class="mdBtn px-2 py-2.5 rounded-2xl border-2 font-extrabold text-xs opacity-60" data-locked="<?= $multi_on?'0':'1' ?>">🤪 Squad<br><span class="text-[10px]">🔜 Segera Hadir</span></button>
    </div>
    <div id="modeMsg" class="hidden mt-2 p-2.5 rounded-2xl bg-yellow-50 border-2 border-yellow-200 text-xs font-bold">🛠️ Mode bareng lagi dibenerin biar nggak lag! Main solo dulu ya 📸</div>
    <div class="font-extrabold mt-3 text-sm">3️⃣ Frame <span class="text-xs opacity-60">(klik, langsung nempel!)</span></div>
    <div id="framePick" class="grid grid-cols-2 xl:grid-cols-3 gap-2 mt-2 max-h-96 overflow-y-auto pr-1"></div>
  </div>
  </div>

  <div id="payBox" class="hidden card-gemoy p-5 mt-4 border-pink-300">
    <div class="font-extrabold text-lg">💰 Bayar dulu biar bisa cekrek!</div>
    <div id="payInfo" class="text-sm font-bold mt-1"></div>
    <div class="flex gap-2 mt-2">
      <select id="ch" class="flex-1 min-w-0 px-4 py-2.5 rounded-2xl border-2 font-bold text-sm"><option value="QRIS">QRIS</option><option value="QRISC">QRIS Custom</option><option value="DANA">DANA</option><option value="SHOPEEPAY">ShopeePay</option><option value="OVO">OVO</option><option value="BRIVA">BRI VA</option><option value="BNIVA">BNI VA</option><option value="BCAVA">BCA VA</option></select>
      <button onclick="buatBayar()" class="shrink-0 px-5 py-2.5 rounded-2xl bg-black text-white font-extrabold text-sm">Buat QR →</button>
    </div>
    <div id="payQR" class="text-center mt-2"></div>
    <div class="text-xs opacity-60 font-bold mt-1">Otomatis lanjut motret kalau sudah PAID. Dev: <button onclick="simul()" class="underline">🧪 simulasi bayar</button></div>
  </div>

  <div class="card-gemoy p-4 mt-4 opacity-70">
    <div class="font-extrabold text-sm">👥 Join sesi teman? <span class="text-[10px] bg-yellow-100 px-2 py-0.5 rounded-full">🔜 Segera Hadir</span></div>
    <p class="text-xs font-bold opacity-60 mt-1">Mode bareng lagi dibenerin biar nggak lag. Sementara bikin sesi solo sendiri ya! 📸</p>
  </div>
</div>
<script>
let ori='portrait', mode='solo', FRAMES=[], frameCode='', stream=null, shots=[], busy=false;
let SESI=null, SESI_MEMBER=0, paidTimer=null;
const $=id=>document.getElementById(id);
const NEED=()=> mode==='squad'?4 : mode==='duo'?2 : 1;
function setOri(o){ if(SESI&&SESI.paid){ alert('Orientasi dikunci setelah bayar 🔒'); return; } ori=o; paint(); paintPreview(); }
document.querySelectorAll('.mdBtn').forEach(b=>b.onclick=()=>{
  if(b.dataset.locked==='1'){ const m=$('modeMsg'); m.classList.remove('hidden'); clearTimeout(window._mmT); window._mmT=setTimeout(()=>m.classList.add('hidden'),3000); boom(); return; }
  mode=b.dataset.m; paint(); renderFrames(); renderShots();
});
function paint(){
  $('oP').style.background=ori==='portrait'?'#111':'#fff'; $('oP').style.color=ori==='portrait'?'#fff':'#111';
  $('oL').style.background=ori==='landscape'?'#111':'#fff'; $('oL').style.color=ori==='landscape'?'#fff':'#111';
  document.querySelectorAll('.mdBtn').forEach(b=>{ const on=b.dataset.m===mode; b.style.background=on?'#ec4899':'#fff'; b.style.color=on?'#fff':'#111'; });
}
async function loadFrames(){
  try{ const r=await fetch('api/frames.php'); const j=await r.json(); if(j.ok&&j.data.length){ FRAMES=j.data; frameCode=FRAMES[0].code; } }catch(e){}
  renderFrames();
}
function renderFrames(){
  $('framePick').innerHTML = FRAMES.map(f=>{
    const th = pickOverlay(f) || f.overlay_path || '';
    const img = th ? `<img src="${th}" class="w-full h-16 object-cover rounded-xl border" loading="lazy" onerror="this.style.display='none'">` : `<div class="w-full h-16 rounded-xl grid place-items-center text-3xl border" style="background:${f.bg}">${f.deco||'🎨'}</div>`;
    return `<button onclick="frameCode='${f.code}';renderFrames();paintPreview()" class="rounded-2xl border-2 p-1 text-left" style="border-color:${frameCode===f.code?'#ec4899':'#eee'};background:${frameCode===f.code?'#fdf2f8':'#fff'}">${img}<div class="text-[11px] font-extrabold mt-1 px-0.5 truncate">${f.nama}</div></button>`;
  }).join('')||'<span class="text-xs opacity-60">Loading...</span>';
  paintPreview();
}
function parseOv(v){ try{ const o=typeof v==='string'?JSON.parse(v):v; return (o&&typeof o==='object')?o:{}; }catch(e){ return {}; } }
// overlay sesuai template (berdasar mode) + orientasi
function pickOverlay(f){
  const tpl = mode==='squad'?'strip4':mode==='duo'?'polaroid':'single';
  const o = parseOv(f.overlays), ol = ori==='landscape'?'l':'p';
  if(o[tpl]&&o[tpl][ol]) return o[tpl][ol];
  if(o[tpl]&&o[tpl].p) return o[tpl].p;
  return f.overlay_path||'';
}
// preview live: frame yang dipilih langsung ngecover kamera (stretch penuh = kayak hasil edit)
function paintPreview(){
  const box=$('camBox'); if(box) box.style.aspectRatio = ori==='landscape' ? '4/3' : '3/4';
  const f = FRAMES.find(x=>x.code===frameCode);
  const top=$('pvTop'), bot=$('pvBottom'), ov=$('pvOverlay');
  if(!f){ if(top)top.textContent=''; if(bot)bot.textContent=''; if(ov)ov.style.display='none'; return; }
  if(top){ top.textContent=`${f.deco||'⭐'} JARVISBOOTH ${f.deco||'⭐'}`; top.style.background=f.bg||'transparent'; top.style.color=f.fg||'#fff'; }
  if(bot){ bot.textContent='@jarvisbooth'; bot.style.background=(f.bg||'#000')+'cc'; bot.style.color=f.fg||'#fff'; }
  if(ov){
    const src = pickOverlay(f);
    if(src){ ov.style.display='block'; if(ov.dataset.src!==src){ ov.dataset.src=src; ov.src=src; } }
    else { ov.style.display='none'; ov.dataset.src=''; ov.removeAttribute('src'); }
  }
}
// kamera langsung nyala perangkat default, daftar susulan
async function startCam(manual){
  try{
    if(stream) stream.getTracks().forEach(t=>t.stop());
    const devId = manual ? $('camSel').value : '';
    stream = await navigator.mediaDevices.getUserMedia(devId?{video:{deviceId:{exact:devId},width:{ideal:1280}}}:{video:{width:{ideal:1280}},audio:false});
    $('cam').srcObject=stream; $('err').classList.add('hidden');
    const devs=await navigator.mediaDevices.enumerateDevices();
    const vids=devs.filter(d=>d.kind==='videoinput');
    const cur=$('camSel').value;
    $('camSel').innerHTML=vids.map((d,i)=>`<option value="${d.deviceId}">${d.label||'Kamera '+(i+1)}</option>`).join('')||'<option value="">Kamera default</option>';
    if(cur) $('camSel').value=cur;
  }catch(e){ $('err').classList.remove('hidden'); $('err').textContent='😭 Kamera keblokir / tidak ada. Klik ikon kunci di address bar → Allow camera.'; }
}
$('camSel').onchange=()=>startCam(true);
function beep(f){ try{ const c=new (window.AudioContext||window.webkitAudioContext)(); const o=c.createOscillator(),g=c.createGain(); o.connect(g);g.connect(c.destination);o.frequency.value=f;g.gain.value=.15;o.start();o.stop(c.currentTime+.12);}catch(e){} }
async function cekrek(){
  if(busy) return;
  if(!SESI || !SESI.paid){ // gate pembayaran, state tetap
    $('payBox').classList.remove('hidden');
    $('payInfo').textContent=`Sesi ${ori} • ${mode} • Rp5.000 / 10 menit. Pilih channel terus Buat QR.`;
    $('payBox').scrollIntoView({behavior:'smooth'});
    return;
  }
  shoot();
}
async function buatBayar(){
  $('payInfo').textContent='⏳ Bikin sesi + QR Tripay...';
  const fd=new FormData(); fd.append('orientation',ori); fd.append('mode',mode); fd.append('channel',$('ch').value);
  try{
    const r=await fetch('api/session_create.php',{method:'POST',body:fd}); const j=await r.json();
    if(!j.code) throw new Error(j.msg||'gagal');
    SESI={code:j.code,paid:false}; SESI_MEMBER=j.member_id||0;
    localStorage.setItem('jb_room_'+j.code, JSON.stringify({member_id:j.member_id,slot:0}));
    localStorage.setItem('jb_session', j.code);
    if(!j.ok){ $('payInfo').textContent='⚠️ '+j.msg; }
    else {
      let h='';
      if(j.qr_url) h+=`<img src="${j.qr_url}" class="w-44 h-44 mx-auto rounded-2xl border">`;
      else if(j.pay_code) h+=`<div class="text-xl font-extrabold tracking-widest bg-gray-100 rounded-2xl py-2 px-4">${j.pay_code}</div>`;
      if(j.checkout_url) h+=`<a href="${j.checkout_url}" target="_blank" class="inline-block mt-2 px-5 py-2 rounded-full bg-pink-500 text-white text-sm font-extrabold">Bayar via Tripay →</a>`;
      $('payQR').innerHTML=h;
      $('payInfo').textContent='Sesi '+j.code+' • Rp'+Number(j.amount).toLocaleString('id-ID')+' • nunggu PAID...';
      clearInterval(paidTimer); paidTimer=setInterval(checkPaid,2000);
    }
  }catch(e){ $('payInfo').textContent='❌ '+e.message; }
}
async function checkPaid(){
  if(!SESI) return;
  try{
    const r=await fetch('api/session_status.php?code='+SESI.code); const j=await r.json();
    if(j.ok&&j.session.status==='active'){
      clearInterval(paidTimer); SESI.paid=true;
      try{ localStorage.setItem('jb_paid_session', SESI.code); }catch(e){}
      $('payBox').classList.add('hidden'); boom();
      $('payInfo').textContent='';
      if(mode==='solo'){ shoot(); }
      else { const rb=$('roomBtn'); rb.classList.remove('hidden'); rb.href='booth.php?session='+SESI.code; rb.textContent='✅ LUNAS! MASUK BOOTH →'; rb.scrollIntoView({behavior:'smooth'}); }
    }
  }catch(e){}
}
async function simul(){
  if(!SESI||!SESI.code){ alert('Buat QR dulu!'); return; }
  const fd=new FormData(); fd.append('code',SESI.code);
  await fetch('api/session_simulate.php',{method:'POST',body:fd}); checkPaid();
}
function grab(){
  const v=$('cam'), c=document.createElement('canvas');
  c.width=v.videoWidth||640; c.height=v.videoHeight||480;
  const x=c.getContext('2d'); x.save(); x.translate(c.width,0); x.scale(-1,1); x.drawImage(v,0,0,c.width,c.height); x.restore();
  return c.toDataURL('image/jpeg',0.9);
}
async function shoot(){
  if(!$('cam').srcObject){ alert('Kamera belum nyala 😭'); return; }
  busy=true; $('cekrekBtn').disabled=true;
  while(shots.length<NEED()){
    for(let n=3;n>=1;n--){ $('count').style.display='grid'; $('countNum').textContent=n; beep(600+n*150); await new Promise(r=>setTimeout(r,700)); }
    $('countNum').textContent='📸'; beep(1400);
    const f=$('flash'); f.style.transition='none'; f.style.opacity='1'; requestAnimationFrame(()=>{f.style.transition='opacity .4s';f.style.opacity='0';});
    await new Promise(r=>setTimeout(r,300)); $('count').style.display='none';
    shots.push(grab()); if(shots.length>NEED()) shots=shots.slice(-NEED()); renderShots();
    await new Promise(r=>setTimeout(r,700));
  }
  busy=false; $('cekrekBtn').disabled=false; boom();
  $('editBtn').classList.remove('hidden');
}
function renderShots(){
  $('thumbs').innerHTML=shots.map((s,i)=>`<div class="rounded-xl overflow-hidden border-2 border-pink-200"><img src="${s}" class="w-full aspect-[4/3] object-cover"></div>`).join('');
  if(shots.length) $('editBtn').classList.remove('hidden');
}
function goEdit(){
  if(!shots.length) return;
  try{ localStorage.setItem('jb_shots',JSON.stringify(shots)); }catch(e){ alert('Foto kegedean, coba lebih sedikit!'); return; }
  localStorage.setItem('jb_mode', mode==='squad'?'strip4':mode==='duo'?'polaroid':'single');
  if(frameCode) localStorage.setItem('jb_want_frame',frameCode);
  localStorage.setItem('jb_ori',ori);
  if(SESI&&SESI.code) localStorage.setItem('jb_session',SESI.code);
  location.href='edit.php';
}
paint(); loadFrames();
$('pvOverlay').onerror=()=>{ $('pvOverlay').style.display='none'; };
// refresh-safe: kalau sesi ini sudah PAID sebelumnya, langsung aktif lagi
(async function restorePaid(){
  let c=null; try{ c=localStorage.getItem('jb_paid_session'); }catch(e){}
  if(!c) return;
  try{
    const r=await fetch('api/session_status.php?code='+c); const j=await r.json();
    if(j.ok&&j.session.status==='active'&&j.is_owner){
      SESI={code:c,paid:true};
      if(j.session.orientation&&(j.session.orientation!==ori)){ ori=j.session.orientation; }
      if(j.session.mode){ mode=j.session.mode; }
      paint();
    }
  }catch(e){}
})();
if(navigator.mediaDevices?.getUserMedia) startCam(false);
else { $('err').classList.remove('hidden'); $('err').textContent='Browser tidak support kamera.'; }
</script>
<?php include 'includes/footer.php'; exit; endif; ?>

<div class="py-8 max-w-xl mx-auto text-center" data-sesi="<?= htmlspecialchars($code) ?>">
  <h1 class="text-3xl font-extrabold">Sesi <span class="bg-black text-white px-3 py-1 rounded-2xl tracking-widest"><?= htmlspecialchars($code) ?></span></h1>
  <div id="timer" class="mt-2 inline-block px-5 py-2 rounded-full bg-gray-100 font-extrabold">⏳ ...</div>
  <div id="box" class="card-gemoy p-5 mt-4 text-left">⏳ Loading...</div>
  <div class="mt-3 flex gap-2 justify-center text-sm font-bold">
    <a href="sesi.php" class="px-4 py-2 rounded-full bg-white border-2">+ Sesi baru</a>
    <a href="transaksi.php" class="px-4 py-2 rounded-full bg-candy-lemon border-2">💳 Transaksiku</a>
  </div>
  <div id="cp" class="text-xs font-bold text-green-600 h-4"></div>
</div>
<script>
const CODE="<?= $code ?>";
async function poll(){
  try{
    const r=await fetch('api/session_status.php?code='+CODE); const j=await r.json();
    if(!j.ok){ document.getElementById('box').textContent='Sesi tidak ketemu 😭'; return; }
    const s=j.session, o=j.order;
    if(s.status==='active'){
      const sisa=Math.max(0,parseInt(s.sisa||0)), m=Math.floor(sisa/60), d=sisa%60;
      document.getElementById('timer').textContent=`⏰ Sisa ${m}:${String(d).padStart(2,'0')} • ${s.orientation} • ${s.mode}`;
      document.getElementById('timer').className='mt-2 inline-block px-5 py-2 rounded-full font-extrabold '+(sisa<60?'bg-red-500 text-white':'bg-green-100 text-green-700');
      document.getElementById('box').innerHTML=`✅ <b>LUNAS!</b> Sesi aktif, foto sepuasnya sampai timer habis.<br><a href="booth.php?session=${CODE}" class="inline-block mt-3 px-8 py-3.5 rounded-2xl bg-black text-white font-extrabold">📸 MASUK BOOTH →</a>`;
    } else if(s.status==='expired'){
      document.getElementById('timer').textContent='⏰ Waktu habis!';
      document.getElementById('box').innerHTML=`Waktu 10 menit habis. Hasil yang sudah ada tetap bisa diedit/download. 😌<br><a href="sesi.php" class="inline-block mt-3 px-6 py-3 rounded-2xl bg-gradient-to-r from-pink-500 to-purple-500 text-white font-extrabold">+ Perpanjang 5rb / 10 mnt</a>`;
    } else {
      document.getElementById('timer').textContent='💰 Menunggu pembayaran...';
      let h=`Sesi <b>${CODE}</b> (${s.orientation} • ${s.mode}) — bayar <b>Rp${o?Number(o.amount).toLocaleString('id-ID'):'5.000'}</b> untuk aktifkan 10 menit.<br>`;
      if(o){
        if(o.qr_url) h+=`<img src="${o.qr_url}" class="w-44 h-44 mx-auto rounded-2xl border mt-2">`;
        else if(o.pay_code) h+=`<div class="mt-2 text-xl font-extrabold tracking-widest bg-gray-100 rounded-2xl py-2">${o.pay_code}</div>`;
        if(o.checkout_url) h+=`<a href="${o.checkout_url}" target="_blank" class="inline-block mt-2 px-5 py-2.5 rounded-full bg-pink-500 text-white font-extrabold text-sm">Bayar via Tripay →</a>`;
        h+=`<div class="text-xs opacity-60 mt-1">Auto-aktif kalau PAID. Dev: <button onclick="simul()" class="underline font-bold">🧪 simulasi bayar</button></div>`;
      } else h+=`<span class="text-xs">Order Tripay belum kebikin (cek setting Tripay admin).</span> <button onclick="simul()" class="underline font-bold text-xs">🧪 simulasi bayar</button>`;
      document.getElementById('box').innerHTML=h;
    }
  }catch(e){}
}
async function simul(){
  const fd=new FormData(); fd.append('code',CODE);
  await fetch('api/session_simulate.php',{method:'POST',body:fd}); poll();
}
setInterval(poll,2000); poll();
</script>
<?php include 'includes/footer.php'; ?>
