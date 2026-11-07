<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';
function admin_login() { return $_SESSION['admin_id'] ?? null; }
function admin_require() {
  if (!admin_login()) { header('Location: login.php'); exit; }
}
function admin_user() { return ['id'=>$_SESSION['admin_id']??0,'username'=>$_SESSION['admin_user']??'','role'=>$_SESSION['admin_role']??'']; }
function admin_is_super() { return ($_SESSION['admin_role'] ?? '') === 'superadmin'; }
function csrf_token() {
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
  return $_SESSION['csrf'];
}
function csrf_check($t) { return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$t); }
function flash_set($msg, $type='success') { $_SESSION['flash'] = ['msg'=>$msg,'type'=>$type]; }
function flash_get() { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }
