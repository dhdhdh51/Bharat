<?php
/** Case studies listing */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }
$items = DB::all("SELECT * FROM case_studies WHERE status='published' ORDER BY sort_order, id");
SEO::set(['schema' => [SEO::breadcrumb(['Home' => '/', 'Case Studies' => '/case-studies'])]], 'case-studies');
include BASE_PATH . '/includes/header.php';
?>
<?= breadcrumb_html(['Home' => '/', 'Case Studies' => '/case-studies']) ?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow">Case Studies</span>
    <h1>Deep Dives Into <span class="gradient-text">Real Results</span></h1>
    <p>See the strategy, execution and numbers behind our clients' growth.</p>
  </div>
</section>
<section class="section">
  <div class="container">
    <?php if (empty($items)): ?>
      <?= empty_state('Case studies are coming soon.') ?>
    <?php else: ?>
    <div class="cards-grid">
      <?php foreach ($items as $cs): ?>
        <article class="card reveal">
          <img src="<?= e(img($cs['featured_image'], 'portfolio')) ?>" alt="<?= e($cs['title']) ?>" style="border-radius:var(--radius-sm);width:100%;aspect-ratio:16/9;object-fit:cover;margin-bottom:16px;" loading="lazy">
          <span class="tag" style="color:var(--cyan);font-weight:700;text-transform:uppercase;font-size:0.76rem;"><?= e($cs['industry'] ?: 'Case Study') ?></span>
          <h3 style="margin:8px 0 12px;"><?= e($cs['title']) ?></h3>
          <div class="metric-row">
            <?php if ($cs['reach_growth']): ?><div class="metric"><div class="v"><?= e($cs['reach_growth']) ?></div><div class="l">Reach</div></div><?php endif; ?>
            <?php if ($cs['leads_growth']): ?><div class="metric"><div class="v"><?= e($cs['leads_growth']) ?></div><div class="l">Leads</div></div><?php endif; ?>
            <?php if ($cs['roi_metric']): ?><div class="metric"><div class="v"><?= e($cs['roi_metric']) ?></div><div class="l">ROI</div></div><?php endif; ?>
          </div>
          <div style="margin-top:16px;"><a href="<?= e(url('case-studies/' . $cs['slug'])) ?>" class="btn btn--primary btn--sm">Read Case Study</a></div>
        </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php include BASE_PATH . '/templates/partials/cta-band.php'; ?>
<?php include BASE_PATH . '/includes/footer.php'; ?>
