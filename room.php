<?php
require_once 'includes/auth_user.php'; user_require();
// MODE BARENG DIMATIKAN SEMENTARA (lag) — ubah settings.multidevice_enabled=1 untuk buka lagi
$multi_room = '0';
if (db_ok()) { global $conn; $r=$conn->query("SELECT nilai FROM settings WHERE kunci='multidevice_enabled' LIMIT 1"); if($r&&$x=$r->fetch_assoc()) $multi_room=$x['nilai']; }
$code = preg_replace('/[^A-Z0-9]/','', strtoupper($_GET['code'] ?? ''));
$title = $code ? "Room $code 💘 JarvisBooth" : 'Duo / Squad LDR 💘 JarvisBooth';
include 'includes/header.php';
if ($multi_room !== '1'):
?>
<div class="py-16 max-w-md mx-auto text-center">
  <div class="card-gemoy p-8">
    <div class="text-7xl">🛠️</div>
    <h1 class="text-3xl font-extrabold mt-3">Mode Bareng Lagi Dibenerin!</h1>
    <p class="font-semibold opacity-70 text-sm mt-2">Kemarin patah-patah & kameranya rebutan 😅 Jarvis lagi perbaikin biar mulus. Sementara main <b>solo</b> dulu ya — tetap seru kok!</p>
    <a href="sesi.php" class="btn-gemoy inline-block mt-4 px-8 py-3.5 rounded-2xl bg-black text-white font-extrabold">📸 Ke Sesi Solo →</a>
  </div>
</div>
<?php include 'includes/footer.php'; exit; endif;
?>
<script src="https://unpkg.com/peerjs@1.5.4/dist/peerjs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<?php if (!$code): ?>
<!-- PILIH MODE -->
<div class="py-8 max-w-2xl mx-auto text-center">
  <div class="inline-block px-4 py-1.5 rounded-full bg-white border-2 border-pink-200 font-bold text-sm shadow floaty">💘 MODE LDR — 2 HP, 1 KENANGAN 💘</div>
  <h1 class="text-4xl sm:text-5xl font-extrabold mt-3">Foto Bareng <span class="text-pink-500">Walau Jauh</span> 🥹</h1>
  <p class="font-semibold opacity-70 mt-2">Room bagian dari <b>sesi berbayar</b> — bikin sesi dulu (5rb/10 mnt), baru share kode ke teman. Punya kode? langsung join di bawah (tetap wajib login).</p>
  <div class="card-gemoy p-5 mt-5 text-center">
    <a href="sesi.php" class="btn-gemoy inline-block px-8 py-4 rounded-2xl bg-gradient-to-r from-pink-500 to-purple-500 text-white text-xl font-extrabold">💰 Bikin Sesi Baru →</a>
    <div class="mt-4 flex gap-2 text-left">
      <input id="joinCode" maxlength="5" placeholder="Punya kode sesi? mis: X7K2Q" class="flex-1 min-w-0 px-4 py-2.5 rounded-2xl border-2 border-purple-100 font-bold uppercase outline-none">
      <button onclick="gabung()" class="shrink-0 px-5 py-2.5 rounded-2xl bg-black text-white font-extrabold">Join →</button>
    </div>
    <div id="msg" class="text-sm font-bold text-pink-600 mt-2 h-5"></div>
    <p class="text-xs opacity-60 font-bold mt-2">💡 Tetap wajib login. Slot kosong diisi Jarvis 🤖 + tulisan PACAR (SEGERA).</p>
  </div>
  <a href="booth.php" class="inline-block mt-4 text-sm font-bold opacity-70">mau solo aja? → booth biasa 📷</a>
</div>
<script>
async function gabung(){
  const c = document.getElementById('joinCode').value.trim().toUpperCase();
  if(!c){ document.getElementById('msg').textContent='Isi kode dulu!'; return; }
  location.href = 'room.php?code='+c;
}
</script>
<?php include 'includes/footer.php'; exit; endif; ?>

