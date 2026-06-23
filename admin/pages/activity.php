<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_admin();
if (!is_super_admin()) { http_response_code(403); include __DIR__ . '/../includes/denied.php'; exit; }

$page = max(1, (int) input('page', 1));
$perPage = 40;
$total = (int) DB::value("SELECT COUNT(*) FROM activity_logs", [], 0);
$offset = ($page - 1) * $perPage;
$logs = DB::all("SELECT al.*, a.name AS admin_name FROM activity_logs al LEFT JOIN admins a ON a.id=al.admin_id ORDER BY al.created_at DESC LIMIT $perPage OFFSET $offset");

$pageTitle = 'Activity Log';
include __DIR__ . '/../includes/header.php';
?>
<?php if (empty($logs)): ?>
  <div class="ad-card"><div class="ad-empty"><div class="ad-empty__icon">&#128339;</div><h2>No activity logged</h2></div></div>
<?php else: ?>
  <div class="ad-table-wrap"><table class="ad-table">
    <thead><tr><th>User</th><th>Action</th><th>Details</th><th>IP</th><th>When</th></tr></thead>
    <tbody>
      <?php foreach ($logs as $l): ?>
        <tr><td><?= e($l['admin_name'] ?: 'System') ?></td><td><?= e(str_replace('_',' ',$l['action'])) ?></td><td><?= e($l['details']) ?></td><td><?= e($l['ip_address']) ?></td><td><?= e(fmt_date($l['created_at'], 'M j, H:i')) ?></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
  <?= ad_pagination($total, $perPage, $page, admin_url('pages/activity.php')) ?>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
