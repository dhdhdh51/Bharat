<?php
require_once __DIR__ . '/includes/auth.php';
if (current_admin()) { redirect('admin/'); }

$token = (string) input('token');
$id = (int) input('id', 0);
$error = '';
$success = '';

$admin = $id > 0 ? DB::row('SELECT * FROM admins WHERE id = ?', [$id]) : null;
$validToken = false;
if ($admin && !empty($admin['reset_token']) && !empty($admin['reset_expires'])) {
    if (hash_equals($admin['reset_token'], hash('sha256', $token)) && strtotime($admin['reset_expires']) > time()) {
        $validToken = true;
    }
}

if (is_post() && $validToken) {
    if (!csrf_check()) {
        $error = 'Security token expired. Please try again.';
    } else {
        $pw = (string) input('password');
        $pw2 = (string) input('password_confirm');
        if (strlen($pw) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($pw !== $pw2) {
            $error = 'Passwords do not match.';
        } else {
            DB::update('admins', [
                'password'      => password_hash($pw, PASSWORD_DEFAULT),
                'reset_token'   => null,
                'reset_expires' => null,
                'failed_attempts' => 0,
                'locked_until'  => null,
            ], 'id = :id', ['id' => $admin['id']]);
            activity_log((int) $admin['id'], 'password_reset', 'Password changed via reset link');
            flash_set('success', 'Password updated. Please sign in.');
            redirect('admin/login.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
<title>Reset Password · <?= e(setting('site_name', SITE_NAME)) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= e(admin_url('assets/css/admin.css')) ?>?v=1.0">
</head>
<body class="admin">
<div class="ad-login">
  <div class="ad-login__card">
    <div class="ad-login__logo"><span class="ad-login__mark">B</span> <?= e(setting('site_name', SITE_NAME)) ?></div>
    <p class="sub">Set a new password</p>
    <?php if (!$validToken): ?>
      <div class="ad-alert ad-alert--error">This reset link is invalid or has expired.</div>
      <a href="<?= e(admin_url('forgot-password.php')) ?>" class="ad-btn ad-btn--primary" style="width:100%;justify-content:center;">Request a new link</a>
    <?php else: ?>
      <?php if ($error !== ''): ?><div class="ad-alert ad-alert--error"><?= e($error) ?></div><?php endif; ?>
      <form method="post" action="<?= e(admin_url('reset-password.php?token=' . urlencode($token) . '&id=' . $id)) ?>">
        <?= csrf_field() ?>
        <div class="ad-field"><label>New Password</label><input type="password" name="password" required minlength="8"></div>
        <div class="ad-field"><label>Confirm Password</label><input type="password" name="password_confirm" required minlength="8"></div>
        <button type="submit" class="ad-btn ad-btn--primary" style="width:100%;justify-content:center;">Update Password</button>
      </form>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
