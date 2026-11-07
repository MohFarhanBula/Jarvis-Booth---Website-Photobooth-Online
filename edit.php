<?php require_once 'includes/auth_user.php'; user_require(); $title='Edit Gemoy ✨ JarvisBooth'; include 'includes/header.php'; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
<div class="py-6">
  <a href="booth.php" class="text-sm font-bold opacity-70">← foto ulang</a>
  <h1 class="text-3xl sm:text-4xl font-extrabold mt-1">Edit Sekocak Mungkin 🎨 <span id="oriBadge" class="text-sm align-middle bg-purple-100 px-3 py-1 rounded-full"></span></h1>
  <p class="font-semibold opacity-70 text-sm sm:text-base">Drag stiker seenaknya. Semua frame termasuk sesi ✨ Orientasi dikunci dari sesi.</p>

  <div class="grid lg:grid-cols-[minmax(0,420px)_minmax(0,1fr)] gap-4 mt-4 items-start">
    <!-- CANVAS -->
    <div class="card-gemoy p-3 sm:p-4 text-center min-w-0 w-full max-w-full overflow-hidden">
      <div id="cvWrap" class="w-full max-w-full rounded-2xl overflow-hidden border-4 border-pink-100 shadow bg-white">
        <canvas id="cv"></canvas>
      </div>
      <div class="mt-3 flex flex-wrap gap-2 justify-center text-xs sm:text-sm font-bold">
        <button onclick="undo()" class="px-3 py-2 rounded-full bg-gray-100">↩️ Undo</button>
        <button onclick="clearStickers()" class="px-3 py-2 rounded-full bg-gray-100">🧹 Bersihin stiker</button>
        <button onclick="shuffleTheme()" class="px-3 py-2 rounded-full bg-candy-lemon">🎲 Acak tema</button>
      </div>
      <button id="saveBtn" onclick="saveResult()" class="btn-gemoy mt-3 w-full px-6 py-4 rounded-2xl bg-black text-white text-xl font-extrabold">💾 SIMPAN & PAMER! 🚀</button>
      <label class="mt-2 flex items-start gap-2 text-left text-xs sm:text-sm font-bold bg-pink-50 border-2 border-pink-100 rounded-2xl px-3 py-2 cursor-pointer">
        <input type="checkbox" id="pubCheck" checked class="w-4 h-4 mt-0.5 accent-pink-500">
        <span>🌍 Pajang di Hall of Fame <span class="opacity-60 font-semibold">(biar viral! matikan kalau malu-malu 🔒 — link hasil tetap bisa dibuka)</span></span>
      </label>
      <div id="saveMsg" class="text-sm font-bold mt-2 h-5 text-pink-600"></div>
    </div>

    <!-- CONTROLS -->
    <div class="grid gap-3 content-start min-w-0 w-full max-w-full">
      <div class="card-gemoy p-4 min-w-0">
        <div class="font-extrabold">🖼️ Template</div>
        <div class="flex flex-wrap gap-2 mt-2 text-xs sm:text-sm font-bold">
          <button data-t="strip4" class="tpl px-4 py-2 rounded-full border-2">🇰🇷 Strip 4</button>
          <button data-t="polaroid" class="tpl px-4 py-2 rounded-full border-2">💘 Polaroid 2</button>
          <button data-t="single" class="tpl px-4 py-2 rounded-full border-2">🤡 Single 1</button>
        </div>
        <div class="font-extrabold mt-4">🎨 Frame Kartun <a href="harga.php" class="text-xs text-pink-600 font-bold">lihat harga →</a></div>
        <div id="themes" class="flex flex-wrap gap-2 mt-2 text-sm font-bold"></div>
        <div id="lockMsg" class="hidden mt-2 p-2.5 rounded-2xl bg-orange-50 border-2 border-orange-200 text-xs font-bold"></div>
        <div class="font-extrabold mt-4">✨ Filter Foto <span class="text-xs opacity-60">(klik, auto ke semua foto)</span></div>
        <div id="filters" class="flex flex-wrap gap-2 mt-2 text-sm font-bold"></div>
        <label class="block font-extrabold mt-4">✏️ Caption bawah <span class="text-xs opacity-60">(mis: “Aib Wisuda 2026”)</span></label>
        <input id="caption" maxlength="60" placeholder="tulis caption kocak..." class="mt-1 w-full px-4 py-2.5 rounded-2xl border-2 border-pink-100 outline-none focus:border-pink-400 font-bold" oninput="rebuild(false)">
      </div>

      <div class="card-gemoy p-4 min-w-0 overflow-hidden">
        <div class="font-extrabold text-sm sm:text-base">😜 Stiker <span class="text-xs opacity-60 font-bold">(klik nempel, drag geser)</span></div>
        <div id="stickers" class="grid grid-cols-6 sm:grid-cols-8 gap-2 mt-2 max-h-48 overflow-y-auto pr-1"></div>
        <div class="font-extrabold mt-3 text-sm sm:text-base">🔥 Teks Meme</div>
        <div id="memes" class="flex flex-wrap gap-2 mt-2"></div>
        <div class="flex gap-2 mt-3 min-w-0">
          <input id="customTxt" placeholder="teks sendiri... mis: GEMOY PARAH" class="min-w-0 flex-1 px-4 py-2 rounded-full border-2 border-purple-100 font-bold outline-none text-sm">
          <button onclick="addText(document.getElementById('customTxt').value||'GEMOY 🤪')" class="shrink-0 px-4 py-2 rounded-full bg-purple-600 text-white font-bold text-sm whitespace-nowrap">Tempel!</button>
        </div>
        <p class="text-xs opacity-60 mt-2 font-bold">Tips: klik stiker di kanvas → bisa gedein, puter, hapus (pencet Delete). Double-klik teks buat edit.</p>
      </div>
    </div>
  </div>
