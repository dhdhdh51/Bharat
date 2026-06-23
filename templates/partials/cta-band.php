<?php
/** Reusable final CTA band. Optional $ctaTitle, $ctaText. */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }
$wa = whatsapp_link();
$booking = setting('booking_url', '');
?>
<section class="section">
  <div class="container">
    <div class="cta-band reveal">
      <h2><?= e($ctaTitle ?? 'Ready to Grow Your Business Online?') ?></h2>
      <p><?= e($ctaText ?? 'Book a free strategy call and discover exactly how we can scale your reach, leads and revenue.') ?></p>
      <div class="flex" style="justify-content:center;">
        <a href="<?= e($booking !== '' ? $booking : url('/contact')) ?>" class="btn btn--gold btn--lg"<?= $booking !== '' ? ' target="_blank" rel="noopener"' : '' ?>><i class="fas fa-calendar-check" aria-hidden="true"></i> Book Free Call</a>
        <?php if ($wa !== '#'): ?>
        <a href="<?= e($wa) ?>" target="_blank" rel="noopener" class="btn btn--ghost btn--lg"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp Us</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