<!-- ROOM LOBBY + LIVE -->
<div class="py-6">
  <div class="flex flex-wrap items-center gap-2">
    <a href="room.php" class="text-sm font-bold opacity-70">← rooms</a>
    <h1 class="text-3xl sm:text-4xl font-extrabold">Room <span id="rc" class="bg-black text-white px-3 py-1 rounded-2xl tracking-widest"><?= htmlspecialchars($code) ?></span></h1>
    <button onclick="copyRoom()" class="px-3 py-1.5 rounded-full bg-candy-lemon text-sm font-extrabold border-2">🔗 Copy link</button>
    <span id="modeBadge" class="text-xs font-extrabold bg-pink-100 px-3 py-1.5 rounded-full">...</span>
    <span id="oriBadge" class="text-xs font-extrabold bg-purple-100 px-3 py-1.5 rounded-full">...</span>
    <span id="timerBadge" class="text-xs font-extrabold bg-gray-100 px-3 py-1.5 rounded-full">⏳ ...</span>
  </div>
  <div id="syncToast" class="hidden mt-2 p-2.5 rounded-2xl bg-purple-600 text-white text-sm font-bold text-center"></div>
  <p class="text-sm font-semibold opacity-70 mt-1">Share link / QR ke pasangan. Tunggu sampai namanya muncul, terus gas cekrek bareng! <span id="cp" class="text-green-600"></span></p>

  <div id="joinBox" class="hidden card-gemoy p-5 mt-4 max-w-md">
    <div class="font-extrabold text-lg">Masuk ke room <?= htmlspecialchars($code) ?> 🎟️</div>
    <input id="namaJoin" maxlength="20" placeholder="nama kamu..." class="mt-2 w-full px-4 py-2.5 rounded-2xl border-2 border-pink-100 font-bold outline-none">
    <button onclick="doJoin()" class="mt-2 w-full px-4 py-3 rounded-2xl bg-black text-white font-extrabold">Masuk Room 🚀</button>
    <div id="jmsg" class="text-sm font-bold text-pink-600 mt-1 h-5"></div>
  </div>

  <div id="lobby" class="hidden">
    <div class="grid lg:grid-cols-[1fr_340px] gap-4 mt-4 items-start">
      <div>
        <div id="vgrid" class="grid grid-cols-2 gap-2"></div>
        <div class="flex gap-2 mt-2 text-xs font-bold">
          <select id="camSel" class="flex-1 min-w-0 px-3 py-2 rounded-full bg-white border-2" title="Ganti kamera"></select>
          <button onclick="switchCam()" class="px-3 py-2 rounded-full bg-white border-2" title="Muat ulang kamera">🔄</button>
        </div>
        <div class="card-gemoy p-3 mt-3">
          <div class="font-extrabold text-sm">🎨 Frame bareng <span class="text-xs opacity-60">(termasuk sesi! ganti di 1 layar, semua ikut ganti ✨)</span></div>
          <div id="framePick" class="flex gap-2 mt-2 overflow-x-auto pb-1"></div>
          <div class="flex gap-2 mt-2 text-xs font-bold">
            <button data-t="strip4" class="tplB px-3 py-1.5 rounded-full border-2">Strip 4</button>
            <button data-t="polaroid" class="tplB px-3 py-1.5 rounded-full border-2">Polaroid 2</button>
            <button data-t="single" class="tplB px-3 py-1.5 rounded-full border-2">Single 1</button>
          </div>
        </div>
        <div class="card-gemoy p-3 mt-3 flex flex-wrap items-center gap-2">
          <button id="shootBtn" onclick="mulai()" class="btn-gemoy flex-1 min-w-[180px] px-5 py-3.5 rounded-2xl bg-gradient-to-r from-pink-500 to-purple-500 text-white font-extrabold">📸 CEKREK BARENG (5 dtk)</button>
          <button onclick="lanjutEdit(true)" class="px-4 py-3 rounded-2xl bg-candy-lemon font-extrabold border-2 text-sm">⏩ Lanjut dgn yg ada</button>
        </div>
        <div id="stat" class="text-sm font-bold mt-2 opacity-80">⏳ Nunggu temen join...</div>
        <div id="thumbs" class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-2"></div>
        <button id="editBtn" onclick="lanjutEdit(false)" class="hidden btn-gemoy mt-3 w-full px-5 py-3.5 rounded-2xl bg-black text-white font-extrabold">✨ Semua kekumpul! Gas ke Editor →</button>
      </div>
      <div class="card-gemoy p-4 text-center">
        <div class="font-extrabold text-sm">📱 Scan buat join</div>
        <div id="qr" class="mt-2 inline-block bg-white p-2 rounded-xl"></div>
        <div class="mt-3 text-left text-sm font-bold">
          <div class="opacity-60 text-xs">YANG ONLINE (<span id="cnt">0</span>):</div>
          <div id="mlist" class="grid gap-1 mt-1"></div>
        </div>
        <div class="mt-3 p-2.5 rounded-2xl bg-purple-50 text-[11px] font-bold text-left">🤖 Tips Jarvis: HTTPS wajib buat kamera HP. Kalau video live macet (internet lemot), santai — foto HD tetap dijamin karena yang diupload itu jepretan lokal, bukan screenshot video call.</div>
      </div>
    </div>
  </div>

  <!-- overlay countdown -->
  <div id="ov" class="hidden fixed inset-0 z-[60] bg-black/70 place-items-center"><div class="text-center"><div id="ovNum" class="count-big font-extrabold text-white">5</div><div class="text-white font-bold">Senyum! Jangan merem! 😬</div></div></div>
  <canvas id="cap" class="hidden"></canvas>
