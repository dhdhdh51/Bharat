<?php
/** Industries We Serve */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }
SEO::set(['schema' => [SEO::breadcrumb(['Home' => '/', 'Industries' => '/industries'])]], 'industries');
$industries = [
  ['cart-shopping', 'E-commerce', 'Scale online stores with performance ads and conversion-focused funnels.'],
  ['heart-pulse', 'Healthcare', 'Attract patients with local SEO, reputation management and trust-building content.'],
  ['building', 'Real Estate', 'Generate qualified property leads with targeted social and search campaigns.'],
  ['graduation-cap', 'Education', 'Fill seats and programs with enrollment-driven marketing.'],
  ['laptop-code', 'SaaS & Tech', 'Drive demos and signups with full-funnel B2B marketing.'],
  ['dumbbell', 'Fitness & Wellness', 'Grow memberships with community-first social media.'],
  ['utensils', 'Hospitality', 'Boost bookings and footfall with local and visual marketing.'],
  ['briefcase', 'Professional Services', 'Build authority and a steady pipeline of high-value clients.'],
];
include BASE_PATH . '/includes/header.php';
?>
<?= breadcrumb_html(['Home' => '/', 'Industries' => '/industries']) ?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow">Industries</span>
    <h1>Marketing Built for <span class="gradient-text">Your Industry</span></h1>
    <p>We tailor strategies to the unique dynamics of every sector we serve.</p>
  </div>
</section>
<section class="section">
  <div class="container">
    <div class="cards-grid">
      <?php foreach ($industries as $i): ?>
        <div class="card reveal"><div class="icon-badge"><i class="fas fa-<?= $i[0] ?>"></i></div><h3><?= e($i[1]) ?></h3><p><?= e($i[2]) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php include BASE_PATH . '/templates/partials/cta-band.php'; ?>
<?php include BASE_PATH . '/includes/footer.php'; ?>
