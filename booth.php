<?php
require_once 'includes/auth_user.php'; user_require();
$user = user_current();
// Booth solo hanya untuk sesi aktif berbayar — tanpa sesi dilempar ke sesi.php
$sess_code = preg_replace('/[^A-Z0-9]/','', strtoupper($_GET['session'] ?? ''));
if ($sess_code && db_ok()) {
  global $conn;
  $sc = $conn->real_escape_string($sess_code);
  $rs = $conn->query("SELECT code, owner_id, status, TIMESTAMPDIFF(SECOND,NOW(),expires_at) AS sisa FROM sessions WHERE code='$sc' LIMIT 1");
  $srow = $rs ? $rs->fetch_assoc() : null;
  if (!$srow || $srow['status']!=='active' || intval($srow['sisa']??0)<=0 || intval($srow['owner_id'])!==intval($user['id'])) $sess_code='';
}
if (!$sess_code) { header('Location: sesi.php'); exit; }
$mode = $_GET['mode'] ?? 'strip4';
if (!in_array($mode, ['strip4','polaroid','single'], true)) $mode = 'strip4';
$frame_req = preg_replace('/[^a-z0-9-]/','', strtolower($_GET['frame'] ?? ''));
$need = ['strip4'=>4,'polaroid'=>2,'single'=>1][$mode];
$title = 'Booth 📷 Mode ' . strtoupper($mode);
include 'includes/header.php';
echo '<script>try{localStorage.setItem("jb_session","'.htmlspecialchars($sess_code).'")}catch(e){}</script>';
if ($frame_req) echo '<script>try{localStorage.setItem("jb_want_frame","'.htmlspecialchars($frame_req).'")}catch(e){}</script>';
?>
<div class="py-6">
  <a href="index.php" class="text-sm font-bold opacity-70 hover:opacity-100">← balik (jangan kabur!)</a>
  <div class="flex flex-wrap items-end justify-between gap-2 mt-1">
    <div>
      <h1 class="text-4xl font-extrabold">Ruang Foto 🤳 <span class="text-sm align-middle bg-pink-500 text-white px-3 py-1 rounded-full"><?= strtoupper($mode) ?> • <?= $need ?>x CEKREK</span></h1>
      <p id="joke" class="font-semibold opacity-70">Jarvis: posisiin muka di tengah, jangan cuma poni yang kelihatan!</p>
    </div>
    <div class="flex gap-2 text-sm font-bold">
      <select id="modeSel" class="px-3 py-2 rounded-full bg-white border-2" onchange="location.href='booth.php?session=<?= htmlspecialchars($sess_code) ?>&mode='+this.value">
        <option value="strip4" <?= $mode==='strip4'?'selected':'' ?>>🇰🇷 Strip 4</option>
        <option value="polaroid" <?= $mode==='polaroid'?'selected':'' ?>>💘 Polaroid 2</option>
        <option value="single" <?= $mode==='single'?'selected':'' ?>>🤡 Single 1</option>
      </select>
      <button id="mirrorBtn" class="px-3 py-2 rounded-full bg-white border-2">🪞 Mirror: ON</button>
    </div>
  </div>

  <div class="grid lg:grid-cols-[1fr_300px] gap-4 mt-4">
    <div class="card-gemoy p-4 relative overflow-hidden">
      <div class="relative rounded-2xl overflow-hidden bg-black aspect-[4/3]">
        <video id="cam" autoplay playsinline muted class="mirror w-full h-full object-cover"></video>
        <canvas id="snap" class="hidden"></canvas>
        <div id="flash" class="absolute inset-0 bg-white opacity-0 pointer-events-none"></div>
        <div id="count" class="absolute inset-0 hidden place-items-center"><div class="count-big font-extrabold text-white" id="countNum">3</div></div>
        <div id="poseLabel" class="absolute top-3 left-3 bg-black/70 text-white text-sm font-bold px-3 py-1.5 rounded-full">Pose 1/<?= $need ?>: Senyum Pede 😁</div>
        <div class="absolute bottom-3 left-3 right-3 flex gap-2 text-sm font-bold">
          <select id="camSel" class="flex-1 px-3 py-2 rounded-full bg-white/95"></select>
          <button onclick="startCam()" class="px-3 py-2 rounded-full bg-white/95">🔄</button>
        </div>
      </div>
      <div class="mt-3 flex flex-wrap gap-2">
        <button id="shootBtn" onclick="autoShoot()" class="btn-gemoy flex-1 min-w-[180px] px-6 py-4 rounded-2xl bg-gradient-to-r from-pink-500 to-purple-500 text-white text-xl font-extrabold">📸 CEKREK <?= $need ?>x OTOMATIS</button>
        <button onclick="singleShoot()" class="btn-gemoy px-6 py-4 rounded-2xl bg-candy-lemon font-extrabold border-2">1x aja 📷</button>
        <label class="btn-gemoy px-6 py-4 rounded-2xl bg-white border-2 font-extrabold cursor-pointer">📁 Upload <input id="upload" type="file" accept="image/*" multiple class="hidden"></label>
      </div>
      <div class="mt-2 flex items-center gap-3 text-sm font-bold">
        <label class="flex items-center gap-1"><input type="checkbox" id="autoNext" checked class="w-4 h-4"> Auto-lanjut ke edit ✨</label>
        <span class="opacity-60">Tips: cahaya dari depan, jangan backlight kayak mau audisi indosiar 💡</span>
      </div>
      <div id="err" class="hidden mt-3 p-3 rounded-2xl bg-red-50 border-2 border-red-200 text-sm font-bold"></div>
    </div>

    <div class="card-gemoy p-4">
      <div class="font-extrabold text-lg">Hasil Cekrek (<span id="got">0</span>/<?= $need ?>)</div>
      <div class="text-xs opacity-60 font-bold">Klik foto buat hapus / ulangi. Bisa campur kamera + upload.</div>
      <div id="thumbs" class="grid grid-cols-2 gap-2 mt-3"></div>
      <div class="mt-3 grid gap-2">
        <button id="toEdit" onclick="goEdit()" class="btn-gemoy px-4 py-3 rounded-2xl bg-black text-white font-extrabold disabled:opacity-40" disabled>Next: Edit + Stiker → ✨</button>
        <button onclick="resetShots()" class="px-4 py-2 rounded-full bg-gray-100 font-bold text-sm">🗑️ Ulangi semua</button>
      </div>
      <div class="mt-3 p-3 rounded-2xl bg-purple-50 text-xs font-bold">🤖 Jarvis bilang: <span id="joke2">foto ke-1 harus paling niat, sisanya boleh ngakak.</span></div>
    </div>
  </div>