</div>

<script>
let shots = [], mode = 'strip4', ORI = 'portrait';
try { shots = JSON.parse(localStorage.getItem('jb_shots')||'[]'); mode = localStorage.getItem('jb_mode')||'strip4'; ORI = localStorage.getItem('jb_ori')||'portrait'; } catch(e){}
if(!shots.length){ document.body.insertAdjacentHTML('afterbegin',''); location.href='booth.php'; }
if(ORI!=='landscape') ORI='portrait';
try{ document.getElementById('oriBadge').textContent = (ORI==='landscape'?'🖥️ Landscape':'📱 Portrait'); }catch(e){}

const THEMES = [
  {id:'bubblegum', code:'basic-bubblegum', n:'🍬 Bubblegum', bg:'#FFD6E6', fg:'#B3125F', deco:'🍬', harga:0},
  {id:'korea', code:'basic-korea', n:'🇰🇷 Korea Clean', bg:'#FFFFFF', fg:'#111111', deco:'✨', harga:0},
];
let FRAMES = [...THEMES];
let paidFrames = [];
try { paidFrames = JSON.parse(localStorage.getItem('jb_paid')||'[]'); } catch(e){}
const FILTERS = [
  {id:'normal', n:'😇 Normal'}, {id:'bw', n:'⬛ BW Dramatis'}, {id:'sepia', n:'📼 Vintage'},
  {id:'gemoy', n:'🌸 Gemoy (+pink)'}, {id:'cold', n:'🧊 Cold Idol'}, {id:'ngakak', n:'🤪 Kontras Absurd'},
];
let curTheme = FRAMES[1], curFilter='normal', curTpl = ['strip4','polaroid','single'].includes(mode)?mode:'strip4';
if(curTpl==='strip4') curTheme = FRAMES[0];
function isUnlocked(f){ return true; /* semua frame GRATIS — yang dibayar cuma sesi */ }

