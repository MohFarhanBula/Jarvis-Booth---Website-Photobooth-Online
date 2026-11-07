<?php
// Jarvis Photobooth - Config DB
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "photoboot_db";

$conn = null;
try {
  $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
  if ($conn->connect_error) { $conn = null; }
  else { $conn->set_charset("utf8mb4"); }
} catch (Throwable $e) { $conn = null; }

function db_ok() { global $conn; return $conn !== null; }
function base_url() {
  $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
  $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
  $dir = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
  // naik ke root photoboot
  // /photoboot/api -> /photoboot ; /photoboot -> /photoboot
  if (str_ends_with($dir, '/api')) $dir = substr($dir, 0, -4);
  if ($dir === '') $dir = '/photoboot';
  return $proto . '://' . $host . $dir;
}
