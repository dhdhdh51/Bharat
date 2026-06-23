<?php
/** Home page */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }

$faqs        = DB::all("SELECT * FROM faqs WHERE status='published' ORDER BY sort_order, id LIMIT 8");
$services    = DB::all("SELECT * FROM services WHERE status='published' ORDER BY is_featured DESC, sort_order, id LIMIT 8");
$stats       = DB::all("SELECT * FROM agency_stats ORDER BY sort_order, id");
$logos       = DB::all("SELECT * FROM client_logos ORDER BY sort_order, id");
$portfolio   = DB::all("SELECT * FROM portfolio WHERE status='published' ORDER BY sort_order, id LIMIT 6");
$testimonials= DB::all("SELECT * FROM testimonials WHERE status='published' ORDER BY sort_order, id LIMIT 6");
$packages    = DB::all("SELECT * FROM pricing_packages WHERE status='published' ORDER BY sort_order, id LIMIT 4");
$caseStudies = DB::all("SELECT * FROM case_studies WHERE status='published' ORDER BY sort_order, id LIMIT 2");
$portfolioCats = array_values(array_unique(array_filter(array_map(fn($p) => $p['category'], $portfolio))));

SEO::set([
    'type'   => 'website',
    'schema' => [SEO::faqSchema($faqs)],
], 'home');

$base = rtrim(SITE_URL, '/') . '/';
include BASE_PATH . '/includes/header.php';
?>

<!-- C. HERO -->
<section class="hero">
  <div class="hero__bg" aria-hidden="true">
    <span class="hero__glow hero__glow--1"></span>
    <span class="hero__glow hero__glow--2"></span>
  </div>
  <div class="container">
    <div class="hero__inner">
      <div class="hero__content reveal">
        <span class="eyebrow"><i class="fas fa-bolt" aria-hidden="true"></i> <?= e(setting('tagline', 'Grow Your Brand. Scale Your Reach.')) ?></span>
        <h1 class="hero__title">We Turn Attention Into <span class="gradient-text">Leads, Sales &amp; Brand Growth.</span></h1>
        <p class="hero__text">Bharat SEO is a full-service digital marketing agency delivering social media marketing, SEO, paid advertising and high-converting websites that grow your business online.</p>
        <div class="hero__cta">
          <a href="<?= e(url('/free-audit')) ?>" class="btn btn--primary btn--lg">Get Free Strategy Call <i class="fas fa-arrow-right-long" aria-hidden="true"></i></a>
          <a href="<?= e(url('/portfolio')) ?>" class="btn btn--ghost btn--lg">View Our Work</a>
        </div>
        <div class="hero__badges">
          <div class="hero__badge"><i class="fas fa-chart-line" aria-hidden="true"></i> Data-Driven Strategy</div>
          <div class="hero__badge"><i class="fas fa-wand-magic-sparkles" aria-hidden="true"></i> Creative Campaigns</div>
          <div class="hero__badge"><i class="fas fa-bullseye" aria-hidden="true"></i> ROI-Focused Growth</div>
        </div>
      </div>
      <div class="hero__visual reveal">
        <canvas id="hero-three-canvas" data-base="<?= e($base) ?>" aria-hidden="true"></canvas>
        <div class="hero__fallback" id="heroFallback" style="display:none;">
          <div style="text-align:center;">
            <i class="fas fa-globe" style="font-size:3.4rem;background:var(--grad-text);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;" aria-hidden="true"></i>
            <p style="margin-top:10px;font-weight:600;color:var(--text);">Connected Growth Network</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- D. TRUSTED BY -->
<?php if (!empty($logos)): ?>
<section class="section--tight section">
  <div class="container">
    <p class="text-center muted" style="margin-bottom:22px;">Trusted by ambitious brands across India</p>
    <div class="logos reveal">
      <?php foreach ($logos as $l): ?>
        <?php if (!empty($l['logo'])): ?>
          <div class="logo-chip"><img src="<?= e(upload_url($l['logo'])) ?>" alt="<?= e($l['name']) ?>" loading="lazy" style="max-height:34px;"></div>
        <?php else: ?>
          <div class="logo-chip"><?= e($l['name']) ?></div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- E. STATS -->