const STICKERS = ['🕶️','👓','🐱','🐶','👑','🎀','⭐','✨','💘','🔥','🌈','🍑','🥑','💀','🤡','👻','🐸','🦊','🐼','🍓','⚡','💩','🍼','🧢'];
const MEMES = ['GEMOY 🤪','AIB 🤡','BUCIN 💘','RIYA? 😌','SULTAN 💸','CANDU 🫠','MASIH CUPU 🐣','ANTI JAIM 😎','KAPAN NIKAH? 💍','TOLONG 😭'];
$('stickers').innerHTML = STICKERS.map((s,i)=>`<button data-si="${i}" class="sticker-btn sticker-item bg-white border-2 border-pink-100 rounded-xl sm:rounded-2xl w-10 h-10 sm:w-12 sm:h-12 grid place-items-center text-2xl sm:text-3xl">${s}</button>`).join('');
document.querySelectorAll('.sticker-item').forEach(b=>b.onclick=()=>addSticker(STICKERS[+b.dataset.si]));
$('memes').innerHTML = MEMES.map((m,i)=>`<button data-mi="${i}" class="meme-item px-3 py-1.5 rounded-full bg-black text-white text-[11px] sm:text-xs font-extrabold hover:bg-pink-600 whitespace-nowrap">${m}</button>`).join('');
document.querySelectorAll('.meme-item').forEach(b=>b.onclick=()=>addText(MEMES[+b.dataset.mi]));
$('filters').innerHTML = FILTERS.map(f=>`<button data-f="${f.id}" class="fl px-3 py-1.5 rounded-full border-2 bg-white text-[11px] sm:text-sm">${f.n}</button>`).join('');
function parseOverlays(v){ try{ const o = typeof v==='string'?JSON.parse(v):v; return (o&&typeof o==='object')?o:{}; }catch(e){ return {}; } }
// pilih overlay sesuai template + orientasi (fallback overlay lama)
function resolveOverlay(f, tpl, ori){
  const o = f.ovls||{};
  const k = (tpl||curTpl||'strip4'), ol = (ori||ORI)==='landscape'?'l':'p';
  if(o[k] && o[k][ol]) return o[k][ol];
  if(o[k] && o[k].p) return o[k].p;
  return f.overlay||'';
}
function renderFrames(){
  $('themes').innerHTML = FRAMES.map(t=>{
    const thumb = resolveOverlay(t, curTpl, ORI) || t.overlay || '';
    const img = thumb ? `<img src="${thumb}" class="w-full h-20 object-cover rounded-xl border" loading="lazy" onerror="this.style.display='none'">` : `<div class="w-full h-20 rounded-xl grid place-items-center text-4xl border" style="background:${t.bg}">${t.deco||'🎨'}</div>`;
    return `<button data-th="${t.id||t.code}" class="th text-left rounded-2xl border-2 bg-white p-1.5 hover:scale-[1.02] transition"><div class="relative">${img}<span class="absolute bottom-1 right-1 text-[10px] bg-green-100 px-1.5 py-0.5 rounded-full font-extrabold">✅</span></div><div class="text-[11px] font-extrabold mt-1 px-0.5 truncate">${t.n||t.code}</div></button>`;
  }).join('');
  // grid 3 kolom
  $('themes').className = 'grid grid-cols-3 gap-2 mt-2';
  document.querySelectorAll('.th').forEach(b=>b.onclick=()=>{
    const f = FRAMES.find(t=>(t.id||t.code)===b.dataset.th);
    if(!f) return;
    curTheme=f; rebuild(true); mark();
  });
  mark();
}
async function loadFrames(){
  try{
    const r = await fetch('api/frames.php'); const j = await r.json();
    if(j.ok && j.data.length){
      FRAMES = j.data.map(f=>({id:f.code, code:f.code, n:(f.deco||'🎨')+' '+f.nama, bg:f.bg, fg:f.fg, deco:f.deco||'⭐', harga:intval(f.harga), overlay:f.overlay_path||'', ovls:parseOverlays(f.overlays), layout:f.layout}));
      let want=null; try{ want=localStorage.getItem('jb_want_frame'); localStorage.removeItem('jb_want_frame'); }catch(e){}
      const byUrl=new URLSearchParams(location.search).get('frame');
      const target=(byUrl||want||'');
      const cur = FRAMES.find(f=>f.code===target) || FRAMES.find(f=>(f.code)===curTheme.code) || FRAMES[0];
      curTheme = cur;
      renderFrames(); rebuild(false);
      return;
    }
  }catch(e){}
  renderFrames();
}
function intval(v){ const n=parseInt(v); return isNaN(n)?0:n; }
document.querySelectorAll('.tpl').forEach(b=>b.onclick=()=>{curTpl=b.dataset.t; paintTpl(); renderFrames(); rebuild(true);});
document.querySelectorAll('.fl').forEach(b=>b.onclick=()=>{curFilter=b.dataset.f; rebuild(true); mark();});
function mark(){
  document.querySelectorAll('.tpl').forEach(b=>{ b.style.background = b.dataset.t===curTpl?'#111':'#fff'; b.style.color=b.dataset.t===curTpl?'#fff':'#111'; });
  document.querySelectorAll('.th').forEach(b=>{ b.style.outline = b.dataset.th===(curTheme.id||curTheme.code)?'3px solid #ec4899':''; });
  document.querySelectorAll('.fl').forEach(b=>{ b.style.outline = b.dataset.f===curFilter?'3px solid #8b5cf6':''; });
}
function shuffleTheme(){ const free=FRAMES.filter(isUnlocked); curTheme=(free.length?free:FRAMES)[Math.floor(Math.random()*(free.length||FRAMES.length))]; rebuild(true); mark(); boom(); }

