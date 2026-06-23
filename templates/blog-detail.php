<?php
/** Blog detail with TOC, comments, sharing, related */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }

$slug = $GLOBALS['route_slug'] ?? '';
$post = DB::row("SELECT b.*, c.name AS cat_name, c.slug AS cat_slug FROM blogs b LEFT JOIN blog_categories c ON c.id=b.category_id WHERE b.slug = ? AND b.status='published'", [$slug]);
if (!$post) { http_response_code(404); include BASE_PATH . '/templates/404.php'; return; }

// Increment views (overall + per-day), guarded by session to avoid double count
if (empty($_SESSION['viewed_blog_' . $post['id']])) {
    DB::run('UPDATE blogs SET views = views + 1 WHERE id = ?', [$post['id']]);
    DB::run('INSERT INTO blog_views (blog_id, view_date, views) VALUES (?, CURDATE(), 1) ON DUPLICATE KEY UPDATE views = views + 1', [$post['id']]);
    $_SESSION['viewed_blog_' . $post['id']] = true;
}

$tags = array_filter(array_map('trim', explode(',', (string) $post['tags'])));
$related = DB::all("SELECT title, slug, featured_image FROM blogs WHERE id != ? AND status='published' AND (category_id = ? OR ? IS NULL) ORDER BY published_at DESC LIMIT 3", [$post['id'], $post['category_id'], $post['category_id']]);
$comments = DB::all("SELECT * FROM blog_comments WHERE blog_id = ? AND status='approved' ORDER BY created_at DESC", [$post['id']]);

$schema = [
    '@context' => 'https://schema.org',
    '@type'    => 'Article',
    'headline' => $post['title'],
    'description' => str_excerpt($post['excerpt'] ?: $post['content'], 160),
    'image'    => $post['featured_image'] ? upload_url($post['featured_image']) : SEO::get('image'),
    'author'   => ['@type' => 'Person', 'name' => $post['author']],
    'publisher'=> ['@type' => 'Organization', 'name' => SITE_NAME],
    'datePublished' => $post['published_at'] ?: $post['created_at'],
    'dateModified'  => $post['updated_at'],
    'mainEntityOfPage' => url('blog/' . $post['slug']),
];

SEO::set([
    'title'       => $post['meta_title'] ?: ($post['title'] . ' | ' . SITE_NAME),
    'description' => $post['meta_description'] ?: str_excerpt($post['excerpt'] ?: $post['content'], 160),
    'image'       => $post['featured_image'] ? upload_url($post['featured_image']) : '',
    'type'        => 'article',
    'schema'      => [$schema, SEO::breadcrumb(['Home' => '/', 'Blog' => '/blog', $post['title'] => 'blog/' . $post['slug']])],
], 'blog:' . $post['slug']);

$shareUrl = rawurlencode(url('blog/' . $post['slug']));
$shareText = rawurlencode($post['title']);
include BASE_PATH . '/includes/header.php';
?>
<?= breadcrumb_html(['Home' => '/', 'Blog' => '/blog', $post['title'] => 'blog/' . $post['slug']]) ?>
<article>
<section class="page-hero">
  <div class="container container-sm">
    <?php if ($post['cat_name']): ?><a href="<?= e(url('/blog?cat=' . urlencode($post['cat_slug']))) ?>" class="chip"><?= e($post['cat_name']) ?></a><?php endif; ?>
    <h1 style="margin-top:14px;"><?= e($post['title']) ?></h1>
    <p class="muted" style="margin-top:14px;"><i class="far fa-user"></i> <?= e($post['author']) ?> &nbsp;·&nbsp; <i class="far fa-calendar"></i> <?= e(fmt_date($post['published_at'] ?: $post['created_at'])) ?> &nbsp;·&nbsp; <i class="far fa-clock"></i> <?= (int) $post['reading_time'] ?> min read</p>
  </div>
</section>

