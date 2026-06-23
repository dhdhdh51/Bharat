<?php
/** Public footer + floating buttons + scripts. */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }

$footerMenu = DB::all("SELECT * FROM menus WHERE location='footer' AND status='active' ORDER BY sort_order, id");
$socials = DB::all("SELECT * FROM social_links WHERE status='active' ORDER BY sort_order, id");
$footerServices = DB::all("SELECT title, slug FROM services WHERE status='published' ORDER BY sort_order, id LIMIT 7");
$customFooter = setting_raw('custom_footer', '');
$wa = whatsapp_link();
$phone = setting('contact_phone', '');
?>
</main>

<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-col footer-about">
        <a href="<?= e(url('/')) ?>" class="brand">
          <span class="brand__mark">B</span>
          <span><?= e(setting('site_name', SITE_NAME)) ?></span>
        </a>
        <p><?= e(setting('footer_about', '')) ?></p>
        <?php if (!empty($socials)): ?>
        <div class="footer-social">
          <?php foreach ($socials as $s): ?>
            <a href="<?= e($s['url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($s['platform']) ?>">
              <i class="fa-brands fa-<?= e($s['icon'] ?: 'globe') ?>" aria-hidden="true"></i>
            </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <div class="footer-col">
        <h4>Services</h4>
        <ul>
          <?php if (empty($footerServices)): ?>
            <li><a href="<?= e(url('/services')) ?>">All Services</a></li>
          <?php else: foreach ($footerServices as $s): ?>
            <li><a href="<?= e(url('service/' . $s['slug'])) ?>"><?= e($s['title']) ?></a></li>
          <?php endforeach; endif; ?>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Quick Links</h4>
        <ul>
          <li><a href="<?= e(url('/about')) ?>">About Us</a></li>
          <li><a href="<?= e(url('/portfolio')) ?>">Portfolio</a></li>
          <li><a href="<?= e(url('/case-studies')) ?>">Case Studies</a></li>
          <li><a href="<?= e(url('/pricing')) ?>">Pricing</a></li>
          <li><a href="<?= e(url('/blog')) ?>">Blog</a></li>
          <li><a href="<?= e(url('/industries')) ?>">Industries</a></li>
          <li><a href="<?= e(url('/free-audit')) ?>">Free Audit</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Get In Touch</h4>
        <ul>
          <?php if ($phone !== ''): ?><li><a href="<?= e(call_link()) ?>"><i class="fas fa-phone" aria-hidden="true"></i> <?= e($phone) ?></a></li><?php endif; ?>
          <?php if (setting('contact_email') !== ''): ?><li><a href="mailto:<?= e(setting('contact_email')) ?>"><i class="fas fa-envelope" aria-hidden="true"></i> <?= e(setting('contact_email')) ?></a></li><?php endif; ?>
          <?php if (setting('address') !== ''): ?><li><i class="fas fa-location-dot" aria-hidden="true"></i> <?= e(setting('address')) ?></li><?php endif; ?>
        </ul>
        <h4 style="margin-top:22px;">Newsletter</h4>
        <form class="newsletter-form" data-ajax action="<?= e(url('api/newsletter.php')) ?>" method="post">
          <?= csrf_field() ?>
          <?= honeypot_field() ?>
          <input type="email" name="email" placeholder="Your email" aria-label="Email for newsletter" required>
          <button type="submit" class="btn btn--primary btn--sm" aria-label="Subscribe"><i class="fas fa-paper-plane" aria-hidden="true"></i></button>
        </form>
        <div class="form-message" style="margin-top:8px;"></div>
      </div>
    </div>

    <div class="footer-bottom">
      <span>&copy; <?= date('Y') ?> <?= e(setting('copyright_text', SITE_NAME . '. All rights reserved.')) ?></span>
      <ul style="display:flex;gap:16px;flex-wrap:wrap;">
        <?php foreach ($footerMenu as $m): ?>
          <li><a href="<?= e(url($m['url'])) ?>"><?= e($m['title']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</footer>

<!-- Floating action buttons -->
<div class="fab-stack">
  <button class="fab fab--top" id="backToTop" aria-label="Back to top"><i class="fas fa-arrow-up" aria-hidden="true"></i></button>
  <?php if ($phone !== ''): ?>
  <a class="fab fab--call" href="<?= e(call_link()) ?>" aria-label="Call us"><i class="fas fa-phone" aria-hidden="true"></i></a>
  <?php endif; ?>
  <?php if ($wa !== '#'): ?>
  <a class="fab fab--whatsapp" href="<?= e($wa) ?>" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></a>
  <?php endif; ?>
</div>

<script src="<?= asset('js/main.js') ?>?v=1.0" defer></script>
<?php if ($customFooter !== ''): ?><?= $customFooter ?><?php endif; ?>
</body>
</html>
