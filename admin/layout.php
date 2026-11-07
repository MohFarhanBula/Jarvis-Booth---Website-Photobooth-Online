<?php require_once 'auth.php'; admin_require(); $u = admin_user();
function layout_head($title, $active='', $sub=''){
  $pend = 0;
  try{ global $conn; if(db_ok()){ $q=$conn->query("SELECT COUNT(*) c FROM orders WHERE status='UNPAID'"); if($q) $pend=$q->fetch_assoc()['c']; } }catch(Throwable $e){}
  $menus = [
    ['index.php','bi-speedometer2','Dashboard','index','wrap'],
    ['frames.php','bi-images','Frames','frames','wrap'],
    ['orders.php','bi-receipt','Orders','orders','wrap'],
    ['sessions.php','bi-ticket-perforated','Sessions','sessions','wrap'],
    ['photos.php','bi-camera','Foto & Room','photos','wrap'],
    ['settings.php','bi-gear','Setting','settings','wrap'],
  ];
  if(($GLOBALS['u']['role']??'')==='superadmin') $menus[]=['users.php','bi-people','Users','users','wrap'];
  $flash = flash_get();
  echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.htmlspecialchars($title).' — Jarvis Admin</title>'
    .'<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">'
    .'<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">'
    .'<link rel="stylesheet" href="assets/admin.css">'
    .'<script src="assets/admin.js"></script></head>'
    .'<body id="page-top">';
  echo '<div id="wrapper"><ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">'
    .'<a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php"><div class="sidebar-brand-icon">🤖</div><div class="sidebar-brand-text mx-2">Jarvis Admin</div></a>'
    .'<hr class="sidebar-divider my-0">';
  foreach($menus as $m){
    $on = $active===$m[3]?' active':'';
    $bd = ($m[3]==='orders'&&$pend>0)?' <span class="badge bg-warning text-dark ms-1">'.$pend.'</span>':'';
    echo '<li class="nav-item'.$on.'"><a class="nav-link" href="'.$m[0].'"><i class="bi '.$m[1].'"></i><span>'.$m[2].$bd.'</span></a></li>';
  }
  echo '<hr class="sidebar-divider"><div class="sidebar-heading">Lainnya</div>'
    .'<li class="nav-item"><a class="nav-link" href="../index.php"><i class="bi bi-camera-fill"></i><span>Lihat Booth</span></a></li>'
    .'<li class="nav-item"><a class="nav-link" href="../harga.php"><i class="bi bi-tag-fill"></i><span>Halaman Harga</span></a></li>'
    .'<hr class="sidebar-divider d-none d-md-block"></ul>';
  echo '<div id="content-wrapper" class="d-flex flex-column"><div id="content">'
    .'<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">'
    .'<button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle me-3" onclick="document.getElementById(\'wrapper\').classList.toggle(\'toggled\')"><i class="bi bi-list"></i></button>'
    .'<div class="d-none d-sm-block"><h1 class="h5 mb-0 text-gray-800">'.htmlspecialchars($title).'</h1><small class="text-muted">'.htmlspecialchars($sub).'</small></div>'
    .'<ul class="navbar-nav ms-auto"><li class="nav-item"><span class="nav-link"><span class="me-2 d-none d-lg-inline text-gray-600 small"><b>'.htmlspecialchars($GLOBALS['u']['username']).'</b> · '.htmlspecialchars($GLOBALS['u']['role']).'</span><span class="img-profile rounded-circle">'.strtoupper(substr($GLOBALS['u']['username']??'A',0,1)).'</span></span></li>'
    .'<li class="nav-item"><a class="nav-link" href="logout.php" title="Keluar"><i class="bi bi-box-arrow-right"></i></a></li></ul></nav>';
  echo '<div class="container-fluid">';
  if($flash){
    $bg = ($flash['type']==='danger')?'danger':(($flash['type']==='warn')?'warning':'success');
    echo '<div class="alert alert-'.$bg.' alert-dismissible fade show" role="alert"><b>'.htmlspecialchars($flash['msg']).'</b><button class="btn-close" data-bs-dismiss="alert"></button></div>';
  }
}
function layout_foot(){
  echo '</div></div><footer class="sticky-footer bg-white"><div class="container my-auto"><div class="copyright text-center my-auto"><span>JarvisBooth Admin • SB Admin style • '.date('Y').'</span></div></div></footer></div>'
  .'<div class="modal fade" id="confirmModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="cmTitle">Konfirmasi</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" id="cmBody"></div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger" id="cmOk"><i class="bi bi-trash3"></i> Ya, lanjutkan</button></div></div></div></div>'
  .'<div class="modal fade" id="lbModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content"><div class="modal-body text-center"><img id="lbImg" class="img-fluid rounded"></div></div></div></div>'
  .'<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>'
  .'<script>let cmForm=null;function askConfirm(title,html,form){document.getElementById("cmTitle").textContent=title;document.getElementById("cmBody").innerHTML=html;cmForm=form;bootstrap.Modal.getOrCreateInstance(document.getElementById("confirmModal")).show();}document.getElementById("cmOk")?.addEventListener("click",()=>{if(cmForm)cmForm.submit();});function admLightbox(src){document.getElementById("lbImg").src=src;bootstrap.Modal.getOrCreateInstance(document.getElementById("lbModal")).show();}function admLbClose(){}</script>'
  .'</body></html>';
}
