<?php
if (!defined('BASE_PATH')) { require_once __DIR__ . '/../includes/bootstrap.php'; }
if (!headers_sent()) { http_response_code(403); }
SEO::set(['title' => 'Access Denied | ' . SITE_NAME, 'noindex' => true]);
include BASE_PATH . '/includes/header.php';
?>
<section class="section" style="min-height:55vh;display:grid;place-items:center;text-align:center;">
  <div class="container container-sm">
    <div class="gradient-text" style="font-family:var(--font-head);font-weight:800;font-size:clamp(4rem,14vw,7rem);">403</div>
    <h1>Access Denied</h1>
    <p class="lead">You don't have permission to view this page.</p>
    <a href="<?= e(url('/')) ?>" class="btn btn--primary btn--lg" style="margin-top:18px;">Back to Home</a>
  </div>
</section>
<?php include BASE_PATH . '/includes/footer.php'; ?>
