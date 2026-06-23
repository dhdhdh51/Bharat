<?php
if (!defined('BASE_PATH')) { exit('Forbidden'); }
$pageTitle = 'Access Denied';
include __DIR__ . '/header.php';
?>
<div class="ad-empty">
  <div class="ad-empty__icon">&#128274;</div>
  <h2>Access Denied</h2>
  <p>Your role does not have permission to access this section.</p>
  <a href="<?= e(admin_url()) ?>" class="ad-btn ad-btn--primary">Back to Dashboard</a>
</div>
<?php include __DIR__ . '/footer.php'; ?>
