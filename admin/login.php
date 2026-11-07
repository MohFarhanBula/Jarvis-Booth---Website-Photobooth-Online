<?php require_once 'auth.php';
if (admin_login()) { header('Location: index.php'); exit; }
$err = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $u = trim($_POST['username'] ?? ''); $p = $_POST['password'] ?? '';
  if (db_ok()) {
    global $conn;
    $st = $conn->prepare("SELECT id, username, password_hash, role FROM users WHERE username=? LIMIT 1");
    $st->bind_param("s",$u); $st->execute(); $res=$st->get_result(); $row=$res?$res->fetch_assoc():null; $st->close();
    if ($row && password_verify($p, $row['password_hash'])) {
      session_regenerate_id(true);
      $_SESSION['admin_id']=$row['id']; $_SESSION['admin_user']=$row['username']; $_SESSION['admin_role']=$row['role'];
      header('Location: index.php'); exit;
    }
  }
  $err = 'Username atau password salah. Silakan coba lagi.';
}
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — Jarvis Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"></head>
<body class="bg-primary" style="background:linear-gradient(135deg,#4e73df,#224abe)">
<div class="container"><div class="row justify-content-center"><div class="col-xl-5 col-lg-6 col-md-8">
<div class="card o-hidden border-0 shadow-lg my-5"><div class="card-body p-5">
<div class="text-center"><h1 class="h4 text-gray-900 mb-1">🤖 Jarvis Admin</h1><p class="text-muted small mb-4">Masuk untuk mengelola frame, order & foto. Default: bos / jarvis123</p></div>
<?php if($err): ?><div class="alert alert-danger py-2 small"><?= htmlspecialchars($err) ?></div><?php endif; ?>
<form method="POST">
<div class="mb-3"><label class="form-label small fw-bold">Username</label><input name="username" class="form-control" required autocomplete="username" placeholder="bos"></div>
<div class="mb-3"><label class="form-label small fw-bold">Password</label><div class="input-group"><input id="pw" name="password" type="password" class="form-control" required autocomplete="current-password" placeholder="••••••"><button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('pw').type=document.getElementById('pw').type==='password'?'text':'password'"><i class="bi bi-eye"></i></button></div></div>
<button class="btn btn-primary w-100">Login <i class="bi bi-box-arrow-in-right"></i></button>
</form><hr><div class="text-center"><a class="small" href="../index.php">← Kembali ke Booth</a></div>
</div></div></div></div></div></body></html>
