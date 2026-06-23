<?php
/** Service detail (dynamic template for all service pages) */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }

$slug = $GLOBALS['route_slug'] ?? '';
$service = DB::row("SELECT * FROM services WHERE slug = ? AND status='published'", [$slug]);

if (!$service) {
    http_response_code(404);
    include BASE_PATH . '/templates/404.php';
    return;
}

$benefits     = pipe_list($service['benefits']);
$deliverables = pipe_list($service['deliverables']);
$process      = pipe_list($service['process']);
$related      = DB::all("SELECT title, slug, icon, short_description FROM services WHERE id != ? AND status='published' ORDER BY RAND() LIMIT 3", [$service['id']]);
$faqs         = DB::all("SELECT * FROM faqs WHERE status='published' ORDER BY sort_order, id LIMIT 5");
$testimonials = DB::all("SELECT * FROM testimonials WHERE status='published' ORDER BY sort_order, id LIMIT 3");
$relatedBlogs = DB::all("SELECT title, slug FROM blogs WHERE status='published' ORDER BY published_at DESC LIMIT 3");

$serviceUrl = url('service/' . $service['slug']);
$schema = [
    '@context' => 'https://schema.org',
    '@type'    => 'Service',
    'name'     => $service['title'],
    'description' => str_excerpt($service['overview'] ?: $service['short_description'], 200),
    'provider' => ['@type' => 'Organization', 'name' => SITE_NAME, 'url' => rtrim(SITE_URL, '/')],
    'areaServed' => 'IN',
    'url'      => $serviceUrl,
];
$schemaBlocks = [
    $schema,
    SEO::breadcrumb(['Home' => '/', 'Services' => '/services', $service['title'] => 'service/' . $service['slug']]),
];
if (!empty($faqs)) {
    $schemaBlocks[] = SEO::faqSchema($faqs);
}

SEO::set([
    'title'       => $service['meta_title'] ?: ($service['title'] . ' | ' . SITE_NAME),
    'description' => $service['meta_description'] ?: str_excerpt($service['overview'] ?: $service['short_description'], 160),
    'image'       => $service['banner'] ? upload_url($service['banner']) : '',
    'type'        => 'website',
    'schema'      => $schemaBlocks,
], 'service:' . $service['slug']);

include BASE_PATH . '/includes/header.php';
?>
<?= breadcrumb_html(['Home' => '/', 'Services' => '/services', $service['title'] => 'service/' . $service['slug']]) ?>

<section class="page-hero">
  <div class="container">
    <span class="eyebrow"><i class="fas fa-<?= e($service['icon'] ?: 'star') ?>" aria-hidden="true"></i> Service</span>
    <h1><?= e($service['title']) ?></h1>
    <p><?= e($service['short_description'] ?: str_excerpt($service['overview'], 160)) ?></p>
    <div class="flex" style="justify-content:center;margin-top:22px;">
      <a href="<?= e(url('/free-audit')) ?>" class="btn btn--primary btn--lg">Get Free Consultation</a>
      <?php if (whatsapp_link() !== '#'): ?><a href="<?= e(whatsapp_link('Hi, I am interested in ' . $service['title'])) ?>" target="_blank" rel="noopener" class="btn btn--ghost btn--lg"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp</a><?php endif; ?>
    </div>
  </div>
</section>

<!-- Overview -->
<section class="section">
  <div class="container">
    <div class="split">
      <div class="reveal">
        <span class="eyebrow">Overview</span>
        <h2>What We Deliver</h2>
        <p style="margin-top:14px;"><?= e($service['overview'] ?: $service['short_description']) ?></p>
        <?php if (!empty($benefits)): ?>
        <ul class="list-check" style="margin-top:22px;">
          <?php foreach ($benefits as $b): ?><li><?= e($b) ?></li><?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
      <div class="reveal">
        <img src="<?= e(img($service['image'] ?: $service['banner'], 'service')) ?>" alt="<?= e($service['title']) ?>" style="border-radius:var(--radius);width:100%;object-fit:cover;" loading="lazy">
      </div>
    </div>
  </div>
