<?php
/**
 * Public site header / <head> + navigation.
 * Expects SEO::set() to have been called before include (falls back to defaults).
 */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }
if (empty(SEO::$data)) { SEO::set([]); }

$themeDefault = setting('theme_default', 'dark');
$ga   = setting('google_analytics_id', '');
$gsc  = setting('google_search_console', '');
$pixel = setting('facebook_pixel', '');
$customHead = setting_raw('custom_head', '');
$customBody = setting_raw('custom_body', '');
$headerMenu = DB::all("SELECT * FROM menus WHERE location='header' AND status='active' ORDER BY sort_order, id");
$logo = setting('logo', '');
$currentPath = '/' . ltrim(strtok($_SERVER['REQUEST_URI'] ?? '/', '?'), '/');
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= e($themeDefault) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#0a0e1a">
<?php if ($gsc !== ''): ?><meta name="google-site-verification" content="<?= e($gsc) ?>"><?php endif; ?>
<?= SEO::renderMeta() ?>
<link rel="icon" href="<?= e($logo !== '' ? upload_url(setting('favicon', $logo)) : asset('images/favicon.svg')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=1.0">
<?= SEO::renderSchema() ?>
<?php if ($customHead !== ''): ?><?= $customHead ?><?php endif; ?>
<?php if ($ga !== ''): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($ga) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= e($ga) ?>');</script>
<?php endif; ?>
<?php if ($pixel !== ''): ?>
<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','<?= e($pixel) ?>');fbq('track','PageView');</script>
<?php endif; ?>
</head>
<body>
<?php if ($customBody !== ''): ?><?= $customBody ?><?php endif; ?>

<!-- Preloader -->
<div class="preloader" id="preloader" aria-hidden="true">
  <div class="preloader__logo">
    <span class="preloader__mark">B</span>
    <span><?= e(setting('site_name', SITE_NAME)) ?></span>
  </div>
</div>

<?php
// Flash messages (server-rendered)
foreach (flash_get() as $f):
?>
<div class="container" style="padding-top:14px;">
  <div class="alert alert--<?= e($f['type'] === 'error' ? 'error' : ($f['type'] === 'success' ? 'success' : 'info')) ?>"><?= e($f['message']) ?></div>
</div>
<?php endforeach; ?>

<?php include __DIR__ . '/navbar.php'; ?>

<main id="main">
