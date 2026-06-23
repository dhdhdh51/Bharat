<?php
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }
SEO::set(['title' => 'Payment Successful | ' . SITE_NAME, 'noindex' => true]);
include BASE_PATH . '/includes/header.php';
?>
<section class="section" style="min-height:55vh;display:grid;place-items:center;text-align:center;">
  <div class="container container-sm">
    <div class="icon-badge" style="width:80px;height:80px;margin:0 auto 20px;font-size:2rem;background:linear-gradient(135deg,#2fd47a,#2fe6e0);"><i class="fas fa-check"></i></div>
    <h1>Payment Successful</h1>
    <p class="lead">Thank you! Your payment was received. Our team will reach out shortly with next steps.</p>
    <?php $ref = e(input('ref', '')); if ($ref !== ''): ?><p class="muted">Reference: <?= $ref ?></p><?php endif; ?>
    <a href="<?= e(url('/')) ?>" class="btn btn--primary btn--lg" style="margin-top:18px;">Back to Home</a>
  </div>
</section>
<?php include BASE_PATH . '/includes/footer.php'; ?>