<section class="section">
  <div class="container container-sm">
    <img src="<?= e(img($post['featured_image'], 'blog')) ?>" alt="<?= e($post['title']) ?>" style="border-radius:var(--radius);width:100%;max-height:460px;object-fit:cover;margin-bottom:32px;">
    <div class="prose"><?= clean_html($post['content']) ?></div>

    <?php if (!empty($tags)): ?>
    <div class="flex" style="margin-top:28px;gap:8px;">
      <?php foreach ($tags as $t): ?><span class="chip"><?= e($t) ?></span><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="flex" style="justify-content:center;margin-top:28px;gap:8px;">
      <span class="muted" style="align-self:center;">Share:</span>
      <a class="btn btn--ghost btn--sm" target="_blank" rel="noopener" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $shareUrl ?>"><i class="fa-brands fa-linkedin"></i></a>
      <a class="btn btn--ghost btn--sm" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&text=<?= $shareText ?>"><i class="fa-brands fa-x-twitter"></i></a>
      <a class="btn btn--ghost btn--sm" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>"><i class="fa-brands fa-facebook"></i></a>
      <a class="btn btn--ghost btn--sm" target="_blank" rel="noopener" href="<?= e(whatsapp_link($post['title'] . ' ' . url('blog/' . $post['slug']))) ?>"><i class="fa-brands fa-whatsapp"></i></a>
    </div>

    <!-- Author box -->
    <div class="card" style="margin-top:36px;display:flex;gap:16px;align-items:center;flex-wrap:wrap;">
      <span class="avatar" style="width:60px;height:60px;"></span>
      <div><div class="person__name" style="font-size:1.1rem;"><?= e($post['author']) ?></div><p style="font-size:0.9rem;margin-top:4px;">Part of the <?= e(SITE_NAME) ?> team, sharing strategies that drive real growth.</p></div>
    </div>
  </div>
</section>
</article>

<!-- Comments -->
<section class="section" style="background:var(--bg-2);">
  <div class="container container-sm">
    <h2 style="margin-bottom:20px;">Comments (<?= count($comments) ?>)</h2>
    <?php if (empty($comments)): ?>
      <p class="muted">Be the first to comment.</p>
    <?php else: ?>
      <div style="display:flex;flex-direction:column;gap:16px;">
        <?php foreach ($comments as $cm): ?>
          <div class="card">
            <div class="person" style="margin-bottom:8px;"><span class="avatar"></span><div><div class="person__name"><?= e($cm['name']) ?></div><div class="person__role"><?= e(fmt_date($cm['created_at'])) ?></div></div></div>
            <p><?= nl2br(e($cm['comment'])) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="card" style="margin-top:28px;">
      <h3 style="margin-bottom:16px;">Leave a Comment</h3>
      <form data-ajax action="<?= e(url('api/comment.php')) ?>" method="post">
        <?= csrf_field() ?>
        <?= honeypot_field() ?>
        <input type="hidden" name="blog_id" value="<?= (int) $post['id'] ?>">
        <div class="form-message" role="status" aria-live="polite"></div>
        <div class="form-grid">
          <div class="field"><label>Name <span class="req">*</span></label><input type="text" name="name" required><span class="error-msg"></span></div>
          <div class="field"><label>Email <span class="req">*</span></label><input type="email" name="email" required><span class="error-msg"></span></div>
          <div class="field full"><label>Comment <span class="req">*</span></label><textarea name="comment" required></textarea><span class="error-msg"></span></div>
          <div class="field full"><button type="submit" class="btn btn--primary">Post Comment</button><p class="form-note">Comments are reviewed before publishing.</p></div>
        </div>
      </form>
    </div>
  </div>
</section>

<?php if (!empty($related)): ?>
<section class="section">
  <div class="container">
    <div class="section-head text-center reveal"><h2>Related Articles</h2></div>
    <div class="cards-grid">
      <?php foreach ($related as $r): ?>
        <article class="card blog-card reveal">
          <a href="<?= e(url('blog/' . $r['slug'])) ?>" class="blog-card__img"><img src="<?= e(img($r['featured_image'], 'blog')) ?>" alt="<?= e($r['title']) ?>" loading="lazy"></a>
          <div class="blog-card__body"><h3><a href="<?= e(url('blog/' . $r['slug'])) ?>"><?= e($r['title']) ?></a></h3></div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include BASE_PATH . '/includes/footer.php'; ?>
