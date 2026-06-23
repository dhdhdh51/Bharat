<?php
/** Blog listing with search, category filter, pagination */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }

$perPage = 9;
$page = max(1, (int) input('page', 1));
$offset = ($page - 1) * $perPage;
$q = trim((string) input('q', ''));
$catSlug = trim((string) input('cat', ''));

$categories = DB::all("SELECT * FROM blog_categories ORDER BY name");
$where = ["b.status='published'", "(b.published_at IS NULL OR b.published_at <= NOW())"];
$params = [];
if ($q !== '') {
    $where[] = "(b.title LIKE ? OR b.excerpt LIKE ? OR b.content LIKE ?)";
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like);
}
if ($catSlug !== '') {
    $where[] = "c.slug = ?";
    $params[] = $catSlug;
}
$whereSql = implode(' AND ', $where);

$total = (int) DB::value("SELECT COUNT(*) FROM blogs b LEFT JOIN blog_categories c ON c.id=b.category_id WHERE $whereSql", $params, 0);
$posts = DB::all("SELECT b.*, c.name AS cat_name, c.slug AS cat_slug FROM blogs b LEFT JOIN blog_categories c ON c.id=b.category_id WHERE $whereSql ORDER BY COALESCE(b.published_at, b.created_at) DESC LIMIT $perPage OFFSET $offset", $params);

SEO::set(['schema' => [SEO::breadcrumb(['Home' => '/', 'Blog' => '/blog'])]], 'blog');
include BASE_PATH . '/includes/header.php';
?>
<?= breadcrumb_html(['Home' => '/', 'Blog' => '/blog']) ?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow">Blog</span>
    <h1>Insights to <span class="gradient-text">Grow Faster</span></h1>
    <p>Actionable SEO, social media and paid advertising strategies from our team.</p>
  </div>
</section>
<section class="section">
  <div class="container">
    <div class="flex" style="justify-content:space-between;align-items:center;margin-bottom:28px;gap:16px;">
      <div class="filters" style="margin:0;justify-content:flex-start;">
        <a href="<?= e(url('/blog')) ?>" class="filter-btn<?= $catSlug === '' ? ' active' : '' ?>">All</a>
        <?php foreach ($categories as $c): ?>
          <a href="<?= e(url('/blog?cat=' . urlencode($c['slug']))) ?>" class="filter-btn<?= $catSlug === $c['slug'] ? ' active' : '' ?>"><?= e($c['name']) ?></a>
        <?php endforeach; ?>
      </div>
      <form action="<?= e(url('/blog')) ?>" method="get" class="flex" style="gap:8px;flex-wrap:nowrap;">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search articles..." aria-label="Search blog" style="padding:10px 14px;border-radius:100px;border:1px solid var(--border-strong);background:var(--bg-2);color:var(--text);">
        <button class="btn btn--primary btn--sm" type="submit"><i class="fas fa-magnifying-glass"></i></button>
      </form>
    </div>

    <?php if (empty($posts)): ?>
      <?= empty_state($q !== '' ? 'No articles found for "' . e($q) . '".' : 'No articles published yet. Check back soon!') ?>
    <?php else: ?>
    <div class="cards-grid">
      <?php foreach ($posts as $b): ?>
        <article class="card blog-card reveal">
          <a href="<?= e(url('blog/' . $b['slug'])) ?>" class="blog-card__img"><img src="<?= e(img($b['featured_image'], 'blog')) ?>" alt="<?= e($b['title']) ?>" loading="lazy"></a>
          <div class="blog-card__body">
            <div class="meta">
              <?php if ($b['cat_name']): ?><span class="chip"><?= e($b['cat_name']) ?></span><?php endif; ?>
              <span><i class="far fa-clock"></i> <?= (int) $b['reading_time'] ?> min</span>
            </div>
            <h3><a href="<?= e(url('blog/' . $b['slug'])) ?>"><?= e($b['title']) ?></a></h3>
            <p style="font-size:0.92rem;"><?= e(str_excerpt($b['excerpt'] ?: $b['content'], 120)) ?></p>
            <div class="meta" style="margin-top:auto;"><span><?= e($b['author']) ?></span><span><?= e(fmt_date($b['published_at'] ?: $b['created_at'])) ?></span></div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <?php
      $baseUrl = url('/blog') . ($catSlug !== '' ? '?cat=' . urlencode($catSlug) : ($q !== '' ? '?q=' . urlencode($q) : ''));
      echo paginate($total, $perPage, $page, $baseUrl);
    ?>
    <?php endif; ?>
  </div>
</section>
<?php include BASE_PATH . '/includes/footer.php'; ?>