</section>

<!-- Deliverables + Process -->
<section class="section" style="background:var(--bg-2);">
  <div class="container">
    <?php if (!empty($deliverables)): ?>
    <div class="section-head text-center reveal"><span class="eyebrow">Deliverables</span><h2>What's Included</h2></div>
    <div class="cards-grid" style="margin-bottom:48px;">
      <?php foreach ($deliverables as $d): ?>
        <div class="card reveal"><div class="icon-badge"><i class="fas fa-check" aria-hidden="true"></i></div><h3 style="font-size:1.1rem;"><?= e($d) ?></h3></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($process)): ?>
    <div class="section-head text-center reveal"><span class="eyebrow">Process</span><h2>How It Works</h2></div>
    <div class="process-grid">
      <?php foreach ($process as $i => $p): ?>
        <div class="process-step reveal"><div class="process-step__num"><?= str_pad((string)($i+1),2,'0',STR_PAD_LEFT) ?></div><h4><?= e($p) ?></h4></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- Testimonials -->
<?php if (!empty($testimonials)): ?>
<section class="section">
  <div class="container">
    <div class="section-head text-center reveal"><span class="eyebrow">Testimonials</span><h2>Clients Who Trust Us</h2></div>
    <div class="cards-grid">
      <?php foreach ($testimonials as $t): ?>
        <article class="card testimonial-card reveal">
          <div class="stars"><?= str_repeat('★', max(1,min(5,(int)$t['rating']))) ?></div>
          <p class="review">&ldquo;<?= e($t['review']) ?>&rdquo;</p>
          <div class="person"><span class="avatar"></span><div><div class="person__name"><?= e($t['client_name']) ?></div><div class="person__role"><?= e($t['company']) ?></div></div></div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- FAQ -->
<?php if (!empty($faqs)): ?>
<section class="section" style="background:var(--bg-2);">
  <div class="container">
    <div class="section-head text-center reveal"><span class="eyebrow">FAQ</span><h2>Common Questions</h2></div>
    <div class="faq reveal">
      <?php foreach ($faqs as $f): ?>
        <div class="faq-item"><button class="faq-q" aria-expanded="false"><span><?= e($f['question']) ?></span><i class="fas fa-plus ico"></i></button><div class="faq-a"><p><?= e($f['answer']) ?></p></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Related services + blogs -->
<?php if (!empty($related)): ?>
<section class="section">
  <div class="container">
    <div class="section-head text-center reveal"><span class="eyebrow">Related Services</span><h2>You Might Also Need</h2></div>
    <div class="cards-grid">
      <?php foreach ($related as $r): ?>
        <a class="card service-card reveal" href="<?= e(url('service/' . $r['slug'])) ?>">
          <div class="icon-badge"><i class="fas fa-<?= e($r['icon'] ?: 'star') ?>"></i></div>
          <h3><?= e($r['title']) ?></h3>
          <p><?= e(str_excerpt($r['short_description'], 100)) ?></p>
          <span class="link-arrow">Explore <i class="fas fa-arrow-right-long"></i></span>
        </a>
      <?php endforeach; ?>
    </div>
    <?php if (!empty($relatedBlogs)): ?>
    <div class="text-center" style="margin-top:32px;">
      <p class="muted">Read more: 
        <?php foreach ($relatedBlogs as $i => $rb): ?><a href="<?= e(url('blog/' . $rb['slug'])) ?>" style="color:var(--primary-2);"><?= e($rb['title']) ?></a><?= $i < count($relatedBlogs)-1 ? ' · ' : '' ?><?php endforeach; ?>
      </p>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php include BASE_PATH . '/templates/partials/cta-band.php'; ?>
<?php include BASE_PATH . '/includes/footer.php'; ?>