<?php if (!empty($stats)): ?>
<section class="section--tight section">
  <div class="container">
    <div class="stats-grid reveal">
      <?php foreach ($stats as $st):
        $num = (float) preg_replace('/[^0-9.]/', '', $st['value']);
        $hasNum = is_numeric($st['value']) || preg_match('/^[0-9.]+$/', preg_replace('/[^0-9.]/', '', $st['value'])); ?>
        <div class="stat">
          <div class="stat__value">
            <?php if ($num > 0): ?><span data-count="<?= e($num) ?>"><?= e($num) ?></span><?php else: ?><?= e($st['value']) ?><?php endif; ?><?= e($st['suffix']) ?>
          </div>
          <div class="stat__label"><?= e($st['label']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- F. SERVICES GRID -->
<section class="section" id="services">
  <div class="container">
    <div class="section-head text-center reveal">
      <span class="eyebrow">What We Do</span>
      <h2>Full-Funnel Digital Marketing Services</h2>
      <p class="lead">From strategy to execution, we cover every channel that grows your brand and pipeline.</p>
    </div>
    <?php if (empty($services)): ?>
      <?= empty_state('Our services will appear here soon.') ?>
    <?php else: ?>
    <div class="cards-grid">
      <?php foreach ($services as $s): ?>
        <a class="card service-card reveal" href="<?= e(url('service/' . $s['slug'])) ?>">
          <span class="card__shine"></span>
          <div class="icon-badge"><i class="fas fa-<?= e($s['icon'] ?: 'star') ?>" aria-hidden="true"></i></div>
          <h3><?= e($s['title']) ?></h3>
          <p><?= e(str_excerpt($s['short_description'] ?: $s['overview'], 110)) ?></p>
          <span class="link-arrow">Learn more <i class="fas fa-arrow-right-long" aria-hidden="true"></i></span>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="text-center" style="margin-top:36px;">
      <a href="<?= e(url('/services')) ?>" class="btn btn--ghost">View All Services</a>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- G. WHY CHOOSE -->
<section class="section" style="background:var(--bg-2);">
  <div class="container">
    <div class="section-head text-center reveal">
      <span class="eyebrow">Why Bharat SEO</span>
      <h2>Built Different. Driven by Results.</h2>
    </div>
    <div class="cards-grid">
      <?php
      $whys = [
        ['lightbulb', 'Strategy First', 'Every campaign starts with research and a clear plan tied to your business goals.'],
        ['chart-pie', 'Transparent Reporting', 'Real dashboards and honest metrics — no vanity numbers, ever.'],
        ['palette', 'Creative Content', 'Scroll-stopping creatives that build brand and drive action.'],
        ['gauge-high', 'Performance Marketing', 'We optimize relentlessly for ROI, ROAS and qualified leads.'],
        ['users-gear', 'Dedicated Team', 'A senior team that knows your brand inside out.'],
        ['trophy', 'Measurable Results', 'We are obsessed with outcomes you can see in your bottom line.'],
      ];
      foreach ($whys as $w): ?>
        <div class="card reveal">
          <div class="icon-badge"><i class="fas fa-<?= $w[0] ?>" aria-hidden="true"></i></div>
          <h3><?= e($w[1]) ?></h3>
          <p><?= e($w[2]) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- H. PROCESS -->
<section class="section">
  <div class="container">
    <div class="section-head text-center reveal">
      <span class="eyebrow">How We Work</span>
      <h2>Our Proven Growth Process</h2>
    </div>
    <div class="process-grid">
      <?php
      $steps = ['Discover', 'Strategy', 'Create', 'Launch', 'Optimize', 'Scale'];
      foreach ($steps as $i => $step): ?>
        <div class="process-step reveal">
          <div class="process-step__num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></div>
          <h4><?= e($step) ?></h4>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- I. PORTFOLIO PREVIEW -->
<?php if (!empty($portfolio)): ?>
<section class="section" style="background:var(--bg-2);">
  <div class="container">
    <div class="section-head text-center reveal">
      <span class="eyebrow">Our Work</span>
      <h2>Results We're Proud Of</h2>
    </div>
    <div data-filter-group data-filter-target="#homePortfolioGrid" class="filters reveal">
      <button class="filter-btn active" data-filter="all">All</button>
      <?php foreach ($portfolioCats as $c): ?>
        <button class="filter-btn" data-filter="<?= e($c) ?>"><?= e($c) ?></button>
      <?php endforeach; ?>
    </div>
    <div class="cards-grid" id="homePortfolioGrid">
      <?php foreach ($portfolio as $p): ?>
        <article class="portfolio-card reveal" data-cat="<?= e($p['category']) ?>">
          <div class="portfolio-card__img">
            <img src="<?= e(img($p['featured_image'], 'portfolio')) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
          </div>
          <div class="portfolio-card__body">
            <span class="tag"><?= e($p['category'] ?: 'Project') ?></span>
            <h3><?= e($p['title']) ?></h3>
            <?php if (!empty($p['result_metric'])): ?><div class="result"><i class="fas fa-arrow-trend-up" aria-hidden="true"></i> <?= e($p['result_metric']) ?></div><?php endif; ?>
            <div style="margin-top:14px;"><a href="<?= e(url('portfolio/' . $p['slug'])) ?>" class="link-arrow">View Project <i class="fas fa-arrow-right-long" aria-hidden="true"></i></a></div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <div class="text-center" style="margin-top:36px;"><a href="<?= e(url('/portfolio')) ?>" class="btn btn--ghost">View Full Portfolio</a></div>
  </div>
</section>
<?php endif; ?>

<!-- J. CASE STUDY RESULTS -->
<?php if (!empty($caseStudies)): ?>
<section class="section">
  <div class="container">
    <div class="section-head text-center reveal">
      <span class="eyebrow">Case Studies</span>
      <h2>Real Growth, Real Numbers</h2>
    </div>
    <div class="cards-grid">
      <?php foreach ($caseStudies as $cs): ?>
        <article class="card reveal">
          <span class="tag" style="color:var(--cyan);font-weight:700;text-transform:uppercase;font-size:0.76rem;"><?= e($cs['industry'] ?: 'Case Study') ?></span>
          <h3 style="margin:8px 0 14px;"><?= e($cs['title']) ?></h3>
          <div class="metric-row">
            <?php if ($cs['reach_growth']): ?><div class="metric"><div class="v"><?= e($cs['reach_growth']) ?></div><div class="l">Reach Growth</div></div><?php endif; ?>
            <?php if ($cs['leads_growth']): ?><div class="metric"><div class="v"><?= e($cs['leads_growth']) ?></div><div class="l">Leads Growth</div></div><?php endif; ?>
            <?php if ($cs['roi_metric']): ?><div class="metric"><div class="v"><?= e($cs['roi_metric']) ?></div><div class="l">ROI / ROAS</div></div><?php endif; ?>
          </div>
          <div style="margin-top:18px;"><a href="<?= e(url('case-studies/' . $cs['slug'])) ?>" class="link-arrow">Read Case Study <i class="fas fa-arrow-right-long" aria-hidden="true"></i></a></div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- K. TESTIMONIALS -->
<?php if (!empty($testimonials)): ?>
<section class="section" style="background:var(--bg-2);">
  <div class="container">
    <div class="section-head text-center reveal">
      <span class="eyebrow">Testimonials</span>
      <h2>Loved by Our Clients</h2>
    </div>
    <div class="cards-grid">
      <?php foreach ($testimonials as $t): ?>
        <article class="card testimonial-card reveal">
          <div class="stars" aria-label="<?= (int) $t['rating'] ?> out of 5"><?= str_repeat('★', max(1, min(5, (int) $t['rating']))) ?></div>
          <p class="review">&ldquo;<?= e($t['review']) ?>&rdquo;</p>
          <div class="person">
            <?php if (!empty($t['image'])): ?><img class="avatar" src="<?= e(upload_url($t['image'])) ?>" alt="<?= e($t['client_name']) ?>" loading="lazy"><?php else: ?><span class="avatar" aria-hidden="true"></span><?php endif; ?>
            <div>
              <div class="person__name"><?= e($t['client_name']) ?></div>
              <div class="person__role"><?= e(trim(($t['designation'] ?? '') . ($t['company'] ? ', ' . $t['company'] : ''), ', ')) ?></div>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- L. PACKAGES PREVIEW -->
<?php if (!empty($packages)): ?>
<section class="section">
  <div class="container">
    <div class="section-head text-center reveal">
      <span class="eyebrow">Packages</span>
      <h2>Plans That Scale With You</h2>
    </div>
    <div class="pricing-grid">
      <?php foreach ($packages as $pk): $feat = pipe_list($pk['features']); ?>
        <div class="card price-card reveal<?= $pk['is_popular'] ? ' popular' : '' ?>">
          <?php if ($pk['is_popular']): ?><span class="badge-popular">Most Popular</span><?php endif; ?>
          <h3><?= e($pk['name']) ?></h3>
          <div class="price"><?= e($pk['currency'] === 'INR' && is_numeric(str_replace(',', '', $pk['price'])) ? '₹' : '') ?><?= e($pk['price']) ?> <small><?= e($pk['duration']) ?></small></div>
          <p style="font-size:0.9rem;"><?= e($pk['description']) ?></p>
          <ul class="price-features">
            <?php foreach (array_slice($feat, 0, 5) as $f): ?><li><?= e($f) ?></li><?php endforeach; ?>
          </ul>
          <a href="<?= e(url('/pricing')) ?>" class="btn btn--primary btn--block"><?= e($pk['cta_text'] ?: 'Get Started') ?></a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- M. FAQ -->
<?php if (!empty($faqs)): ?>
<section class="section" style="background:var(--bg-2);">
  <div class="container">
    <div class="section-head text-center reveal">
      <span class="eyebrow">FAQ</span>
      <h2>Questions, Answered</h2>
    </div>
    <div class="faq reveal">
      <?php foreach ($faqs as $f): ?>
        <div class="faq-item">
          <button class="faq-q" aria-expanded="false"><span><?= e($f['question']) ?></span><i class="fas fa-plus ico" aria-hidden="true"></i></button>
          <div class="faq-a"><p><?= e($f['answer']) ?></p></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- N. FREE AUDIT LEAD FORM -->
<section class="section" id="free-audit">
  <div class="container container-sm">
    <div class="section-head text-center reveal">
      <span class="eyebrow">Free Audit</span>
      <h2>Get Your Free Digital Marketing Audit</h2>
      <p class="lead">Tell us about your business and we'll send a personalized growth roadmap — no cost, no obligation.</p>
    </div>
    <div class="card reveal">
      <?php $leadFormSource = 'free_audit'; include BASE_PATH . '/templates/partials/lead-form.php'; ?>
    </div>
  </div>
</section>

<!-- O. FINAL CTA -->
<?php include BASE_PATH . '/templates/partials/cta-band.php'; ?>

<?php include BASE_PATH . '/includes/footer.php'; ?>