const canvas = new fabric.Canvas('cv', { preserveObjectStacking:true });
let baseW = 600, baseH = 1750, viewScale = 1;
function dims(){
  const P = curTpl==='strip4'?{W:600,H:1750,pad:24,gap:14,head:110,foot:130} : curTpl==='polaroid'?{W:700,H:1050,pad:30,gap:18,head:100,foot:170} : {W:700,H:860,pad:30,gap:0,head:90,foot:150};
  if(ORI!=='landscape') return P;
  // landscape: kanvas melebar, foto dijejer ke samping
  return curTpl==='strip4'?{W:1750,H:600,pad:24,gap:14,head:80,foot:100} : curTpl==='polaroid'?{W:1050,H:700,pad:30,gap:18,head:80,foot:110} : {W:860,H:700,pad:30,gap:0,head:80,foot:110};
}
function fitCanvas(){
  const wrap = document.getElementById('cvWrap');
  if(!wrap || !baseW) return;
  const avail = wrap.clientWidth || Math.min(window.innerWidth - 48, 420);
  viewScale = Math.min(1, avail / baseW);
  if(viewScale <= 0) viewScale = 1;
  canvas.setDimensions({ width: Math.round(baseW * viewScale), height: Math.round(baseH * viewScale) });
  canvas.setZoom(viewScale);
  canvas.renderAll();
}
window.addEventListener('resize', ()=>fitCanvas());
function paintTpl(){ mark(); }
paintTpl();

function coverCrop(img, tw, th, bx, by){
  const iw=img.width, ih=img.height, scale=Math.max(tw/iw, th/ih);
  const sw=iw*scale, sh=ih*scale;
  // center-crop ala object-fit:cover — kelebihan dibagi rata kiri-kanan / atas-bawah
  img.set({ originX:'left', originY:'top', scaleX:scale, scaleY:scale, left: bx-(sw-tw)/2, top: by-(sh-th)/2 });
  return img;
}
function applyFilter(img){
  img.filters = [];
  if(curFilter==='bw') img.filters.push(new fabric.Image.filters.Grayscale());
  if(curFilter==='sepia') img.filters.push(new fabric.Image.filters.Sepia());
  if(curFilter==='gemoy') img.filters.push(new fabric.Image.filters.BlendColor({color:'#ff9ec7', mode:'tint', alpha:0.28}));
  if(curFilter==='cold') img.filters.push(new fabric.Image.filters.BlendColor({color:'#7dd3fc', mode:'tint', alpha:0.3}));
  if(curFilter==='ngakak') { img.filters.push(new fabric.Image.filters.Contrast({contrast:0.35})); img.filters.push(new fabric.Image.filters.Saturation({saturation:0.6})); }
  img.applyFilters();
}

