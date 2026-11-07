</div><!-- /container -->
<footer class="mt-16 border-t-4 border-dashed border-pink-200 bg-white">
  <div class="max-w-6xl mx-auto px-4 py-8 flex flex-col sm:flex-row gap-4 items-center justify-between text-sm">
    <div>🤖 Dibuat dengan <b>kegemoyan</b> oleh <b>Jarvis</b> • 5rb/sesi, wajib login, langsung cekrek!</div>
    <div class="flex gap-2">
      <a href="sesi.php" class="px-3 py-1.5 rounded-full bg-pink-100 hover:bg-pink-200 font-bold">Coba lagi 📸</a>
      <a href="galeri.php" class="px-3 py-1.5 rounded-full bg-purple-100 hover:bg-purple-200 font-bold">Hall of Fame 🏆</a>
    </div>
  </div>
</footer>
<script>
// maskot cerewet global
const JOKES = ["Senyum! Jangan kayak lagi ditagih pinjol 😬","Jepret! Yang merem ngulang ya 📸","Cakep! Naik 200% nih ✨","Pose jamet dikit boleh lah 🤪","Tahan... 1 detik jadi kenangan 7 turunan"];
function jarvisSay(el){ if(el) el.textContent = JOKES[Math.floor(Math.random()*JOKES.length)]; }
function boom(){ if(window.confetti) confetti({particleCount:120, spread:75, origin:{y:.7}}); }
</script>
</body>
</html>