</div>

<script>
const CODE = "<?= $code ?>";
const $ = id => document.getElementById(id);
let me = null, mySlot = 0, myMode = 'duo', peer = null, localStream = null;
let myPeerId = '', called = {}, remoteStreams = {}, captured = false, lastShootAt = 0;
let roomCache = null, FRAMES = [], curFrame = '', curTpl = '', lastRev = -1, sessActive = true;
try { me = JSON.parse(localStorage.getItem('jb_room_'+CODE)||'null'); } catch(e){}
if(!me){ $('joinBox').classList.remove('hidden'); }
else { $('lobby').classList.remove('hidden'); init(); }

new QRCode($('qr'), { text: location.origin + location.pathname.replace(/\/[^\/]*$/,'/') + 'room.php?code='+CODE, width: 130, height: 130 });
function copyRoom(){ navigator.clipboard.writeText(location.href); $('cp').textContent='✅ link ke-copy!'; boom(); }

async function doJoin(){
  const nama = $('namaJoin').value.trim() || 'Tamu Gemoy';
  $('jmsg').textContent = '⏳ Masuk...';
  const fd = new FormData(); fd.append('code', CODE); fd.append('nama', nama);
  try{
    const r = await fetch('api/room_join.php',{method:'POST',body:fd}); const j = await r.json();
    if(!j.ok) throw new Error(j.msg);
    localStorage.setItem('jb_room_'+CODE, JSON.stringify({member_id:j.member_id, slot:j.slot}));
    location.reload();
  }catch(e){ $('jmsg').textContent='❌ '+e.message; }
}

