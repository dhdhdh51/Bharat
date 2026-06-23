<?php
/** Case study detail */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }
$slug = $GLOBALS['route_slug'] ?? '';
$cs = DB::row("SELECT * FROM case_studies WHERE slug = ? AND status='published'", [$slug]);
if (!$cs) { http_response_code(404); include BASE_PATH . '/templates/404.php'; return; }

$gallery = array_filter(array_map('trim', explode('|', (string) $cs['gallery'])));
$related = DB::all("SELECT title, slug FROM services WHERE status='published' ORDER BY RAND() LIMIT 3");

SEO::set([
    'title'       => $cs['meta_title'] ?: ($cs['title'] . ' | Case Study | ' . SITE_NAME),
    'description' => $cs['meta_description'] ?: str_excerpt($cs['challenge'], 160),
    'image'       => $cs['featured_image'] ? upload_url($cs['featured_image']) : '',
    'type'        => 'article',
    'schema'      => [SEO::breadcrumb(['Home' => '/', 'Case Studies' => '/case-studies', $cs['title'] => 'case-studies/' . $cs['slug']])],
], 'case-study:' . $cs['slug']);

$shareUrl = rawurlencode(url('case-studies/' . $cs['slug']));
$shareText = rawurlencode($cs['title']);
include BASE_PATH . '/includes/header.php';
?>
<?= breadcrumb_html(['Home' => '/', 'Case Studies' => '/case-studies', $cs['title'] => 'case-studies/' . $cs['slug']]) ?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow"><?= e($cs['industry'] ?: 'Case Study') ?><?= $cs['client_name'] ? ' · ' . e($cs['client_name']) : '' ?></span>
    <h1><?= e($cs['title']) ?></h1>
  </div>
</section>
<section class="section">
  <div class="container">
    <img src="<?= e(img($cs['featured_image'], 'portfolio')) ?>" alt="<?= e($cs['title']) ?>" style="border-radius:var(--radius);width:100%;max-height:500px;object-fit:cover;" loading="lazy">

    <div class="metric-row" style="margin-top:36px;">
      <?php if ($cs['reach_growth']): ?><div class="metric"><div class="v"><?= e($cs['reach_growth']) ?></div><div class="l">Reach Growth</div></div><?php endif; ?>
      <?php if ($cs['leads_growth']): ?><div class="metric"><div class="v"><?= e($cs['leads_growth']) ?></div><div class="l">Leads Growth</div></div><?php endif; ?>
      <?php if ($cs['revenue_impact']): ?><div class="metric"><div class="v"><?= e($cs['revenue_impact']) ?></div><div class="l">Revenue Impact</div></div><?php endif; ?>
      <?php if ($cs['roi_metric']): ?><div class="metric"><div class="v"><?= e($cs['roi_metric']) ?></div><div class="l">ROI / ROAS</div></div><?php endif; ?>
    </div>

    <div class="prose" style="margin-top:44px;">
      <?php if ($cs['challenge']): ?><h2>The Challenge</h2><p><?= nl2br(e($cs['challenge'])) ?></p><?php endif; ?>
      <?php if ($cs['strategy']): ?><h2>The Strategy</h2><p><?= nl2br(e($cs['strategy'])) ?></p><?php endif; ?>
      <?php if ($cs['execution']): ?><h2>The Execution</h2><p><?= nl2br(e($cs['execution'])) ?></p><?php endif; ?>
      <?php if ($cs['results']): ?><h2>The Results</h2><p><?= nl2br(e($cs['results'])) ?></p><?php endif; ?>

      <?php if ($cs['before_metric'] || $cs['after_metric']): ?>
      <div class="split" style="margin:24px 0;">
        <div class="card"><div class="l muted">Before</div><div class="price" style="font-size:1.6rem;"><?= e($cs['before_metric'] ?: 'N/A') ?></div></div>
        <div class="card" style="border-color:var(--primary);"><div class="l muted">After</div><div class="price" style="font-size:1.6rem;color:var(--gold);"><?= e($cs['after_metric'] ?: 'N/A') ?></div></div>
      </div>
      <?php endif; ?>
    </div>

    <?php if (!empty($gallery)): ?>
    <div class="cards-grid" style="margin-top:32px;">
      <?php foreach ($gallery as $g): ?><img src="<?= e(upload_url($g)) ?>" alt="<?= e($cs['title']) ?> screenshot" style="border-radius:var(--radius);width:100%;object-fit:cover;aspect-ratio:4/3;" loading="lazy"><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($cs['testimonial'])): ?>
    <div class="card testimonial-card reveal" style="margin-top:36px;max-width:760px;margin-inline:auto;">
      <div class="stars">★★★★★</div>
      <p class="review">&ldquo;<?= e($cs['testimonial']) ?>&rdquo;</p>
      <div class="person"><span class="avatar"></span><div><div class="person__name"><?= e($cs['client_name'] ?: 'Client') ?></div><div class="person__role"><?= e($cs['industry']) ?></div></div></div>
    </div>
    <?php endif; ?>

    <div class="flex" style="justify-content:center;margin-top:32px;">
      <span class="muted" style="align-self:center;">Share:</span>
      <a class="btn btn--ghost btn--sm" target="_blank" rel="noopener" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $shareUrl ?>"><i class="fa-brands fa-linkedin"></i> LinkedIn</a>
      <a class="btn btn--ghost btn--sm" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&text=<?= $shareText ?>"><i class="fa-brands fa-x-twitter"></i> Twitter</a>
      <a class="btn btn--ghost btn--sm" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>"><i class="fa-brands fa-facebook"></i> Facebook</a>
    </div>
  </div>
</section>

<?php if (!empty($related)): ?>
<section class="section" style="background:var(--bg-2);">
  <div class="container">
    <div class="section-head text-center reveal"><h2>Related Services</h2></div>
    <div class="cards-grid">
      <?php foreach ($related as $r): ?>
        <a class="card service-card reveal" href="<?= e(url('service/' . $r['slug'])) ?>"><h3><?= e($r['title']) ?></h3><span class="link-arrow">Explore <i class="fas fa-arrow-right-long"></i></span></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include BASE_PATH . '/templates/partials/cta-band.php'; ?>
<?php include BASE_PATH . '/includes/footer.php'; ?>
