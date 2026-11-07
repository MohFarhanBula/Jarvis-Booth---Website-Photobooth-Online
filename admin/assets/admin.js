// Jarvis Admin helpers: sidebar, table search/sort/paginate, lightbox
function admToggleNav(){ document.body.classList.toggle('nav-open'); }
// generic table enhancer: <table class="tbl" data-enhance> + <input data-tsearch="tableId">
document.addEventListener('DOMContentLoaded', ()=>{
  document.querySelectorAll('table[data-enhance]').forEach(setupTable);
  document.querySelectorAll('[data-tsearch]').forEach(inp=>{
    inp.addEventListener('input', ()=>{
      const t=document.getElementById(inp.dataset.tsearch);
      if(t) t.dispatchEvent(new CustomEvent('adm-search',{detail:inp.value}));
    });
  });
});
function setupTable(tbl){
  const tbody=tbl.querySelector('tbody'); if(!tbody) return;
  const rows=[...tbody.rows]; let q='', sortIdx=-1, dir=1, page=0; const per=parseInt(tbl.dataset.per||'12');
  const info=document.createElement('div'); info.className='adm-sub'; info.style.cssText='padding:8px 2px';
  tbl.after(info);
  const pager=document.createElement('div'); pager.style.cssText='display:flex;gap:8px;align-items:center;padding:8px 2px';
  pager.innerHTML=`<button class="btn" data-p="prev">←</button><span class="adm-sub" data-i></span><button class="btn" data-p="next">→</button>`;
  info.after(pager);
  tbl.addEventListener('adm-search', e=>{ q=(e.detail||'').toLowerCase(); page=0; draw(); });
  tbl.querySelectorAll('th').forEach((th,i)=>{
    th.title='Klik untuk sortir';
    th.addEventListener('click', ()=>{ if(sortIdx===i) dir*=-1; else {sortIdx=i;dir=1;} draw(); });
  });
  pager.querySelector('[data-p="prev"]').onclick=()=>{ if(page>0){page--;draw();} };
  pager.querySelector('[data-p="next"]').onclick=()=>{ page++;draw(); };
  function filtered(){
    let r=rows.filter(tr=>!q||tr.innerText.toLowerCase().includes(q));
    if(sortIdx>=0) r=r.slice().sort((a,b)=>{
      const x=a.cells[sortIdx]?.innerText||'', y=b.cells[sortIdx]?.innerText||'';
      const nx=parseFloat(x.replace(/[^0-9.-]/g,'')), ny=parseFloat(y.replace(/[^0-9.-]/g,''));
      if(!isNaN(nx)&&!isNaN(ny)&&x.match(/[0-9]/)) return (nx-ny)*dir;
      return x.localeCompare(y)*dir;
    });
    return r;
  }
  function draw(){
    const r=filtered(); const pages=Math.max(1,Math.ceil(r.length/per)); page=Math.min(page,pages-1);
    rows.forEach(tr=>tr.style.display='none');
    r.slice(page*per,page*per+per).forEach(tr=>tr.style.display='');
    info.textContent=`Menampilkan ${r.length? page*per+1:0}–${Math.min(r.length,(page+1)*per)} dari ${r.length} data`;
    pager.querySelector('[data-i]').textContent=`Hal ${page+1}/${pages}`;
    if(!r.length && !tbody.querySelector('.adm-empty')){
      // show empty via info only to keep simple
    }
  }
  draw();
}
function admLightbox(src){ const lb=document.getElementById('lb'); if(!lb) return; lb.querySelector('img').src=src; lb.classList.add('open'); }
function admLbClose(){ document.getElementById('lb')?.classList.remove('open'); }
document.addEventListener('keydown', e=>{ if(e.key==='Escape') admLbClose(); });