async function init(){
  await startLocal();
  setupPeer();
  await loadFrames();
  setInterval(poll, 1000); poll();
  setInterval(ping, 5000); ping();
}
async function loadFrames(){
  try{
    const r=await fetch('api/frames.php'); const j=await r.json();
    if(j.ok) FRAMES=j.data;
  }catch(e){}
  renderFramePick();
}
function parseOv(v){ try{ const o=typeof v==='string'?JSON.parse(v):v; return (o&&typeof o==='object')?o:{}; }catch(e){ return {}; } }
function pickOv(f){
  const o = parseOv(f.overlays), tpl = curTpl||'polaroid';
  const ori = (roomCache?.room.orientation||'portrait')==='landscape'?'l':'p';
  if(o[tpl]&&o[tpl][ori]) return o[tpl][ori];
  if(o[tpl]&&o[tpl].p) return o[tpl].p;
  return f.overlay_path||'';
}
function renderFramePick(){
  const box=$('framePick'); if(!box) return;
  box.innerHTML = FRAMES.map(f=>{
    const th = pickOv(f);
    const img = th ? `<img src="${th}" class="w-full h-14 object-cover rounded-xl border" loading="lazy" onerror="this.style.display='none'">` : `<div class="w-full h-14 rounded-xl grid place-items-center text-2xl border" style="background:${f.bg}">${f.deco||'🎨'}</div>`;
    return `<button onclick="setFrame('${f.code}')" data-f="${f.code}" class="frB rounded-2xl border-2 bg-white p-1 text-left" style="border-color:${curFrame===f.code?'#ec4899':'#eee'}">${img}<div class="text-[11px] font-extrabold mt-0.5 px-0.5 truncate">${escapeHtml(f.nama)}</div></button>`;
  }).join('') || '<span class="text-xs opacity-60">Loading frame...</span>';
  box.className = 'grid grid-cols-3 gap-2 mt-2 max-h-64 overflow-y-auto pr-1';
  document.querySelectorAll('.tplB').forEach(b=>{ const on=b.dataset.t===curTpl; b.style.background=on?'#111':'#fff'; b.style.color=on?'#fff':'#111'; b.onclick=()=>setTemplate(b.dataset.t); });
}
async function setFrame(code){
  const fd=new FormData(); fd.append('code',CODE); fd.append('frame',code);
  try{ const r=await fetch('api/room_sync.php',{method:'POST',body:fd}); const j=await r.json(); if(j.ok&&j.state){ applyState(j.state,true); } }catch(e){}
}
async function setTemplate(t){
  const fd=new FormData(); fd.append('code',CODE); fd.append('template',t);
  try{ const r=await fetch('api/room_sync.php',{method:'POST',body:fd}); const j=await r.json(); if(j.ok&&j.state){ applyState(j.state,true); } }catch(e){}
}
function applyState(st, mine){
  const changed = st.frame_code!==curFrame || st.template!==curTpl;
  curFrame = st.frame_code||curFrame; curTpl = st.template||curTpl;
  renderFramePick(); paintRoomPreview();
  if(changed && !mine && st.state_by){
    const t=$('syncToast'); t.classList.remove('hidden');
    const fn=(FRAMES.find(f=>f.code===curFrame)?.nama)||curFrame;
    t.textContent=`✨ ${st.state_by} ganti frame → ${fn} (${curTpl})`;
    clearTimeout(window._stT); window._stT=setTimeout(()=>t.classList.add('hidden'),3500);
  }
}

async function startLocal(devId){
  try{
    if(localStream) localStream.getTracks().forEach(t=>t.stop());
    // langsung nyala perangkat default; daftar kamera diisi susulan buat ganti-ganti
    localStream = await navigator.mediaDevices.getUserMedia(devId?{video:{deviceId:{exact:devId},width:{ideal:640}},audio:false}:{video:{width:{ideal:640}},audio:false});
    // sambung ulang peer call yang sudah jalan ke stream baru
    if(peer && peer.open && window._calls) Object.values(window._calls).forEach(c=>{ try{ c.peerConnection?.getSenders().forEach(s=>{ if(s.track&&s.track.kind==='video') s.replaceTrack(localStream.getVideoTracks()[0]); }); }catch(e){} });
  }catch(e){
    $('stat').innerHTML = '😭 Kamera diblokir! Klik 🔒 di address bar → Allow. Sementara bisa tetap join, nanti pakai 📁 upload.<br><input type="file" id="up1" accept="image/*" class="mt-2 text-xs">';
    document.addEventListener('change', ev=>{
      if(ev.target.id==='up1'){ const r=new FileReader(); r.onload=e2=>uploadShot(e2.target.result); r.readAsDataURL(ev.target.files[0]); }
    });
    return;
  }
  renderVideos();
  listCams();
}
async function listCams(){
  try{
    const devs=await navigator.mediaDevices.enumerateDevices();
    const vids=devs.filter(d=>d.kind==='videoinput');
    const sel=$('camSel'); if(!sel) return;
    const cur=sel.value;
    sel.innerHTML=vids.map((d,i)=>`<option value="${d.deviceId}">${d.label||'Kamera '+(i+1)}</option>`).join('')||'<option value="">Kamera default</option>';
    if(cur) sel.value=cur;
  }catch(e){}
}
async function switchCam(){ await startLocal($('camSel').value||''); }