</div>

<script>
const NEED = <?= $need ?>, MODE = "<?= $mode ?>";
const POSES = ["Senyum Pede 😁","Kaget Dikit 😲","Manyun Gemoy 😚","Ngakak Lepas 🤣","Cool Ala Idol 😎","Kedip Satu Mata 😉"];
let stream=null, shots=[], mirror=true, busy=false;

const $ = id => document.getElementById(id);
jarvisSay($('joke'));

async function listCams(){
  try{
    const devs = await navigator.mediaDevices.enumerateDevices();
    const vids = devs.filter(d=>d.kind==='videoinput');
    $('camSel').innerHTML = vids.map((d,i)=>`<option value="${d.deviceId}">${d.label||'Kamera '+(i+1)}</option>`).join('') || '<option>Kamera default</option>';
  }catch(e){}
}
async function startCam(){
  try{
    if(stream) stream.getTracks().forEach(t=>t.stop());
    const devId = $('camSel').value;
    stream = await navigator.mediaDevices.getUserMedia({ video: devId?{deviceId:{exact:devId},width:{ideal:1280}}:{width:{ideal:1280}}, audio:false });
    $('cam').srcObject = stream; $('err').classList.add('hidden');
    await listCams();
  }catch(e){
    $('err').classList.remove('hidden');
    $('err').innerHTML = '😭 Kamera keblokir! Klik ikon 🔒 di address bar → Allow camera.<br>Atau pakai tombol 📁 Upload foto aja, tetap bisa gas!';
  }
}
$('camSel').onchange = startCam;
$('mirrorBtn').onclick = ()=>{ mirror=!mirror; $('cam').classList.toggle('mirror',mirror); $('mirrorBtn').textContent = mirror?'🪞 Mirror: ON':'🪞 Mirror: OFF'; };
if(!navigator.mediaDevices?.getUserMedia){ $('err').classList.remove('hidden'); $('err').textContent='Browser tidak support kamera, pakai Upload ya!'; }
else { startCam(); listCams(); }

