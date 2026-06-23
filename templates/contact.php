<?php
/** Contact page */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }
$prefillPackage = trim((string) input('package', ''));
SEO::set(['schema' => [SEO::breadcrumb(['Home' => '/', 'Contact' => '/contact'])]], 'contact');
include BASE_PATH . '/includes/header.php';
?>
<?= breadcrumb_html(['Home' => '/', 'Contact' => '/contact']) ?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow">Contact</span>
    <h1>Let's <span class="gradient-text">Talk Growth</span></h1>
    <p>Have a project in mind? Reach out and our team will get back within one business day.</p>
  </div>
</section>
<section class="section">
  <div class="container">
    <div class="split" style="align-items:flex-start;">
      <div class="reveal">
        <div class="card" style="margin-bottom:18px;"><div class="icon-badge"><i class="fas fa-phone"></i></div><h3>Call Us</h3><p><a href="<?= e(call_link()) ?>" style="color:var(--primary-2);"><?= e(setting('contact_phone', 'N/A')) ?></a></p></div>
        <div class="card" style="margin-bottom:18px;"><div class="icon-badge"><i class="fas fa-envelope"></i></div><h3>Email Us</h3><p><a href="mailto:<?= e(setting('contact_email')) ?>" style="color:var(--primary-2);"><?= e(setting('contact_email', 'N/A')) ?></a></p></div>
        <div class="card" style="margin-bottom:18px;"><div class="icon-badge"><i class="fas fa-location-dot"></i></div><h3>Visit Us</h3><p><?= e(setting('address', 'Address coming soon')) ?></p></div>
        <?php if (whatsapp_link() !== '#'): ?>
        <a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener" class="btn btn--primary btn--block"><i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp</a>
        <?php endif; ?>
      </div>
      <div class="reveal">
        <div class="card">
          <h3 style="margin-bottom:16px;">Send a Message</h3>
          <form data-ajax action="<?= e(url('api/contact.php')) ?>" method="post">
            <?= csrf_field() ?>
            <?= honeypot_field() ?>
            <div class="form-message" role="status" aria-live="polite"></div>
            <div class="form-grid">
              <div class="field"><label>Name <span class="req">*</span></label><input type="text" name="name" required><span class="error-msg"></span></div>
              <div class="field"><label>Email <span class="req">*</span></label><input type="email" name="email" required><span class="error-msg"></span></div>
              <div class="field"><label>Phone</label><input type="tel" name="phone"><span class="error-msg"></span></div>
              <div class="field"><label>Subject</label><input type="text" name="subject" value="<?= $prefillPackage !== '' ? e('Inquiry: ' . $prefillPackage . ' package') : '' ?>"><span class="error-msg"></span></div>
              <div class="field full"><label>Message <span class="req">*</span></label><textarea name="message" required><?= $prefillPackage !== '' ? e('Hi, I am interested in the ' . $prefillPackage . ' package.') : '' ?></textarea><span class="error-msg"></span></div>
              <div class="field full"><button type="submit" class="btn btn--primary btn--lg btn--block">Send Message <i class="fas fa-paper-plane"></i></button></div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <?php $map = setting_raw('map_embed', ''); if (trim((string)$map) !== ''): ?>
    <div style="margin-top:40px;border-radius:var(--radius);overflow:hidden;border:1px solid var(--border);">
      <?= $map ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php include BASE_PATH . '/includes/footer.php'; ?>
