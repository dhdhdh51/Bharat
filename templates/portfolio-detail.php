<?php
/** Portfolio project detail */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }

$slug = $GLOBALS['route_slug'] ?? '';
$p = DB::row("SELECT * FROM portfolio WHERE slug = ? AND status='published'", [$slug]);
if (!$p) { http_response_code(404); include BASE_PATH . '/templates/404.php'; return; }

$gallery = DB::all("SELECT * FROM portfolio_images WHERE portfolio_id = ? ORDER BY sort_order, id", [$p['id']]);
$services = pipe_list($p['services_provided']);
$related = DB::all("SELECT title, slug, featured_image, category, result_metric FROM portfolio WHERE id != ? AND status='published' ORDER BY RAND() LIMIT 3", [$p['id']]);

SEO::set([
    'title'       => $p['meta_title'] ?: ($p['title'] . ' | Portfolio | ' . SITE_NAME),
    'description' => $p['meta_description'] ?: str_excerpt($p['description'], 160),
    'image'       => $p['featured_image'] ? upload_url($p['featured_image']) : '',
    'schema'      => [SEO::breadcrumb(['Home' => '/', 'Portfolio' => '/portfolio', $p['title'] => 'portfolio/' . $p['slug']])],
], 'portfolio:' . $p['slug']);

include BASE_PATH . '/includes/header.php';
?>
<?= breadcrumb_html(['Home' => '/', 'Portfolio' => '/portfolio', $p['title'] => 'portfolio/' . $p['slug']]) ?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow"><?= e($p['category'] ?: 'Project') ?></span>
    <h1><?= e($p['title']) ?></h1>
    <?php if ($p['result_metric']): ?><p class="lead"><i class="fas fa-arrow-trend-up" style="color:var(--gold);"></i> <?= e($p['result_metric']) ?></p><?php endif; ?>
  </div>
</section>
<section class="section">
  <div class="container">
    <img src="<?= e(img($p['featured_image'], 'portfolio')) ?>" alt="<?= e($p['title']) ?>" style="border-radius:var(--radius);width:100%;max-height:520px;object-fit:cover;" loading="lazy">
    <div class="split" style="margin-top:40px;">
      <div class="reveal prose" style="margin:0;">
        <h2>About the Project</h2>
        <p><?= nl2br(e($p['description'] ?: 'Project details coming soon.')) ?></p>
      </div>
      <div class="reveal">
        <div class="card">
          <h3 style="margin-bottom:14px;">Project Details</h3>
          <ul class="list-check">
            <?php if ($p['client_industry']): ?><li>Industry: <?= e($p['client_industry']) ?></li><?php endif; ?>
            <?php foreach ($services as $s): ?><li><?= e($s) ?></li><?php endforeach; ?>
            <?php if ($p['result_metric']): ?><li>Result: <?= e($p['result_metric']) ?></li><?php endif; ?>
          </ul>
          <?php if (!empty($p['project_url'])): ?>
          <a href="<?= e($p['project_url']) ?>" target="_blank" rel="noopener nofollow" class="btn btn--ghost btn--block" style="margin-top:18px;">Visit Project <i class="fas fa-up-right-from-square"></i></a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php if (!empty($gallery)): ?>
    <div class="cards-grid" style="margin-top:48px;">
      <?php foreach ($gallery as $g): ?>
        <img src="<?= e(upload_url($g['image'])) ?>" alt="<?= e($g['alt_text'] ?: $p['title']) ?>" style="border-radius:var(--radius);width:100%;object-fit:cover;aspect-ratio:4/3;" loading="lazy">
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php if (!empty($related)): ?>
<section class="section" style="background:var(--bg-2);">
  <div class="container">
    <div class="section-head text-center reveal"><h2>More Work</h2></div>
    <div class="cards-grid">
      <?php foreach ($related as $r): ?>
        <article class="portfolio-card reveal">
          <div class="portfolio-card__img"><img src="<?= e(img($r['featured_image'], 'portfolio')) ?>" alt="<?= e($r['title']) ?>" loading="lazy"></div>
          <div class="portfolio-card__body"><span class="tag"><?= e($r['category']) ?></span><h3><?= e($r['title']) ?></h3>
          <div style="margin-top:12px;"><a href="<?= e(url('portfolio/' . $r['slug'])) ?>" class="link-arrow">View <i class="fas fa-arrow-right-long"></i></a></div></div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include BASE_PATH . '/templates/partials/cta-band.php'; ?>
<?php include BASE_PATH . '/includes/footer.php'; ?>
