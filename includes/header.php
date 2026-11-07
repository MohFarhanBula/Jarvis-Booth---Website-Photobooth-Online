<?php
// header bersama
if (session_status() === PHP_SESSION_NONE) session_start();
$title = $title ?? 'Jarvis Photobooth 📸';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($title) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = { theme: { extend: { fontFamily: { gemoy: ['"Baloo 2"','"Fredoka"','system-ui'] }, colors: { candy: {pink:'#FF8FB1', peach:'#FFC59B', lilac:'#C7B9FF', mint:'#A8E6CF', lemon:'#FFE89B', sky:'#AEE3FF'} } } } }
</script>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700;800&family=Fredoka:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
</head>
<body class="font-gemoy bg-[#FFF7F0] text-[#3A2E3A] min-h-screen">
<!-- NAVBAR LUCU -->
<nav class="sticky top-0 z-50 bg-white/85 backdrop-blur border-b-4 border-black/5">
  <div class="max-w-6xl mx-auto px-4 py-3 flex items-center gap-3">
    <a href="index.php" class="flex items-center gap-2 group">
      <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-candy-pink to-candy-lilac grid place-items-center text-2xl rotate-[-6deg] group-hover:rotate-[6deg] transition shadow-lg border-2 border-white">📸</div>
      <div class="leading-tight">
        <div class="font-extrabold text-xl tracking-tight">Jarvis<span class="text-pink-500">Booth</span></div>
        <div class="text-xs opacity-70 -mt-1">jelek dikit gapapa, yang penting pede ✨</div>
      </div>
    </a>
    <div class="ml-auto flex items-center gap-2 text-sm font-bold">
      <a href="sesi.php" class="hidden sm:inline px-4 py-2 rounded-full bg-black text-white hover:bg-pink-500 transition">📷 Foto Now</a>
      <a href="transaksi.php" class="hidden sm:inline px-4 py-2 rounded-full bg-white border-2 border-black/10 hover:scale-105 transition">💳 Transaksiku</a>
      <a href="galeri.php" class="px-4 py-2 rounded-full bg-candy-lemon border-2 border-black/10 hover:scale-105 transition">🖼️ Galeri</a>
      <?php if (!empty($_SESSION['user_id'])): ?>
        <span class="hidden sm:inline px-3 py-2 rounded-full bg-pink-100" title="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>">👋 <?= htmlspecialchars(mb_strimwidth($_SESSION['user_nama'] ?? 'Teman', 0, 12, '…')) ?></span>
        <a href="logout.php" class="px-3 py-2 rounded-full bg-gray-100 hover:bg-red-100 transition inline-flex items-center gap-1" title="Keluar"><i class="bi bi-box-arrow-right"></i><span class="hidden sm:inline text-xs">Keluar</span></a>
      <?php else: ?>
        <a href="login.php" class="px-4 py-2 rounded-full bg-gradient-to-r from-pink-500 to-purple-500 text-white hover:scale-105 transition">Masuk ✨</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="bg-gradient-to-r from-candy-pink via-candy-peach via-candy-lemon via-candy-mint to-candy-sky h-1.5"></div>
</nav>
<div class="max-w-6xl mx-auto px-4">