let photoObjs=[], decoObjs=[];
function rebuild(keepDeco){
  const {W,H,pad,gap,head,foot} = dims();
  baseW = W; baseH = H;
  canvas.setZoom(1);
  canvas.setDimensions({ width: W, height: H });
  // simpan deco
  const saved = keepDeco ? canvas.getObjects().filter(o=>o.isDeco) : [];
  canvas.clear();
  // bg
  canvas.backgroundColor = curTheme.bg;
  // foto yang dipakai
  const need = curTpl==='strip4'?4:curTpl==='polaroid'?2:1;
  const imgs = []; for(let i=0;i<need;i++) imgs.push(shots[i%shots.length]);
  const areaH = H-head-foot, areaW = W-pad*2;
  const wide = (ORI==='landscape' && need>1);
  let pw, ph;
  if(wide){ pw = (areaW-gap*(need-1))/need; ph = areaH; }
  else { const rows = need; ph = (areaH-gap*(rows-1))/rows; pw = areaW; }
  let loaded=0;
  imgs.forEach((src,idx)=>{
    fabric.Image.fromURL(src, (img)=>{
      const left = wide ? pad + idx*(pw+gap) : pad;
      const top = wide ? head : head + idx*(ph+gap);
      coverCrop(img, pw, ph, left, top);
      img.set({ selectable:false, evented:false });
      // clip rounded
      const clip = new fabric.Rect({ left:left, top:top, width:pw, height:ph, rx:18, ry:18, absolutePositioned:true });
      img.clipPath = clip;
      applyFilter(img);
      canvas.add(img); img.sendToBack();
      // bingkai putih tipis
      const fr = new fabric.Rect({ left:left-4, top:top-4, width:pw+8, height:ph+8, rx:22, ry:22, fill:'transparent', stroke: (curTheme.code||'')==='basic-korea'?'#11111122':'#ffffff', strokeWidth: curTpl==='strip4'?5:8, selectable:false, evented:false });
      canvas.add(fr);
      if(++loaded===imgs.length){ drawHeadFoot(); saved.forEach(o=>canvas.add(o)); drawOverlay(); canvas.renderAll(); requestAnimationFrame(fitCanvas); }
    }, { crossOrigin:'anonymous' });
  });
  if(!imgs.length){ drawHeadFoot(); }
  function drawHeadFoot(){
    const fg = curTheme.fg, deco = curTheme.deco||'⭐';
    const headT = new fabric.Text(deco+' JARVISBOOTH '+deco, { left:W/2, top:head/2-14, originX:'center', originY:'center', fontFamily:'Arial Black', fontSize:curTpl==='single'?32:28, fill:fg });
    const date = new Date().toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'});
    const cap = ($('caption').value||'muka boleh pas-pasan, gaya harus sultan 😎').slice(0,60);
    const footT = new fabric.Text(cap, { left:W/2, top:H-foot+34, originX:'center', originY:'center', fontFamily:'Arial', fontWeight:'bold', fontSize:22, fill:fg });
    const wm = '📸 '+date+' • @jarvisbooth';
    const foot2 = new fabric.Text(wm, { left:W/2, top:H-foot+68, originX:'center', originY:'center', fontFamily:'Arial', fontSize:18, fill:fg, opacity:0.75 });
    [headT,footT,foot2].forEach(o=>{o.selectable=false;o.evented=false;canvas.add(o);});
    canvas.renderAll();
    requestAnimationFrame(fitCanvas);
  }
  function drawOverlay(){
    const ov = resolveOverlay(curTheme, curTpl, ORI) || curTheme.overlay;
    if(!ov) return;
    fabric.Image.fromURL(ov, (img)=>{
      img.set({ left:0, top:0, scaleX: W/img.width, scaleY: H/img.height, selectable:false, evented:false });
      canvas.add(img); canvas.bringToFront(img); canvas.renderAll(); requestAnimationFrame(fitCanvas);
    }, { crossOrigin:'anonymous' });
  }
  mark();
}

