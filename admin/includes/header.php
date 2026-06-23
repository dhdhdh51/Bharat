<?php
/** Admin layout header. Expects $pageTitle. Requires auth.php loaded. */
if (!defined('BASE_PATH')) { exit('Forbidden'); }
$__admin = current_admin();
$pageTitle = $pageTitle ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($pageTitle) ?> · <?= e(setting('site_name', SITE_NAME)) ?> Admin</title>
<link rel="icon" href="<?= e(asset('images/favicon.svg')) ?>">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= e(admin_url('assets/css/admin.css')) ?>?v=1.0">
</head>
<body class="admin">
<div class="ad-layout">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="ad-overlay" id="adOverlay"></div>
  <div class="ad-main">
    <div class="ad-topbar">
      <div style="display:flex;align-items:center;gap:12px;">
        <button class="ad-menu-toggle" id="adMenuToggle" aria-label="Toggle menu"><i class="fas fa-bars"></i></button>
        <h1><?= e($pageTitle) ?></h1>
      </div>
      <div class="ad-topbar__right">
        <a href="<?= e(url('/')) ?>" target="_blank" class="ad-btn ad-btn--sm ad-btn--ghost"><i class="fas fa-up-right-from-square"></i> View Site</a>
        <div class="ad-user">
          <span class="ad-user__avatar"><?= e(strtoupper(substr($__admin['name'] ?? 'A', 0, 1))) ?></span>
          <div style="line-height:1.2;">
            <div style="font-weight:600;"><?= e($__admin['name'] ?? 'Admin') ?></div>
            <div style="font-size:0.74rem;color:var(--ad-muted);"><?= e($__admin['role_name'] ?? '') ?></div>
          </div>
        </div>
        <a href="<?= e(admin_url('logout.php')) ?>" class="ad-btn ad-btn--sm ad-btn--danger" title="Logout"><i class="fas fa-right-from-bracket"></i></a>
      </div>
    </div>
    <div class="ad-content">
    <?php foreach (flash_get() as $f): ?>
      <div class="ad-alert ad-alert--<?= e($f['type'] === 'error' ? 'error' : ($f['type'] === 'success' ? 'success' : 'info')) ?>"><?= e($f['message']) ?></div>
    <?php endforeach; ?>
