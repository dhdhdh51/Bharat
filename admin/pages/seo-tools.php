<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_permission('seo', 'view');

// Build an SEO health checklist from live data
$checks = [];
$checks[] = ['Default meta title set', setting('meta_title') !== ''];
$checks[] = ['Default meta description set', setting('meta_description') !== ''];
$checks[] = ['Default OG image set', setting('og_image') !== ''];
$checks[] = ['Google Analytics configured', setting('google_analytics_id') !== ''];
$checks[] = ['Search Console verification set', setting('google_search_console') !== ''];
$checks[] = ['At least one published blog post', (int) DB::value("SELECT COUNT(*) FROM blogs WHERE status='published'", [], 0) > 0];
$checks[] = ['Services have meta descriptions', (int) DB::value("SELECT COUNT(*) FROM services WHERE status='published' AND (meta_description IS NULL OR meta_description='')", [], 0) === 0];
$checks[] = ['Social links configured', (int) DB::value("SELECT COUNT(*) FROM social_links WHERE status='active'", [], 0) > 0];
$checks[] = ['Contact details set', setting('contact_email') !== '' && setting('contact_phone') !== ''];
$checks[] = ['No pages set to no-index unexpectedly', (int) DB::value("SELECT COUNT(*) FROM page_seo WHERE noindex=1", [], 0) === 0];

$passed = count(array_filter($checks, fn($c) => $c[1]));
$score = (int) round($passed / max(1, count($checks)) * 100);

$pageTitle = 'SEO Tools';
include __DIR__ . '/../includes/header.php';
?>
<div class="ad-grid" style="grid-template-columns:repeat(auto-fit,minmax(min(100%,280px),1fr));margin-bottom:20px;">
  <div class="ad-card ad-stat"><div class="ad-stat__value"><?= $score ?>%</div><div class="ad-stat__label">SEO Health Score</div></div>
  <div class="ad-card">
    <div class="ad-card__head"><h3>Quick Links</h3></div>
    <div style="display:flex;flex-direction:column;gap:8px;">
      <a href="<?= e(url('sitemap.xml')) ?>" target="_blank" class="ad-btn ad-btn--sm"><i class="fas fa-sitemap"></i> View XML Sitemap</a>
      <a href="<?= e(url('robots.txt')) ?>" target="_blank" class="ad-btn ad-btn--sm"><i class="fas fa-robot"></i> View robots.txt</a>
      <a href="<?= e(admin_url('pages/seo.php')) ?>" class="ad-btn ad-btn--sm"><i class="fas fa-magnifying-glass-chart"></i> Manage Page SEO</a>
      <a href="<?= e(admin_url('pages/redirects.php')) ?>" class="ad-btn ad-btn--sm"><i class="fas fa-route"></i> Manage Redirects</a>
    </div>
  </div>
</div>
<div class="ad-card">
  <div class="ad-card__head"><h2>SEO Checklist</h2></div>
  <ul style="display:flex;flex-direction:column;gap:10px;">
    <?php foreach ($checks as $c): ?>
      <li style="display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:10px;background:var(--ad-surface-2);">
        <span style="color:<?= $c[1] ? 'var(--ad-success)' : 'var(--ad-danger)' ?>;font-size:1.1rem;"><i class="fas fa-<?= $c[1] ? 'circle-check' : 'circle-xmark' ?>"></i></span>
        <span><?= e($c[0]) ?></span>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
