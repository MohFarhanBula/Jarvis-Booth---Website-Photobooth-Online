<?php $title = 'JarvisBooth 📸 Photobooth Online Paling Gemoy'; include 'includes/header.php'; ?>

<!-- HERO -->
<section class="pt-10 pb-6 text-center relative">
  <div class="inline-block px-4 py-1.5 rounded-full bg-white border-2 border-pink-200 font-bold text-sm shadow mb-4 floaty">✨ 5RB/SESI • WAJIB LOGIN • FOTO SEPUASNYA ✨</div>
  <h1 class="text-5xl sm:text-7xl font-extrabold leading-[0.95] tracking-tight">
    Muka Capek?<br>
    <span class="bg-gradient-to-r from-pink-500 via-purple-500 to-orange-400 bg-clip-text text-transparent">Foto Dulu Aja!</span> 🤪
  </h1>
  <p id="jarvisJoke" class="mt-4 text-lg opacity-80 font-semibold">Senyum! Jangan kayak lagi ditagih pinjol 😬</p>
  <div class="mt-6 flex flex-wrap justify-center gap-3">
    <a href="sesi.php" class="btn-gemoy px-8 py-4 rounded-2xl bg-black text-white text-xl font-extrabold shadow-[0_6px_0_#ff8fb1] hover:translate-y-1 hover:shadow-none">🚀 MULAI SESI 5RB</a>
    <span class="px-6 py-4 rounded-2xl bg-gray-100 text-xl font-extrabold border-2 opacity-60 cursor-not-allowed" title="Lagi dibenerin biar nggak lag">💘 Mode Bareng 🔜</span>
    <a href="harga.php" class="btn-gemoy px-6 py-4 rounded-2xl bg-white text-xl font-extrabold border-2">💰 Frame Kartun</a>
    <button onclick="gacha()" class="btn-gemoy px-6 py-4 rounded-2xl bg-candy-lemon text-xl font-extrabold border-2 border-black/10 shadow-[0_6px_0_#fff]">🎲 GACHA FRAME</button>
    <a href="galeri.php" class="btn-gemoy px-6 py-4 rounded-2xl bg-white text-xl font-extrabold border-2">🏆 Hall of Fame</a>
  </div>
  <p id="gachaHasil" class="mt-3 font-bold text-pink-600 h-6"></p>

  <!-- contoh strip -->
  <div class="mt-8 grid sm:grid-cols-3 gap-4 max-w-4xl mx-auto text-left">
    <div class="card-gemoy p-4 rotate-[-2deg]">
      <div class="rounded-2xl overflow-hidden bg-gradient-to-b from-pink-200 to-purple-200 aspect-[3/4] grid place-items-center text-7xl">🦊<br><span class="text-sm font-bold bg-white px-3 py-1 rounded-full">strip Korea • 4 pose</span></div>
      <div class="mt-3 font-extrabold">Buat bestie 👯 <span class="text-xs bg-green-100 px-2 py-0.5 rounded-full">PALING LARIS</span></div>
      <a href="sesi.php" class="mt-2 inline-block px-4 py-2 rounded-full bg-pink-500 text-white font-bold text-sm">Pakai ini →</a>
    </div>
    <div class="card-gemoy p-4 rotate-[1.5deg]">
      <div class="rounded-2xl overflow-hidden bg-gradient-to-b from-yellow-100 to-orange-200 aspect-[3/4] grid place-items-center text-7xl">🐱<br><span class="text-sm font-bold bg-white px-3 py-1 rounded-full">polaroid • 2 pose</span></div>
      <div class="mt-3 font-extrabold">Buat ayang 💘</div>
      <a href="sesi.php" class="mt-2 inline-block px-4 py-2 rounded-full bg-purple-500 text-white font-bold text-sm">Pakai ini →</a>
    </div>
    <div class="card-gemoy p-4 rotate-[-1deg]">
      <div class="rounded-2xl overflow-hidden bg-gradient-to-b from-green-100 to-teal-200 aspect-[3/4] grid place-items-center text-7xl">🤡<br><span class="text-sm font-bold bg-white px-3 py-1 rounded-full">single • meme absurd</span></div>
      <div class="mt-3 font-extrabold">Buat aib tongkrongan 💀</div>
      <a href="sesi.php" class="mt-2 inline-block px-4 py-2 rounded-full bg-black text-white font-bold text-sm">Pakai ini →</a>
    </div>
  </div>
</section>

