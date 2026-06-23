<?php
/** Free website / marketing audit */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }
SEO::set(['schema' => [SEO::breadcrumb(['Home' => '/', 'Free Audit' => '/free-audit'])]], 'free-audit');
include BASE_PATH . '/includes/header.php';
?>
<?= breadcrumb_html(['Home' => '/', 'Free Audit' => '/free-audit']) ?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow">100% Free</span>
    <h1>Get Your Free <span class="gradient-text">Digital Marketing Audit</span></h1>
    <p>We'll analyze your website, SEO, social presence and ads — then send a clear roadmap to grow.</p>
  </div>
</section>
<section class="section">
  <div class="container">
    <div class="split" style="align-items:flex-start;">
      <div class="reveal">
        <h2>What You'll Get</h2>
        <ul class="list-check" style="margin-top:20px;">
          <li>Website performance &amp; SEO health check</li>
          <li>Social media presence review</li>
          <li>Paid ads &amp; funnel opportunities</li>
          <li>Competitor benchmarking</li>
          <li>Actionable 90-day growth roadmap</li>
        </ul>
        <div class="card" style="margin-top:24px;">
          <div class="stars" style="color:var(--gold);">★★★★★</div>
          <p class="review" style="margin-top:8px;">&ldquo;The free audit alone gave us more clarity than our previous agency did in months.&rdquo;</p>
          <div class="person" style="margin-top:10px;"><span class="avatar"></span><div><div class="person__name">Aman Verma</div><div class="person__role">CEO, TechNova</div></div></div>
        </div>
      </div>
      <div class="reveal">
        <div class="card">
          <h3 style="margin-bottom:16px;">Request Your Free Audit</h3>
          <?php $leadFormSource = 'free_audit'; include BASE_PATH . '/templates/partials/lead-form.php'; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php include BASE_PATH . '/includes/footer.php'; ?>
