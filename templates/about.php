<?php
/** About Us */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }
$page = DB::row("SELECT * FROM pages WHERE slug='about'");
$team = DB::all("SELECT * FROM team_members WHERE status='active' ORDER BY sort_order, id");
$stats = DB::all("SELECT * FROM agency_stats ORDER BY sort_order, id");
SEO::set(['schema' => [SEO::breadcrumb(['Home' => '/', 'About' => '/about'])]], 'about');
include BASE_PATH . '/includes/header.php';
?>
<?= breadcrumb_html(['Home' => '/', 'About' => '/about']) ?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow">About Us</span>
    <h1>We Help Brands <span class="gradient-text">Win Online</span></h1>
    <p><?= e(setting('tagline', 'Grow Your Brand. Scale Your Reach.')) ?></p>
  </div>
</section>
<section class="section">
  <div class="container container-sm">
    <div class="prose reveal"><?= clean_html($page['content'] ?? '<p>We are a results-driven digital marketing agency.</p>') ?></div>
  </div>
</section>
<?php if (!empty($stats)): ?>
<section class="section--tight section"><div class="container"><div class="stats-grid reveal">
  <?php foreach ($stats as $st): $num=(float)preg_replace('/[^0-9.]/','',$st['value']); ?>
    <div class="stat"><div class="stat__value"><?php if($num>0):?><span data-count="<?= e($num) ?>"><?= e($num) ?></span><?php else:?><?= e($st['value']) ?><?php endif;?><?= e($st['suffix']) ?></div><div class="stat__label"><?= e($st['label']) ?></div></div>
  <?php endforeach; ?>
</div></div></section>
<?php endif; ?>
<?php if (!empty($team)): ?>
<section class="section" style="background:var(--bg-2);">
  <div class="container">
    <div class="section-head text-center reveal"><span class="eyebrow">Our Team</span><h2>Meet the People Behind the Growth</h2></div>
    <div class="cards-grid">
      <?php foreach ($team as $t): ?>
        <div class="card reveal text-center">
          <img src="<?= e(img($t['image'], 'team')) ?>" alt="<?= e($t['name']) ?>" class="avatar" style="width:96px;height:96px;margin:0 auto 14px;" loading="lazy">
          <h3 style="font-size:1.15rem;"><?= e($t['name']) ?></h3>
          <p class="muted" style="font-size:0.9rem;"><?= e($t['designation']) ?></p>
          <?php if ($t['bio']): ?><p style="font-size:0.88rem;margin-top:8px;"><?= e($t['bio']) ?></p><?php endif; ?>
          <div class="flex" style="justify-content:center;margin-top:12px;gap:10px;">
            <?php if ($t['social_linkedin']): ?><a href="<?= e($t['social_linkedin']) ?>" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fa-brands fa-linkedin" style="color:var(--primary-2);"></i></a><?php endif; ?>
            <?php if ($t['social_twitter']): ?><a href="<?= e($t['social_twitter']) ?>" target="_blank" rel="noopener" aria-label="Twitter"><i class="fa-brands fa-x-twitter" style="color:var(--primary-2);"></i></a><?php endif; ?>
            <?php if ($t['social_instagram']): ?><a href="<?= e($t['social_instagram']) ?>" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram" style="color:var(--primary-2);"></i></a><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<?php include BASE_PATH . '/templates/partials/cta-band.php'; ?>
<?php include BASE_PATH . '/includes/footer.php'; ?>
