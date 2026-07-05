<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/helpers.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/index.php'); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            // Regenerate the session ID on privilege change to prevent session fixation.
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role']      = $user['role'];
            header('Location: ' . BASE_URL . '/index.php'); exit;
        }
        $error = 'Invalid email or password.';
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>IMS — CG SHOP</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700&family=DM+Sans:wght@400;500&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css"/>
  <style>
    body{display:flex;align-items:center;justify-content:center;min-height:100vh;background:var(--bg)}
    .login-wrap{width:100%;max-width:380px;padding:0 20px}
    .login-brand{text-align:center;margin-bottom:32px}
    .login-icon{width:52px;height:52px;background:var(--accent);border-radius:14px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px}
    .login-icon svg{width:28px;height:28px;stroke:#0f1117;fill:none;stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round}
    .login-title{font-family:var(--font-head);font-size:24px;font-weight:700}
    .login-sub{font-size:13px;color:var(--text2);margin-top:4px}
    .login-card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:28px}
    .login-card .btn{width:100%;justify-content:center;padding:11px;font-size:14px;margin-top:4px}
    .demo-box{background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:12px 16px;margin-top:20px;font-size:12px;color:var(--text2);font-family:var(--font-mono);line-height:1.8}
    .login-footer{text-align:center;margin-top:20px;font-size:11px;color:var(--text3);font-family:var(--font-mono)}
  </style>
</head>
<body>
<div class="login-wrap">
  <div class="login-brand">
    <div class="login-icon">
      <svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="6" rx="1"/><rect x="2" y="12" width="10" height="9" rx="1"/><rect x="15" y="12" width="7" height="4" rx="1"/><rect x="15" y="19" width="7" height="2" rx="1"/></svg>
    </div>
    <div class="login-title">CG SHOP</div>
    <div class="login-sub">Inventory Management System</div>
  </div>
  <div class="login-card">
    <?php if ($error): ?>
    <div class="alert alert-danger" style="margin-bottom:16px"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="<?= BASE_URL ?>/login.php">
      <?= csrf_field() ?>
      <div class="form-group">
        <label class="form-label">Email address</label>
        <input type="email" name="email" class="form-control" placeholder="admin@ims.com"
               value="<?= e($_POST['email'] ?? '') ?>" required autofocus/>
      </div>
      <div class="form-group" style="margin-bottom:20px">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" required/>
      </div>
      <button type="submit" class="btn btn-primary">Sign in</button>
    </form>
  </div>
  <!--
    Demo-credentials box removed for production. If you want it back for local
    XAMPP testing only, wrap it in: <?php if (!getenv('DB_HOST')): ?> ... <?php endif; ?>
  -->
  <div class="login-footer">Develop by: Justine Villarosa · XAMPP · PHP <?= PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION ?></div>
</div>
</body>
</html>