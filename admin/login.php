<?php
require_once __DIR__ . '/includes/auth.php';

// Already logged in? go to dashboard
if (current_admin()) {
    redirect('admin/');
}

$error = '';
if (is_post()) {
    if (!csrf_check()) {
        $error = 'Security token expired. Please try again.';
    } else {
        $email = clean_text(input('email'), 150);
        $password = (string) input('password');
        [$ok, $msg] = admin_login($email, $password);
        if ($ok) {
            $to = $_SESSION['admin_redirect'] ?? '';
            unset($_SESSION['admin_redirect']);
            if ($to && str_contains($to, '/admin')) {
                redirect($to);
            }
            redirect('admin/');
        }
        $error = $msg;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Admin Login · <?= e(setting('site_name', SITE_NAME)) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= e(admin_url('assets/css/admin.css')) ?>?v=1.0">
</head>
<body class="admin">
<div class="ad-login">
  <div class="ad-login__card">
    <div class="ad-login__logo"><span class="ad-login__mark">B</span> <?= e(setting('site_name', SITE_NAME)) ?></div>
    <p class="sub">Sign in to your admin dashboard</p>
    <?php if ($error !== ''): ?><div class="ad-alert ad-alert--error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= e(admin_url('login.php')) ?>">
      <?= csrf_field() ?>
      <div class="ad-field">
        <label>Email</label>
        <input type="email" name="email" required autofocus value="<?= e(input('email')) ?>">
      </div>
      <div class="ad-field">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="ad-btn ad-btn--primary" style="width:100%;justify-content:center;">Sign In <i class="fas fa-arrow-right-long"></i></button>
    </form>
    <div style="text-align:center;margin-top:14px;">
      <a href="<?= e(admin_url('forgot-password.php')) ?>" style="color:var(--ad-muted);font-size:0.85rem;">Forgot password?</a>
    </div>
    <div class="ad-demo">
      <strong>Demo login:</strong><br>
      Email: <code>admin@bharatseo.com</code><br>
      Password: <code>Admin@123</code>
    </div>
  </div>
</div>
</body>
</html>