<!-- MARQUEE -->
<div class="marquee bg-black text-white font-extrabold py-2.5 -mx-4 rounded-2xl rotate-[-1deg] my-8">
  <div>📸 5RB/SESI 📸 WAJIB LOGIN 📸 BISA STIKER MEME 📸 FILTER GEMOY 📸 DOWNLOAD HD 📸 SHARE KE WA 📸 QR CODE 📸 5RB/SESI 📸 WAJIB LOGIN 📸 BISA STIKER MEME 📸 FILTER GEMOY 📸 DOWNLOAD HD 📸 SHARE KE WA 📸 QR CODE&nbsp;</div>
</div>

<!-- CARA MAIN -->
<section class="grid sm:grid-cols-4 gap-3 my-8">
  <?php $steps=[['1️⃣','Pilih mode','Strip / polaroid / single. Gacha kalau galau.'],['2️⃣','Cekrek 3-2-1','Ikutin countdown Jarvis yang cerewet.'],['3️⃣','Edit sekocak mungkin','Tempel stiker meme + teks alay.'],['4️⃣','Pamer!','Download HD, share WA, scan QR.']];
  foreach($steps as $s): ?>
  <div class="card-gemoy p-5 text-center"><div class="text-4xl"><?= $s[0] ?></div><div class="font-extrabold mt-2"><?= $s[1] ?></div><div class="text-sm opacity-70"><?= $s[2] ?></div></div>
  <?php endforeach; ?>
</section>

<!-- TANTANGAN -->
<section class="card-gemoy p-6 sm:p-8 bg-gradient-to-r from-pink-50 to-purple-50 my-8 flex flex-col sm:flex-row items-center gap-6">
  <div class="text-7xl floaty">🤖</div>
  <div class="flex-1">
    <div class="font-extrabold text-2xl">Tantangan Hari Ini dari Jarvis:</div>
    <div id="challenge" class="text-lg font-bold text-purple-600">"Pose Kaget Lihat Tagihan Listrik ⚡"</div>
    <p class="text-sm opacity-70">Foto pakai pose ini, upload ke galeri, auto jadi legenda tongkrongan.</p>
  </div>
  <a href="sesi.php" class="btn-gemoy px-6 py-3 rounded-2xl bg-purple-600 text-white font-extrabold whitespace-nowrap">Gas Tantangan! 🚀</a>
</section>

<!-- TESTIMONI NGAKAK -->
<section class="my-8">
  <h2 class="text-3xl font-extrabold text-center">Kata mereka yang sudah tercyduk 📸</h2>
  <div class="grid sm:grid-cols-3 gap-3 mt-4">
    <div class="card-gemoy p-5">⭐⭐⭐⭐⭐<br><b>"Gila, foto aibku jadi aesthetic."</b><br><span class="text-sm opacity-70">— Rizky, korban strip 4</span></div>
    <div class="card-gemoy p-5">⭐⭐⭐⭐⭐<br><b>"Pacarku makin nempel sejak foto polaroid di sini."</b><br><span class="text-sm opacity-70">— Salsa, bucin level max</span></div>
    <div class="card-gemoy p-5">⭐⭐⭐⭐⭐<br><b>"Stiker meme-nya ngakak, guruku sampai like."</b><br><span class="text-sm opacity-70">— Bimo, murid teladan (?)</span></div>
  </div>
  <div class="text-center mt-6"><a href="sesi.php" onclick="boom();return true;" class="btn-gemoy inline-block px-10 py-4 rounded-2xl bg-gradient-to-r from-pink-500 to-purple-500 text-white text-2xl font-extrabold shadow-xl">GAS FOTO SEKARANG — 5RB/SESI! 📸</a><div class="text-xs mt-2 opacity-60">* login dulu, bayar sekali, foto sepuasnya 10 menit 🤞</div></div>
</section>

<script>
const CH=[`"Pose Kaget Lihat Tagihan Listrik ⚡"`,`"Pose Ditolak Crush Tapi Tetap Senyum 🙂"`,`"Pose Kucing Oren Jam 3 Pagi 🐈"`,`"Pose Foto KTP Tapi Dipaksa Ceria 🪪"`,`"Pose Tamu Kondangan Laper 🤤"`,`"Pose Anak Pramuka Paling Rajin 🫡"`];
document.getElementById('challenge').textContent = CH[Math.floor(Math.random()*CH.length)];
jarvisSay(document.getElementById('jarvisJoke'));
setInterval(()=>jarvisSay(document.getElementById('jarvisJoke')), 4000);
function gacha(){
  const f=['strip4','polaroid','single']; const n=['STRIP KOREA 4 POSE 🇰🇷','POLAROID BUAT AYANG 💘','SINGLE MEME ABSURD 🤡'];
  const i=Math.floor(Math.random()*3); boom();
  document.getElementById('gachaHasil').textContent='🎲 Kamu dapet: '+n[i]+'! Gas...';
  setTimeout(()=>location.href='sesi.php', 900);
}
</script>

<?php include 'includes/footer.php'; ?>
