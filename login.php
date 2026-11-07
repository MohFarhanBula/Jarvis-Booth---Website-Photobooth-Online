<?php
require_once __DIR__ . '/includes/auth_user.php';
$next = $_GET['next'] ?? 'index.php';
if (user_current()) { header('Location: ' . $next); exit; }
// ambil client id
$client_id = '';
if (db_ok()) { global $conn; $r=$conn->query("SELECT nilai FROM settings WHERE kunci='google_client_id' LIMIT 1"); if($r&&$x=$r->fetch_assoc()) $client_id=$x['nilai']; }
$title = 'Masuk 📸 JarvisBooth';
include 'includes/header.php';
?>
<div class="py-10 max-w-md mx-auto text-center">
  <div class="card-gemoy p-8">
    <div class="text-6xl">🤖📸</div>
    <h1 class="text-3xl font-extrabold mt-2">Wajib Login Dulu!</h1>
    <p class="font-semibold opacity-70 text-sm mt-1">Biar sesi fotomu kesimpen rapi + room LDR bisa sinkron. Loginnya bebas, yang dibayar cuma sesinya 5rb 😌</p>
    <?php if ($client_id): ?>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <div id="g_id_onload" data-client_id="<?= htmlspecialchars($client_id) ?>" data-callback="onGoogle" data-auto_prompt="false"></div>
    <div class="g_id_signin mt-4 flex justify-center" data-type="standard" data-size="large" data-theme="outline" data-text="sign_in_with" data-shape="pill"></div>
    <?php else: ?>
    <div class="mt-3 p-3 rounded-2xl bg-yellow-50 border-2 border-yellow-200 text-xs font-bold text-left">⚠️ Google Client ID belum disetting admin (nanti diisi di panel). Sementara pakai login debug di bawah ya!</div>
    <?php endif; ?>
    <div class="mt-4 p-4 rounded-2xl bg-purple-50 border-2 border-purple-100 text-left">
      <div class="font-extrabold text-sm">🧪 Login debug (localhost)</div>
      <input id="dNama" maxlength="30" placeholder="Nama kamu" class="mt-2 w-full px-4 py-2 rounded-full border-2 font-bold text-sm outline-none">
      <input id="dEmail" maxlength="60" placeholder="Email (opsional)" class="mt-2 w-full px-4 py-2 rounded-full border-2 font-bold text-sm outline-none">
      <button onclick="debugLogin()" class="mt-2 w-full py-2.5 rounded-full bg-black text-white font-extrabold text-sm">Masuk Debug 🚀</button>
      <div id="dmsg" class="text-xs font-bold mt-1 h-4 text-pink-600"></div>
    </div>
  </div>
</div>
<script>
const NEXT = <?= json_encode($next) ?>;
async function onGoogle(resp){
  const fd=new FormData(); fd.append('credential', resp.credential);
  try{ const r=await fetch('api/auth_google.php',{method:'POST',body:fd}); const j=await r.json();
    if(j.ok) location.href=NEXT; else alert('❌ '+j.msg);
  }catch(e){ alert('Gagal login Google'); }
}
async function debugLogin(){
  const fd=new FormData(); fd.append('nama',document.getElementById('dNama').value); fd.append('email',document.getElementById('dEmail').value);
  try{ const r=await fetch('api/auth_debug.php',{method:'POST',body:fd}); const j=await r.json();
    if(j.ok) location.href=NEXT; else document.getElementById('dmsg').textContent='❌ '+j.msg;
  }catch(e){ document.getElementById('dmsg').textContent='❌ Gagal'; }
}
</script>
<?php include 'includes/footer.php'; ?>