function setupPeer(){
  if(typeof Peer==='undefined'){ $('stat').textContent='⚠️ PeerJS gagal load (butuh internet). Mode async tetap jalan: cekrek masing-masing lalu kegabung.'; return; }
  myPeerId = CODE+'-'+me.slot+'-'+Math.random().toString(36).slice(2,6);
  try{ peer = new Peer(myPeerId, { debug: 0 }); }catch(e){ return; }
  peer.on('open', ()=> ping());
  peer.on('call', call=>{
    if(localStream) call.answer(localStream);
    call.on('stream', s=>{ remoteStreams[call.peer]=s; renderVideos(); });
  });
  peer.on('error', ()=>{});
}

function slotOfPeer(pid){ const p=(pid||'').split('-'); return parseInt(p[1]||'0'); }

async function poll(){
  try{
    const r = await fetch('api/room_state.php?code='+CODE); const j = await r.json();
    if(!j.ok) return;
    roomCache = j;
    myMode = j.room.mode;
    $('modeBadge').textContent = j.room.mode==='squad' ? '🤪 SQUAD 4' : j.room.mode==='duo' ? '💘 DUO LDR' : '🧍 SOLO';
    $('oriBadge').textContent = (j.room.orientation||'portrait')==='landscape' ? '🖥️ Landscape (dikunci)' : '📱 Portrait (dikunci)';
    // timer sesi
    const sess = j.session;
    if(sess){
      if(sess.status==='active'){
        const sisa=Math.max(0,parseInt(sess.sisa||0)), m=Math.floor(sisa/60), d=sisa%60;
        $('timerBadge').textContent=`⏰ ${m}:${String(d).padStart(2,'0')}`;
        $('timerBadge').className='text-xs font-extrabold px-3 py-1.5 rounded-full '+(sisa<60?'bg-red-500 text-white':'bg-green-100 text-green-700');
        sessActive=true; $('shootBtn').disabled=false; $('shootBtn').style.opacity='1';
      } else {
        sessActive=false;
        $('timerBadge').textContent = sess.status==='expired' ? '⏰ Waktu habis!' : '🔒 Belum dibayar host';
        $('timerBadge').className='text-xs font-extrabold px-3 py-1.5 rounded-full bg-red-100 text-red-700';
        $('shootBtn').disabled=true; $('shootBtn').style.opacity='.4';
        if(sess.status!=='active' && sess.status!=='expired') $('stat').innerHTML='🔒 Sesi belum aktif — host selesaikan pembayaran di <a class="underline" href="sesi.php?code='+CODE+'">sesi.php</a> ya!';
      }
    }
    // sync frame/template realtime
    const rev = parseInt(j.room.state_rev||0);
    if(lastRev===-1){ curFrame=j.room.frame_code||''; curTpl=j.room.template||'polaroid'; lastRev=rev; renderFramePick(); }
    else if(rev!==lastRev){ lastRev=rev; applyState({frame_code:j.room.frame_code,template:j.room.template,state_by:j.room.state_by}, false); }
    $('cnt').textContent = j.members.length;
    $('mlist').innerHTML = j.members.map(m=>`<div class="px-2.5 py-1.5 rounded-xl ${m.slot===me.slot?'bg-pink-500 text-white':'bg-gray-100'}">Slot ${m.slot+1}: ${escapeHtml(m.nama)} ${m.is_host?'👑':''}</div>`).join('');
    // panggil remote yg slot-nya lebih besar (biar gak dobel call)
    if(peer && peer.open) j.members.forEach(m=>{
      if(m.slot!==me.slot && m.peer_id && m.peer_id!==myPeerId && !called[m.peer_id] && me.slot < m.slot){
        called[m.peer_id]=1;
        try{ const c=peer.call(m.peer_id, localStream); window._calls=window._calls||{}; window._calls[m.peer_id]=c; c.on('stream', s=>{ remoteStreams[m.peer_id]=s; renderVideos(); }); }catch(e){}
      }
    });
    renderVideos();
    // status foto
    if(j.photos.length){
      $('thumbs').innerHTML = j.photos.map(p=>`<div class="rounded-xl overflow-hidden border-2 border-pink-200"><img src="${p.image_path}" class="w-full aspect-[4/3] object-cover"><div class="text-[11px] font-bold text-center bg-white">Slot ${p.slot+1}</div></div>`).join('');
    }
    const need = j.room.mode==='squad'?4:(j.room.mode==='duo'?2:1);
    if(j.photos.length>=need){ $('editBtn').classList.remove('hidden'); $('stat').innerHTML='✅ Semua foto kekumpul! Gas edit bareng ✨'; }
    else if(j.photos.length>0){ $('stat').innerHTML=`📥 Kekumpul ${j.photos.length}/${need}... nunggu yang lain upload.`; }
    // countdown sync
    const now = j.now, shootAt = parseInt(j.room.shoot_at||0);
    if(shootAt && shootAt>now && shootAt!==lastShootAt){ /* countdown baru */ captured=false; }
    if(shootAt && shootAt>now){
      lastShootAt=shootAt;
      const sisa=Math.ceil((shootAt-now)/1000);
      showCount(sisa);
    } else if(shootAt && now>=shootAt && !captured){
      captured=true; hideCount(); jepret();
    } else if(!shootAt){ hideCount(); }
  }catch(e){}
}
async function ping(){
  if(!me) return;
  const fd=new FormData(); fd.append('code',CODE); fd.append('member_id',me.member_id); fd.append('peer_id',myPeerId||'');
  try{ await fetch('api/room_ping.php',{method:'POST',body:fd}); }catch(e){}
}
function chromeHTML(){
  return `<div class="pvT absolute top-0 left-0 right-0 text-center font-extrabold pointer-events-none" style="font-size:11px;padding:4px 0"></div>`
    +`<img class="pvO absolute inset-0 w-full h-full pointer-events-none" style="object-fit:fill;display:none" alt="" onerror="this.style.display='none'">`
    +`<div class="pvB absolute bottom-0 left-0 right-0 text-center font-bold pointer-events-none" style="font-size:10px;padding:3px 0"></div>`;
}
function paintRoomPreview(){
  const f = FRAMES.find(x=>x.code===curFrame);
  document.querySelectorAll('#vgrid > div').forEach(box=>{
    const t=box.querySelector('.pvT'), o=box.querySelector('.pvO'), b=box.querySelector('.pvB');
    if(!f){ if(t)t.textContent=''; if(b)b.textContent=''; if(o)o.style.display='none'; return; }
    if(t){ t.textContent=`${f.deco||'⭐'} JARVISBOOTH ${f.deco||'⭐'}`; t.style.background=f.bg||'transparent'; t.style.color=f.fg||'#fff'; }
    if(b){ b.textContent='@jarvisbooth'; b.style.background=(f.bg||'#000')+'cc'; b.style.color=f.fg||'#fff'; }
    if(o){ const src=pickOv(f); if(src){ o.style.display='block'; if(o.dataset.src!==src){ o.dataset.src=src; o.src=src; } } else { o.style.display='none'; o.dataset.src=''; o.removeAttribute('src'); } }
  });
}
function renderVideos(){
  const need = roomCache?.room.mode==='squad'?4:(roomCache?.room.mode==='duo'?2:1);
  const n = need||2;
  const g=$('vgrid'); if(!g) return;
  // bangun ulang hanya kalau jumlah tile berubah (biar video tidak flicker tiap poll)
  if(g.dataset.n!=String(n)){
    g.dataset.n=String(n);
    let html='';
    for(let s=0;s<n;s++){
      const isMe = s===me.slot;
      const member = roomCache?.members.find(m=>m.slot===s);
      const label = member? escapeHtml(member.nama) : (isMe?'Kamu':(s===1&&n===2?'PACAR (SEGERA) 🤖':'Slot '+(s+1)+' (kosong)'));
      if(isMe){
        html+=`<div class="relative rounded-2xl overflow-hidden bg-black aspect-[4/3] border-2 border-pink-300"><video id="lv" autoplay playsinline muted class="absolute inset-0 w-full h-full object-cover mirror"></video>${chromeHTML()}<span class="absolute top-2 left-2 bg-pink-500 text-white text-[11px] font-bold px-2 py-0.5 rounded-full" style="z-index:20">${label} 📷</span></div>`;
      } else {
        html+=`<div class="relative rounded-2xl overflow-hidden bg-gradient-to-br from-purple-100 to-pink-100 aspect-[4/3] border-2 border-white grid place-items-center" id="rv${s}"><div class="rvBody contents"><span class="text-5xl">${member?'📹':'🤖'}</span></div>${chromeHTML()}<span class="absolute top-2 left-2 bg-black/70 text-white text-[11px] font-bold px-2 py-0.5 rounded-full" style="z-index:20">${label}</span>${!member?'<span class="absolute bottom-2 text-[11px] font-bold opacity-60">share link biar keisi!</span>':''}</div>`;
      }
    }
    g.innerHTML=html;
  } else {
    // update label saja tanpa merobohkan video
    for(let s=0;s<n;s++){
      const member = roomCache?.members.find(m=>m.slot===s);
      const label = member? escapeHtml(member.nama) : (s===me.slot?'Kamu':(s===1&&n===2?'PACAR (SEGERA) 🤖':'Slot '+(s+1)+' (kosong)'));
    }
  }
  const lv=$('lv');
  if(lv && localStream && !lv.srcObject) lv.srcObject=localStream;
  // pasang remote streams tanpa menghapus chrome
  Object.entries(remoteStreams).forEach(([pid, s])=>{
    const slot = slotOfPeer(pid);
    const box=$('rv'+slot);
    if(box && !box.querySelector('video')){ const body=box.querySelector('.rvBody'); if(body){ body.innerHTML=''; const v=document.createElement('video'); v.autoplay=true; v.playsInline=true; v.muted=true; v.className='absolute inset-0 w-full h-full object-cover'; v.srcObject=s; body.appendChild(v); } }
  });
  paintRoomPreview();
}
function escapeHtml(s){return String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}