function beep(f=880,d=0.12){ try{ const ctx=new (window.AudioContext||window.webkitAudioContext)(); const o=ctx.createOscillator(),g=ctx.createGain(); o.connect(g);g.connect(ctx.destination);o.frequency.value=f;o.type='sine';g.gain.value=0.2;o.start();g.gain.exponentialRampToValueAtTime(0.001,ctx.currentTime+d);o.stop(ctx.currentTime+d);}catch(e){} }
function flash(){ const f=$('flash'); f.style.transition='none'; f.style.opacity='1'; requestAnimationFrame(()=>{ f.style.transition='opacity .4s'; f.style.opacity='0'; }); }
function grabFrame(){
  const v=$('cam'), c=$('snap');
  const w=v.videoWidth||640, h=v.videoHeight||480;
  c.width=w; c.height=h; const x=c.getContext('2d');
  x.save(); if(mirror){ x.translate(w,0); x.scale(-1,1);} x.drawImage(v,0,0,w,h); x.restore();
  return c.toDataURL('image/jpeg',0.92);
}
function render(){
  $('got').textContent = shots.length;
  $('poseLabel').textContent = shots.length>=NEED ? 'Komplit! Gas edit ✨' : `Pose ${shots.length+1}/${NEED}: ${POSES[shots.length%POSES.length]}`;
  $('thumbs').innerHTML = shots.map((s,i)=>`<button onclick="delShot(${i})" title="klik buat hapus" class="relative rounded-xl overflow-hidden border-2 border-pink-200 group"><img src="${s}" class="w-full aspect-[4/3] object-cover"><span class="absolute bottom-1 left-1 bg-black/70 text-white text-[11px] font-bold px-2 py-0.5 rounded-full">#${i+1}</span><span class="absolute inset-0 hidden group-hover:grid place-items-center bg-black/40 text-white font-bold">❌ hapus</span></button>`).join('') || '<div class="col-span-2 text-center text-sm opacity-50 py-8">Belum ada. Pencet CEKREK! 📸</div>';
  $('toEdit').disabled = shots.length===0;
  $('toEdit').textContent = shots.length>=NEED ? 'Next: Edit + Stiker → ✨ (komplit!)' : `Next: Edit (${shots.length}/${NEED}) →`;
}
function delShot(i){ shots.splice(i,1); render(); }
function resetShots(){ shots=[]; render(); jarvisSay($('joke2')); }
function singleShoot(){
  if(busy) return;
  if($('cam').srcObject){ shots.push(grabFrame()); if(shots.length>NEED) shots=shots.slice(-NEED); flash(); beep(1200); }
  else { $('upload').click(); return; }
  render(); if(shots.length>=NEED && $('autoNext').checked) setTimeout(goEdit, 600);
}
async function autoShoot(){
  if(busy) return;
  if(!$('cam').srcObject){ $('err').classList.remove('hidden'); $('err').textContent='Nyalain kamera dulu atau pakai Upload 📁'; $('upload').click(); return; }
  busy=true; $('shootBtn').disabled=true;
  // isi sampai NEED
  while(shots.length<NEED){
    for(let n=3;n>=1;n--){
      $('count').style.display='grid'; $('countNum').textContent=n; beep(600+n*150); jarvisSay($('joke'));
      await new Promise(r=>setTimeout(r,750));
      $('countNum').classList.remove('shake'); void $('countNum').offsetWidth; $('countNum').classList.add('shake');
    }
    $('countNum').textContent='📸'; beep(1400,0.25); flash(); boomMini();
    await new Promise(r=>setTimeout(r,350));
    $('count').style.display='none';
    shots.push(grabFrame()); render();
    await new Promise(r=>setTimeout(r,800));
  }
  busy=false; $('shootBtn').disabled=false; boom();
  if($('autoNext').checked) setTimeout(goEdit, 800);
}
function boomMini(){ if(window.confetti) confetti({particleCount:40,spread:60,origin:{y:.6},scalar:.8}); }
$('upload').onchange = e=>{
  const files=[...e.target.files].slice(0,NEED);
  if(!files.length) return;
  let done=0;
  files.forEach(f=>{ const r=new FileReader(); r.onload=ev=>{ shots.push(ev.target.result); if(shots.length>NEED) shots=shots.slice(-NEED); if(++done===files.length) render(); }; r.readAsDataURL(f); });
  e.target.value='';
};
function goEdit(){
  if(!shots.length){ alert('Foto dulu gih, masa edit angin? 😭'); return; }
  try{ localStorage.setItem('jb_shots', JSON.stringify(shots)); localStorage.setItem('jb_mode', MODE); }
  catch(e){ alert('Foto kegedean buat browser, coba 2 foto dulu ya!'); return; }
  location.href='edit.php';
}
render();
</script>
<?php include 'includes/footer.php'; ?>
