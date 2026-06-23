<?php
require_once __DIR__ . '/includes/auth.php';
if (current_admin()) { redirect('admin/'); }

$notice = '';
$type = 'info';
if (is_post()) {
    if (!csrf_check()) {
        $notice = 'Security token expired. Please try again.'; $type = 'error';
    } elseif (!rate_limit('forgot_pw', 4, 900)) {
        $notice = 'Too many requests. Please wait a while.'; $type = 'error';
    } else {
        $email = clean_text(input('email'), 150);
        $admin = valid_email($email) ? DB::row('SELECT * FROM admins WHERE email = ? AND status = "active"', [$email]) : null;
        if ($admin) {
            $token = bin2hex(random_bytes(32));
            $hash = hash('sha256', $token);
            DB::update('admins', [
                'reset_token'   => $hash,
                'reset_expires' => date('Y-m-d H:i:s', time() + 3600),
            ], 'id = :id', ['id' => $admin['id']]);
            $link = admin_url('reset-password.php?token=' . $token . '&id=' . $admin['id']);
            $body = '<p>Hi ' . e($admin['name']) . ',</p><p>We received a request to reset your admin password. '
                . 'Click below to set a new one. This link expires in 1 hour.</p>'
                . '<p><a href="' . e($link) . '" style="display:inline-block;padding:12px 22px;background:#4f8cff;color:#fff;border-radius:8px;text-decoration:none;">Reset Password</a></p>'
                . '<p>If you did not request this, you can ignore this email.</p>';
            @send_mail($admin['email'], 'Reset your admin password', $body);
            activity_log((int) $admin['id'], 'password_reset_requested', '');
        }
        // Always show the same message (avoid user enumeration)
        $notice = 'If that email exists, a password reset link has been sent.'; $type = 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
<title>Forgot Password · <?= e(setting('site_name', SITE_NAME)) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= e(admin_url('assets/css/admin.css')) ?>?v=1.0">
</head>
<body class="admin">
<div class="ad-login">
  <div class="ad-login__card">
    <div class="ad-login__logo"><span class="ad-login__mark">B</span> <?= e(setting('site_name', SITE_NAME)) ?></div>
    <p class="sub">Reset your password</p>
    <?php if ($notice !== ''): ?><div class="ad-alert ad-alert--<?= e($type) ?>"><?= e($notice) ?></div><?php endif; ?>
    <form method="post" action="<?= e(admin_url('forgot-password.php')) ?>">
      <?= csrf_field() ?>
      <div class="ad-field"><label>Email</label><input type="email" name="email" required autofocus></div>
      <button type="submit" class="ad-btn ad-btn--primary" style="width:100%;justify-content:center;">Send Reset Link</button>
    </form>
    <div style="text-align:center;margin-top:14px;"><a href="<?= e(admin_url('login.php')) ?>" style="color:var(--ad-muted);font-size:0.85rem;">Back to login</a></div>
    <?php if (setting('smtp_enabled','0') !== '1'): ?>
    <div class="ad-demo">Note: SMTP is not configured, so emails are sent via PHP mail(). Configure SMTP in Settings for reliable delivery.</div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