async function mulai(){
  if(!sessActive){ alert('Sesi belum aktif / sudah habis ⏰'); return; }
  const fd=new FormData(); fd.append('code',CODE); fd.append('delay',5);
  await fetch('api/room_shoot.php',{method:'POST',body:fd});
  boom();
}
let countTimer=null;
function showCount(n){
  $('ov').style.display='grid';
  $('ovNum').textContent=n>0?n:'📸';
  try{ const ctx=new (window.AudioContext||window.webkitAudioContext)(); const o=ctx.createOscillator(),g=ctx.createGain(); o.connect(g);g.connect(ctx.destination);o.frequency.value=600+n*120;g.gain.value=0.15;o.start();o.stop(ctx.currentTime+0.12);}catch(e){}
}
function hideCount(){ $('ov').style.display='none'; }

async function jepret(){
  showCount(0);
  try{ const ctx=new (window.AudioContext||window.webkitAudioContext)(); const o=ctx.createOscillator(),g=ctx.createGain(); o.connect(g);g.connect(ctx.destination);o.frequency.value=1400;g.gain.value=0.2;o.start();o.stop(ctx.currentTime+0.25);}catch(e){}
  await new Promise(r=>setTimeout(r,400));
  hideCount(); boom();
  const lv=$('lv');
  let data=null;
  if(lv && lv.videoWidth){
    const c=$('cap'); c.width=lv.videoWidth; c.height=lv.videoHeight;
    const x=c.getContext('2d'); x.save(); x.translate(c.width,0); x.scale(-1,1); x.drawImage(lv,0,0,c.width,c.height); x.restore();
    data=c.toDataURL('image/jpeg',0.9);
  }
  if(data) uploadShot(data);
  else $('stat').textContent='😭 Gagal jepret (kamera mati). Pakai upload manual ya!';
}
async function uploadShot(data){
  $('stat').textContent='📤 Upload fotomu...';
  const fd=new FormData(); fd.append('code',CODE); fd.append('slot',me.slot); fd.append('image',data);
  try{ const r=await fetch('api/room_upload.php',{method:'POST',body:fd}); const j=await r.json(); if(j.ok){ $('stat').textContent='✅ Fotomu kekirim! Nunggu yang lain...'; poll(); } else $('stat').textContent='❌ '+j.msg; }catch(e){ $('stat').textContent='❌ Upload gagal, coba lagi!'; }
}
async function lanjutEdit(paksa){
  // ambil state terbaru, kumpulin foto yg ada, slot kosong diisi Jarvis placeholder
  const r=await fetch('api/room_state.php?code='+CODE); const j=await r.json();
  if(!j.ok) return alert('Room error');
  const need=j.room.mode==='squad'?4:(j.room.mode==='duo'?2:1);
  const have={}; j.photos.forEach(p=>have[p.slot]=p.image_path);
  const shots=[];
  for(let s=0;s<need;s++){
    if(have[s]) shots.push(have[s]);
    else if(paksa || need===1){
      // placeholder Jarvis buat jomblo / yg belum upload
      shots.push(jarvisPlaceholder(s));
    }
  }
  if(!shots.length) return alert('Belum ada foto kekumpul!');
  try{ localStorage.setItem('jb_shots', JSON.stringify(await toDataURLs(shots))); }catch(e){ localStorage.setItem('jb_shots', JSON.stringify(shots)); }
  localStorage.setItem('jb_mode', j.room.template||curTpl||'polaroid');
  if(curFrame) localStorage.setItem('jb_want_frame', curFrame);
  localStorage.setItem('jb_ori', j.room.orientation||'portrait');
  localStorage.setItem('jb_session', CODE);
  location.href='edit.php';
}
function jarvisPlaceholder(slot){
  const c=document.createElement('canvas'); c.width=640; c.height=480;
  const x=c.getContext('2d');
  const g=x.createLinearGradient(0,0,640,480); g.addColorStop(0,'#FFD6E6'); g.addColorStop(1,'#C7B9FF');
  x.fillStyle=g; x.fillRect(0,0,640,480);
  x.font='120px serif'; x.textAlign='center'; x.fillText('🤖',320,230);
  x.fillStyle='#3A2E3A'; x.font='bold 34px sans-serif'; x.fillText('PACAR (SEGERA)',320,320);
  x.font='24px sans-serif'; x.fillText('slot '+(slot+1)+' • Jarvis nemenin dulu',320,360);
  return c.toDataURL('image/jpeg',0.9);
}
async function toDataURLs(list){
  // pastikan semua jadi dataURL biar edit.php (Fabric) gak kena CORS
  const out=[];
  for(const u of list){
    if(u.startsWith('data:')){ out.push(u); continue; }
    try{ const r=await fetch(u); const b=await r.blob(); out.push(await new Promise(res=>{ const fr=new FileReader(); fr.onload=e=>res(e.target.result); fr.readAsDataURL(b); })); }
    catch(e){ out.push(u); }
  }
  return out;
}
</script>
<?php include 'includes/footer.php'; ?>
