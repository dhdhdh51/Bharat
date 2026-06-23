<?php
/**
 * Reusable Free Audit / Lead form.
 * Optional: $leadFormTitle, $leadFormSource (default 'free_audit'), $leadFormService (preselect).
 */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }
$lfSource = $leadFormSource ?? 'free_audit';
$lfService = $leadFormService ?? '';
$servicesForSelect = DB::all("SELECT title FROM services WHERE status='published' ORDER BY sort_order, id");
?>
<form class="lead-form" data-ajax action="<?= e(url('api/lead.php')) ?>" method="post" novalidate>
  <?= csrf_field() ?>
  <?= honeypot_field() ?>
  <input type="hidden" name="source" value="<?= e($lfSource) ?>">
  <div class="form-message" role="status" aria-live="polite"></div>
  <div class="form-grid">
    <div class="field">
      <label>Full Name <span class="req">*</span></label>
      <input type="text" name="full_name" required autocomplete="name">
      <span class="error-msg"></span>
    </div>
    <div class="field">
      <label>Business Name</label>
      <input type="text" name="business_name" autocomplete="organization">
      <span class="error-msg"></span>
    </div>
    <div class="field">
      <label>Phone Number <span class="req">*</span></label>
      <input type="tel" name="phone" required autocomplete="tel">
      <span class="error-msg"></span>
    </div>
    <div class="field">
      <label>Email <span class="req">*</span></label>
      <input type="email" name="email" required autocomplete="email">
      <span class="error-msg"></span>
    </div>
    <div class="field">
      <label>Website URL</label>
      <input type="url" name="website_url" placeholder="https://">
      <span class="error-msg"></span>
    </div>
    <div class="field">
      <label>City</label>
      <input type="text" name="city" autocomplete="address-level2">
      <span class="error-msg"></span>
    </div>
    <div class="field">
      <label>Service Needed</label>
      <select name="service_needed">
        <option value="">Select a service</option>
        <?php foreach ($servicesForSelect as $s): ?>
          <option value="<?= e($s['title']) ?>"<?= $lfService === $s['title'] ? ' selected' : '' ?>><?= e($s['title']) ?></option>
        <?php endforeach; ?>
        <option value="Other">Other</option>
      </select>
      <span class="error-msg"></span>
    </div>
    <div class="field">
      <label>Monthly Marketing Budget</label>
      <select name="budget">
        <option value="">Select budget</option>
        <option>Under &#8377;25,000</option>
        <option>&#8377;25,000 - &#8377;50,000</option>
        <option>&#8377;50,000 - &#8377;1,00,000</option>
        <option>&#8377;1,00,000+</option>
      </select>
      <span class="error-msg"></span>
    </div>
    <div class="field full">
      <label>Message</label>
      <textarea name="message" placeholder="Tell us about your goals..."></textarea>
      <span class="error-msg"></span>
    </div>
    <div class="field full">
      <label class="checkbox">
        <input type="checkbox" name="consent" value="1" required>
        <span>I agree to be contacted by <?= e(setting('site_name', SITE_NAME)) ?> regarding my inquiry. <span class="req">*</span></span>
      </label>
      <span class="error-msg"></span>
    </div>
    <div class="field full">
      <button type="submit" class="btn btn--primary btn--lg btn--block">Get My Free Audit <i class="fas fa-arrow-right-long" aria-hidden="true"></i></button>
      <p class="form-note">100% free, no obligation. We respect your privacy.</p>
    </div>
  </div>
</form>
