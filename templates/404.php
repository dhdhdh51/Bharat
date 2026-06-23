<?php
/** 404 Not Found */
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../includes/bootstrap.php';
}
if (!headers_sent()) { http_response_code(404); }
SEO::set(['title' => 'Page Not Found | ' . SITE_NAME, 'description' => 'The page you are looking for could not be found.', 'noindex' => true]);
include BASE_PATH . '/includes/header.php';
?>
<section class="section" style="min-height:60vh;display:grid;place-items:center;text-align:center;">
  <div class="container container-sm">
    <div class="gradient-text" style="font-family:var(--font-head);font-weight:800;font-size:clamp(5rem,18vw,9rem);line-height:1;">404</div>
    <h1 style="margin:10px 0;">Page Not Found</h1>
    <p class="lead">The page you're looking for doesn't exist or has moved.</p>
    <div class="flex" style="justify-content:center;margin-top:24px;">
      <a href="<?= e(url('/')) ?>" class="btn btn--primary btn--lg">Back to Home</a>
      <a href="<?= e(url('/services')) ?>" class="btn btn--ghost btn--lg">Browse Services</a>
    </div>
  </div>
</section>
<?php include BASE_PATH . '/includes/footer.php'; ?>
