<?php
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }
SEO::set(['title' => 'Payment Failed | ' . SITE_NAME, 'noindex' => true]);
include BASE_PATH . '/includes/header.php';
?>
<section class="section" style="min-height:55vh;display:grid;place-items:center;text-align:center;">
  <div class="container container-sm">
    <div class="icon-badge" style="width:80px;height:80px;margin:0 auto 20px;font-size:2rem;background:linear-gradient(135deg,#ff5d6c,#ff9d5c);"><i class="fas fa-xmark"></i></div>
    <h1>Payment Failed</h1>
    <p class="lead">Something went wrong with your payment. No charge was made. Please try again or contact us.</p>
    <div class="flex" style="justify-content:center;margin-top:18px;">
      <a href="<?= e(url('/pricing')) ?>" class="btn btn--primary btn--lg">Try Again</a>
      <a href="<?= e(url('/contact')) ?>" class="btn btn--ghost btn--lg">Contact Support</a>
    </div>
  </div>
</section>
<?php include BASE_PATH . '/includes/footer.php'; ?>
