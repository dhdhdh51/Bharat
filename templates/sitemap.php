<?php
/** HTML sitemap page */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }
$services = DB::all("SELECT title, slug FROM services WHERE status='published' ORDER BY sort_order, id");
$blogs = DB::all("SELECT title, slug FROM blogs WHERE status='published' ORDER BY published_at DESC");
$portfolio = DB::all("SELECT title, slug FROM portfolio WHERE status='published' ORDER BY sort_order, id");
$cases = DB::all("SELECT title, slug FROM case_studies WHERE status='published' ORDER BY sort_order, id");
SEO::set(['schema' => [SEO::breadcrumb(['Home' => '/', 'Sitemap' => '/sitemap'])]], 'sitemap');
include BASE_PATH . '/includes/header.php';
?>
<?= breadcrumb_html(['Home' => '/', 'Sitemap' => '/sitemap']) ?>
<section class="page-hero"><div class="container"><span class="eyebrow">Sitemap</span><h1>Explore the Site</h1></div></section>
<section class="section">
  <div class="container">
    <div class="cards-grid">
      <div class="card"><h3>Main Pages</h3><ul class="list-check" style="margin-top:12px;">
        <li><a href="<?= e(url('/')) ?>">Home</a></li>
        <li><a href="<?= e(url('/about')) ?>">About Us</a></li>
        <li><a href="<?= e(url('/services')) ?>">Services</a></li>
        <li><a href="<?= e(url('/portfolio')) ?>">Portfolio</a></li>
        <li><a href="<?= e(url('/case-studies')) ?>">Case Studies</a></li>
        <li><a href="<?= e(url('/pricing')) ?>">Pricing</a></li>
        <li><a href="<?= e(url('/blog')) ?>">Blog</a></li>
        <li><a href="<?= e(url('/industries')) ?>">Industries</a></li>
        <li><a href="<?= e(url('/free-audit')) ?>">Free Audit</a></li>
        <li><a href="<?= e(url('/contact')) ?>">Contact</a></li>
      </ul></div>
      <div class="card"><h3>Services</h3><ul class="list-check" style="margin-top:12px;">
        <?php foreach ($services as $s): ?><li><a href="<?= e(url('service/' . $s['slug'])) ?>"><?= e($s['title']) ?></a></li><?php endforeach; ?>
      </ul></div>
      <div class="card"><h3>Blog Posts</h3><ul class="list-check" style="margin-top:12px;">
        <?php if (empty($blogs)): ?><li class="muted">No posts yet</li><?php else: foreach ($blogs as $b): ?><li><a href="<?= e(url('blog/' . $b['slug'])) ?>"><?= e($b['title']) ?></a></li><?php endforeach; endif; ?>
      </ul></div>
      <div class="card"><h3>Portfolio &amp; Case Studies</h3><ul class="list-check" style="margin-top:12px;">
        <?php foreach ($portfolio as $p): ?><li><a href="<?= e(url('portfolio/' . $p['slug'])) ?>"><?= e($p['title']) ?></a></li><?php endforeach; ?>
        <?php foreach ($cases as $c): ?><li><a href="<?= e(url('case-studies/' . $c['slug'])) ?>"><?= e($c['title']) ?></a></li><?php endforeach; ?>
      </ul></div>
      <div class="card"><h3>Legal</h3><ul class="list-check" style="margin-top:12px;">
        <li><a href="<?= e(url('/privacy-policy')) ?>">Privacy Policy</a></li>
        <li><a href="<?= e(url('/terms-conditions')) ?>">Terms &amp; Conditions</a></li>
        <li><a href="<?= e(url('/disclaimer')) ?>">Disclaimer</a></li>
      </ul></div>
    </div>
    <p class="text-center muted" style="margin-top:30px;">Looking for the XML sitemap? <a href="<?= e(url('sitemap.xml')) ?>" style="color:var(--primary-2);">View sitemap.xml</a></p>
  </div>
</section>
<?php include BASE_PATH . '/includes/footer.php'; ?>
