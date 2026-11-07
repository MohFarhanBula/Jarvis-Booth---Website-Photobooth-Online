<?php
require_once __DIR__ . '/includes/auth_user.php';
unset($_SESSION['user_id'], $_SESSION['user_nama'], $_SESSION['user_email'], $_SESSION['user_avatar']);
header('Location: index.php'); exit;