function addSticker(ch){
  const {W,H} = dims();
  const t = new fabric.Text(ch, { left:W/2+(Math.random()*120-60), top:H/2+(Math.random()*200-100), fontSize:90, originX:'center', originY:'center' });
  t.isDeco=true; canvas.add(t); canvas.setActiveObject(t); canvas.renderAll(); boomMini();
}
function addText(str){
  const {W,H} = dims();
  const t = new fabric.IText(String(str).slice(0,30), { left:W/2, top:H/2, originX:'center', fontFamily:'Arial Black', fontSize:38, fill:'#111', stroke:'#fff', strokeWidth:8, paintFirst:'stroke', textAlign:'center' });
  t.isDeco=true; canvas.add(t); canvas.setActiveObject(t); canvas.renderAll();
}
function undo(){ const objs=canvas.getObjects().filter(o=>o.isDeco); if(objs.length){ canvas.remove(objs[objs.length-1]); canvas.renderAll(); } }
function clearStickers(){ canvas.getObjects().filter(o=>o.isDeco).forEach(o=>canvas.remove(o)); canvas.renderAll(); }
document.addEventListener('keydown', e=>{ if((e.key==='Delete'||e.key==='Backspace') && document.activeElement.tagName!=='INPUT'){ const a=canvas.getActiveObject(); if(a&&a.isDeco){canvas.remove(a);canvas.renderAll();} } });
function boomMini(){ if(window.confetti) confetti({particleCount:35,spread:55,origin:{y:.6},scalar:.8}); }

async function saveResult(){
  const btn=$('saveBtn'), msg=$('saveMsg');
  btn.disabled=true; btn.textContent='⏳ Lagi di-develop Jarvis...';
  canvas.discardActiveObject(); canvas.renderAll();
  await new Promise(r=>setTimeout(r,300));
  // export full-res: kembalikan zoom ke 1 dulu
  const curZoom = canvas.getZoom();
  canvas.setZoom(1);
  canvas.setDimensions({ width: baseW, height: baseH });
  canvas.renderAll();
  const data = canvas.toDataURL({format:'jpeg', quality:0.92, multiplier:1});
  // kembalikan tampilan responsive
  fitCanvas();
  msg.textContent='📤 Upload ke galeri...';
  try{
    const fd=new FormData(); fd.append('image',data); fd.append('template',curTpl); fd.append('filter',curFilter); fd.append('caption',$('caption').value||''); fd.append('frame_code',curTheme.code||curTheme.id||''); fd.append('is_public',$('pubCheck').checked?'1':'0'); try{ fd.append('session_code',localStorage.getItem('jb_session')||''); }catch(e){}
    const res=await fetch('api/save.php',{method:'POST',body:fd}); const j=await res.json();
    if(j.ok){ boom(); msg.textContent = (j.is_public? '✅ Kesimpen & dipajang!':'✅ Kesimpen privat!') + ' Lagi teleport...'; try{ const mine=JSON.parse(localStorage.getItem('jb_mine')||'[]'); mine.push(j.code); localStorage.setItem('jb_mine',JSON.stringify(mine)); }catch(e){} localStorage.setItem('jb_last', j.code); setTimeout(()=>location.href='result.php?code='+j.code, 700); }
    else { msg.textContent='❌ '+j.msg; btn.disabled=false; btn.textContent='💾 SIMPAN & PAMER! 🚀'; }
  }catch(e){ msg.textContent='❌ Gagal simpan. Coba download manual ya! + tombol kanan'; btn.disabled=false; btn.textContent='💾 SIMPAN & PAMER! 🚀'; const a=document.createElement('a'); a.href=data; a.download='jarvisbooth.jpg'; a.click(); }
}
function $(id){return document.getElementById(id);}
let buyFrame=null, buyRef=null, buyTimer=null;
function showBuy(f){
  buyFrame=f;
  const m=$('lockMsg'); m.classList.remove('hidden');
  m.innerHTML=`🔒 <b>${f.n}</b> itu premium <b>Rp${Number(f.harga).toLocaleString('id-ID')}</b>! Isi nama + pilih bayar, langsung kebuka HD tanpa watermark alay.<br><button onclick="document.getElementById('buyBox').classList.remove('hidden')" class="mt-1 px-3 py-1.5 rounded-full bg-black text-white text-xs font-extrabold">💰 Beli via Tripay</button> <a href="harga.php" class="text-xs font-bold text-pink-600">lihat harga →</a>`;
  $('buyFrameName').textContent=f.n+' • Rp'+Number(f.harga).toLocaleString('id-ID');
  $('buyBox').classList.remove('hidden');
  $('buyBox').scrollIntoView({behavior:'smooth',block:'nearest'});
}
async function buyNow(){
  const nama=$('buyNama').value.trim()||'Tamu', hp=$('buyHp').value.trim(), ch=$('buyChannel').value;
  $('buyMsg').textContent='⏳ Bikin transaksi Tripay...';
  const fd=new FormData(); fd.append('frame',buyFrame.code); fd.append('channel',ch); fd.append('nama',nama); fd.append('hp',hp);
  try{
    const r=await fetch('api/tripay_create.php',{method:'POST',body:fd}); const j=await r.json();
    if(!j.ok) throw new Error(j.msg||'gagal');
    buyRef=j.ref;
    let html=`✅ Order <b>${j.ref}</b> • Rp${Number(j.amount).toLocaleString('id-ID')}<br>`;
    if(j.qr_url) html+=`<img src="${j.qr_url}" class="w-40 h-40 mx-auto rounded-xl border mt-2"><div class="text-[11px]">Scan QRIS buat bayar</div>`;
    else if(j.pay_code) html+=`<div class="mt-1 text-lg tracking-widest bg-gray-100 rounded-xl py-2">${j.pay_code}</div>`;
    if(j.checkout_url) html+=`<a href="${j.checkout_url}" target="_blank" class="inline-block mt-2 px-4 py-2 rounded-full bg-pink-500 text-white text-xs font-extrabold">Buka halaman bayar →</a>`;
    html+=`<div class="text-[11px] mt-1">⏳ Nunggu bayar... auto-kebuka kalau PAID. <a href="bayar.php?ref=${j.ref}" target="_blank" class="text-pink-600 font-bold">buka detail →</a></div>`;
    $('buyMsg').innerHTML=html;
    clearInterval(buyTimer);
    buyTimer=setInterval(async ()=>{
      const s=await fetch('api/order_status.php?ref='+buyRef).then(x=>x.json()).catch(()=>null);
      if(s&&s.ok&&s.order.status==='PAID'){ clearInterval(buyTimer); unlockPaid(buyFrame.code); $('buyMsg').innerHTML='✅ PAID! Frame kebuka, gas edit! 🎉'; boom(); }
    },3000);
  }catch(e){ $('buyMsg').textContent='❌ '+e.message+' (belum setting Tripay? cek admin/settings.php)'; }
}
function unlockPaid(code){
  if(!paidFrames.includes(code)){ paidFrames.push(code); try{localStorage.setItem('jb_paid',JSON.stringify(paidFrames));}catch(e){} }
  curTheme=FRAMES.find(f=>f.code===code)||curTheme;
  $('buyBox').classList.add('hidden'); $('lockMsg').classList.add('hidden');
  renderFrames(); rebuild(true);
}
renderFrames(); rebuild(false); loadFrames();
</script>

<!-- MODAL BELI -->
<div id="buyBox" class="hidden card-gemoy p-4 mt-3 border-orange-300">
  <div class="font-extrabold">💰 Beli frame: <span id="buyFrameName"></span></div>
  <div class="grid sm:grid-cols-3 gap-2 mt-2">
    <input id="buyNama" maxlength="30" placeholder="nama kamu" class="px-3 py-2 rounded-xl border-2 text-sm font-bold">
    <input id="buyHp" maxlength="20" placeholder="HP 08..." class="px-3 py-2 rounded-xl border-2 text-sm font-bold">
    <select id="buyChannel" class="px-3 py-2 rounded-xl border-2 text-sm font-bold"><option value="QRIS">QRIS</option><option value="QRISC">QRIS Custom</option><option value="DANA">DANA</option><option value="SHOPEEPAY">ShopeePay</option><option value="OVO">OVO</option><option value="BRIVA">BRI VA</option><option value="BNIVA">BNI VA</option><option value="BCAVA">BCA VA</option><option value="MANDIRIVA">Mandiri VA</option><option value="ALFAMART">Alfamart</option><option value="INDOMARET">Indomaret</option></select>
  </div>
  <button onclick="buyNow()" class="mt-2 w-full py-2.5 rounded-2xl bg-gradient-to-r from-pink-500 to-purple-500 text-white font-extrabold text-sm">Bayar via Tripay 🚀</button>
  <div id="buyMsg" class="text-xs font-bold mt-2"></div>
</div>
<?php include 'includes/footer.php'; ?>
