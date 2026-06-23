<?php
/** Generic CMS page (legal pages, etc.) from pages table */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }
$slug = $GLOBALS['route_slug'] ?? '';
$page = DB::row("SELECT * FROM pages WHERE slug = ? AND status='published'", [$slug]);
if (!$page) { http_response_code(404); include BASE_PATH . '/templates/404.php'; return; }

SEO::set([
    'title'       => $page['title'] . ' | ' . SITE_NAME,
    'description' => str_excerpt($page['content'], 160),
    'schema'      => [SEO::breadcrumb(['Home' => '/', $page['title'] => '/' . $page['slug']])],
], $page['slug']);
include BASE_PATH . '/includes/header.php';
?>
<?= breadcrumb_html(['Home' => '/', $page['title'] => '/' . $page['slug']]) ?>
<section class="page-hero"><div class="container"><h1><?= e($page['title']) ?></h1></div></section>
<section class="section">
  <div class="container container-sm">
    <div class="prose reveal"><?= clean_html($page['content'] ?: '<p>Content coming soon.</p>') ?></div>
    <p class="muted" style="margin-top:30px;font-size:0.85rem;">Last updated: <?= e(fmt_date($page['updated_at'])) ?></p>
  </div>
</section>
<?php include BASE_PATH . '/includes/footer.php'; ?>
