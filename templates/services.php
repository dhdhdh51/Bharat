<?php
/** Services listing */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }

$categories = DB::all("SELECT * FROM service_categories ORDER BY sort_order, id");
$services   = DB::all("SELECT * FROM services WHERE status='published' ORDER BY sort_order, id");

SEO::set([
    'schema' => [SEO::breadcrumb(['Home' => '/', 'Services' => '/services'])],
], 'services');

include BASE_PATH . '/includes/header.php';
?>
<?= breadcrumb_html(['Home' => '/', 'Services' => '/services']) ?>

<section class="page-hero">
  <div class="container">
    <span class="eyebrow">Our Services</span>
    <h1>Everything You Need to <span class="gradient-text">Grow Online</span></h1>
    <p>From social media and SEO to paid ads, branding and web design — one team, full-funnel growth.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if (empty($services)): ?>
      <?= empty_state('Services are being updated. Please check back soon.') ?>
    <?php else: ?>
      <?php foreach ($categories as $cat):
        $catServices = array_filter($services, fn($s) => (int) $s['category_id'] === (int) $cat['id']);
        if (empty($catServices)) continue; ?>
        <div class="section-head left reveal" style="margin-bottom:24px;">
          <h2><?= e($cat['name']) ?></h2>
          <?php if (!empty($cat['description'])): ?><p><?= e($cat['description']) ?></p><?php endif; ?>
        </div>
        <div class="cards-grid" style="margin-bottom:48px;">
          <?php foreach ($catServices as $s): ?>
            <a class="card service-card reveal" href="<?= e(url('service/' . $s['slug'])) ?>">
              <span class="card__shine"></span>
              <div class="icon-badge"><i class="fas fa-<?= e($s['icon'] ?: 'star') ?>" aria-hidden="true"></i></div>
              <h3><?= e($s['title']) ?></h3>
              <p><?= e(str_excerpt($s['short_description'] ?: $s['overview'], 120)) ?></p>
              <span class="link-arrow">Learn more <i class="fas fa-arrow-right-long" aria-hidden="true"></i></span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>

      <?php // Uncategorized services fallback
      $uncategorized = array_filter($services, fn($s) => empty($s['category_id']));
      if (!empty($uncategorized)): ?>
        <div class="cards-grid">
          <?php foreach ($uncategorized as $s): ?>
            <a class="card service-card reveal" href="<?= e(url('service/' . $s['slug'])) ?>">
              <div class="icon-badge"><i class="fas fa-<?= e($s['icon'] ?: 'star') ?>" aria-hidden="true"></i></div>
              <h3><?= e($s['title']) ?></h3>
              <p><?= e(str_excerpt($s['short_description'], 120)) ?></p>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

<?php include BASE_PATH . '/templates/partials/cta-band.php'; ?>
<?php include BASE_PATH . '/includes/footer.php'; ?>
