<?php
/** Portfolio listing */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }

$items = DB::all("SELECT * FROM portfolio WHERE status='published' ORDER BY sort_order, id");
$cats  = array_values(array_unique(array_filter(array_map(fn($p) => $p['category'], $items))));

SEO::set(['schema' => [SEO::breadcrumb(['Home' => '/', 'Portfolio' => '/portfolio'])]], 'portfolio');
include BASE_PATH . '/includes/header.php';
?>
<?= breadcrumb_html(['Home' => '/', 'Portfolio' => '/portfolio']) ?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow">Portfolio</span>
    <h1>Our Work, <span class="gradient-text">Their Growth</span></h1>
    <p>A selection of brands we've helped scale across social, search and paid channels.</p>
  </div>
</section>
<section class="section">
  <div class="container">
    <?php if (empty($items)): ?>
      <?= empty_state('Projects are being added. Check back soon!') ?>
    <?php else: ?>
      <?php if (!empty($cats)): ?>
      <div class="filters reveal" data-filter-group data-filter-target="#portfolioGrid">
        <button class="filter-btn active" data-filter="all">All</button>
        <?php foreach ($cats as $c): ?><button class="filter-btn" data-filter="<?= e($c) ?>"><?= e($c) ?></button><?php endforeach; ?>
      </div>
      <?php endif; ?>
      <div class="cards-grid" id="portfolioGrid">
        <?php foreach ($items as $p): ?>
          <article class="portfolio-card reveal" data-cat="<?= e($p['category']) ?>">
            <div class="portfolio-card__img"><img src="<?= e(img($p['featured_image'], 'portfolio')) ?>" alt="<?= e($p['title']) ?>" loading="lazy"></div>
            <div class="portfolio-card__body">
              <span class="tag"><?= e($p['category'] ?: 'Project') ?></span>
              <h3><?= e($p['title']) ?></h3>
              <?php if ($p['client_industry']): ?><p style="font-size:0.86rem;"><?= e($p['client_industry']) ?></p><?php endif; ?>
              <?php if ($p['result_metric']): ?><div class="result"><i class="fas fa-arrow-trend-up"></i> <?= e($p['result_metric']) ?></div><?php endif; ?>
              <div style="margin-top:14px;"><a href="<?= e(url('portfolio/' . $p['slug'])) ?>" class="link-arrow">View Details <i class="fas fa-arrow-right-long"></i></a></div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php include BASE_PATH . '/templates/partials/cta-band.php'; ?>
<?php include BASE_PATH . '/includes/footer.php'; ?>
