<?php
/** Pricing / packages */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }
$packages = DB::all("SELECT * FROM pricing_packages WHERE status='published' ORDER BY sort_order, id");
$faqs = DB::all("SELECT * FROM faqs WHERE status='published' ORDER BY sort_order, id LIMIT 6");
SEO::set(['schema' => [SEO::breadcrumb(['Home' => '/', 'Pricing' => '/pricing'])]], 'pricing');
include BASE_PATH . '/includes/header.php';
?>
<?= breadcrumb_html(['Home' => '/', 'Pricing' => '/pricing']) ?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow">Pricing</span>
    <h1>Simple, <span class="gradient-text">Transparent Pricing</span></h1>
    <p>Choose a plan that fits your growth stage. Upgrade or customize any time.</p>
  </div>
</section>
<section class="section">
  <div class="container">
    <?php if (empty($packages)): ?>
      <?= empty_state('Our packages are being finalized. Contact us for a custom quote.') ?>
    <?php else: ?>
    <div class="pricing-grid">
      <?php foreach ($packages as $pk): $feat = pipe_list($pk['features']); $numeric = is_numeric(str_replace(',', '', $pk['price'])); ?>
        <div class="card price-card reveal<?= $pk['is_popular'] ? ' popular' : '' ?>">
          <?php if ($pk['is_popular']): ?><span class="badge-popular">Most Popular</span><?php endif; ?>
          <h3><?= e($pk['name']) ?></h3>
          <div class="price"><?= $numeric && $pk['currency'] === 'INR' ? '₹' : '' ?><?= e($pk['price']) ?> <small><?= e($pk['duration']) ?></small></div>
          <?php if ($pk['description']): ?><p style="font-size:0.9rem;"><?= e($pk['description']) ?></p><?php endif; ?>
          <ul class="price-features">
            <?php foreach ($feat as $f): ?><li><?= e($f) ?></li><?php endforeach; ?>
          </ul>
          <?php if (!empty($pk['notes'])): ?><p class="form-note"><?= e($pk['notes']) ?></p><?php endif; ?>
          <?php if (!empty($pk['payment_link'])): ?>
            <a href="<?= e($pk['payment_link']) ?>" target="_blank" rel="noopener" class="btn btn--primary btn--block"><?= e($pk['cta_text'] ?: 'Get Started') ?></a>
          <?php else: ?>
            <a href="<?= e(url('/contact?package=' . urlencode($pk['name']))) ?>" class="btn btn--primary btn--block"><?= e($pk['cta_text'] ?: 'Get Started') ?></a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- Package inquiry -->
<section class="section" style="background:var(--bg-2);">
  <div class="container container-sm">
    <div class="section-head text-center reveal"><span class="eyebrow">Not Sure?</span><h2>Request a Custom Quote</h2><p>Tell us your goals and we'll recommend the perfect plan.</p></div>
    <div class="card reveal"><?php $leadFormSource = 'package_inquiry'; include BASE_PATH . '/templates/partials/lead-form.php'; ?></div>
  </div>
</section>

<?php if (!empty($faqs)): ?>
<section class="section">
  <div class="container">
    <div class="section-head text-center reveal"><span class="eyebrow">FAQ</span><h2>Pricing Questions</h2></div>
    <div class="faq reveal">
      <?php foreach ($faqs as $f): ?><div class="faq-item"><button class="faq-q" aria-expanded="false"><span><?= e($f['question']) ?></span><i class="fas fa-plus ico"></i></button><div class="faq-a"><p><?= e($f['answer']) ?></p></div></div><?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include BASE_PATH . '/includes/footer.php'; ?>
