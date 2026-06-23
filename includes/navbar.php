<?php
/** Public navigation bar + announcement + mobile drawer. */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }

$announceOn = setting('announcement_enabled', '0') === '1';
$announceText = setting('announcement_text', '');
$announceLink = setting('announcement_link', '');
$navMenu = $headerMenu ?? DB::all("SELECT * FROM menus WHERE location='header' AND status='active' ORDER BY sort_order, id");
$logo = $logo ?? setting('logo', '');
$cp = $currentPath ?? '/';

function nav_is_active(string $url, string $current): bool
{
    $url = '/' . trim(parse_url($url, PHP_URL_PATH) ?? $url, '/');
    if ($url === '/' ) return $current === '/' || $current === '';
    return $current === $url || str_starts_with($current, $url . '/');
}
?>
<?php if ($announceOn && $announceText !== ''): ?>
<div class="announcement">
  <?php if ($announceLink !== ''): ?>
    <a href="<?= e(url($announceLink)) ?>"><?= e($announceText) ?> <i class="fas fa-arrow-right-long" aria-hidden="true"></i></a>
  <?php else: ?>
    <?= e($announceText) ?>
  <?php endif; ?>
</div>
<?php endif; ?>

<header class="site-header" id="siteHeader">
  <div class="container">
    <nav class="nav" aria-label="Primary">
      <a href="<?= e(url('/')) ?>" class="brand" aria-label="<?= e(setting('site_name', SITE_NAME)) ?> home">
        <?php if ($logo !== ''): ?>
          <img src="<?= e(upload_url($logo)) ?>" alt="<?= e(setting('site_name', SITE_NAME)) ?> logo">
        <?php else: ?>
          <span class="brand__mark">B</span>
          <span><?= e(setting('site_name', SITE_NAME)) ?></span>
        <?php endif; ?>
      </a>

      <div class="nav__links">
        <?php foreach ($navMenu as $m): if ($m['is_button']) continue; ?>
          <a href="<?= e(url($m['url'])) ?>"<?= nav_is_active($m['url'], $cp) ? ' class="active"' : '' ?>><?= e($m['title']) ?></a>
        <?php endforeach; ?>
      </div>

      <div class="nav__actions">
        <button class="theme-toggle" data-theme-toggle aria-label="Toggle theme">&#9728;</button>
        <?php foreach ($navMenu as $m): if (!$m['is_button']) continue; ?>
          <a href="<?= e(url($m['url'])) ?>" class="btn btn--primary btn--sm btn--header"><?= e($m['title']) ?></a>
        <?php endforeach; ?>
        <button class="nav__toggle" data-drawer-open aria-label="Open menu" aria-controls="mobileDrawer" aria-expanded="false">
          <span></span><span></span><span></span>
        </button>
      </div>
    </nav>
  </div>
</header>

<!-- Mobile drawer -->
<div class="drawer-backdrop" id="drawerBackdrop"></div>
<aside class="mobile-drawer" id="mobileDrawer" aria-label="Mobile menu">
  <button class="drawer-close" data-drawer-close aria-label="Close menu">&times;</button>
  <?php foreach ($navMenu as $m): ?>
    <a href="<?= e(url($m['url'])) ?>"<?= $m['is_button'] ? ' class="btn btn--primary"' : '' ?> data-drawer-close><?= e($m['title']) ?></a>
  <?php endforeach; ?>
</aside>
