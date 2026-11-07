<?php
// Jalankan sekali: http://localhost/photoboot/install.php
require_once 'config.php';
$msg = [];
// coba buat DB + tabel
try {
  $tmp = new mysqli($DB_HOST, $DB_USER, $DB_PASS);
  $tmp->query("CREATE DATABASE IF NOT EXISTS photoboot_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
  $tmp->select_db("photoboot_db");
  $sql = file_get_contents(__DIR__.'/db.sql');
  // jalankan per statement sederhana
  foreach (explode(';', $sql) as $st) {
    $st = trim($st); if (!$st) continue;
    // skip USE yg double
    $tmp->query($st);
  }
  $msg[] = "✅ Database photoboot_db + tabel photos siap!";
  $tmp->close();
} catch (Throwable $e) { $msg[] = "❌ DB gagal: ".$e->getMessage()." — nyalain MySQL di XAMPP ya!"; }
if (!is_dir(__DIR__.'/uploads')) mkdir(__DIR__.'/uploads',0777,true);
$msg[] = "📁 Folder uploads siap!";
?>
<!doctype html><html><body style="font-family:sans-serif;padding:40px">
<h1>🤖 Jarvis Installer</h1>
<?php foreach($msg as $m) echo "<p>$m</p>"; ?>
<p><a href="index.php">→ Gas ke Photobooth 📸</a></p>
</body></html>
