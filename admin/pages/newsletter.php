<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_permission('newsletter', 'view');
$self = admin_url('pages/newsletter.php');

if (input('export') === 'csv') {
    $rows = DB::all("SELECT * FROM newsletters ORDER BY created_at DESC");
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="subscribers-' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','Email','Status','Subscribed']);
    foreach ($rows as $r) { fputcsv($out, [$r['id'],$r['email'],$r['status'],$r['created_at']]); }
    fclose($out);
    exit;
}

if (is_post()) {
    admin_csrf_guard();
    if (input('action') === 'delete') {
        require_permission('newsletter', 'delete');
        DB::delete('newsletters', 'id = :id', ['id' => ad_id()]);
        flash_set('success', 'Subscriber removed.');
        redirect('admin/pages/newsletter.php');
    }
}

$page = max(1, (int) input('page', 1));
$perPage = 30;
$total = (int) DB::value("SELECT COUNT(*) FROM newsletters", [], 0);
$offset = ($page - 1) * $perPage;
$subs = DB::all("SELECT * FROM newsletters ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");

$pageTitle = 'Newsletter Subscribers';
include __DIR__ . '/../includes/header.php';
?>
<div class="ad-toolbar">
  <span class="ad-badge ad-badge--success"><?= $total ?> subscribers</span>
  <a href="<?= e($self . '?export=csv') ?>" class="ad-btn ad-btn--sm ad-spacer"><i class="fas fa-file-csv"></i> Export CSV</a>
</div>
<?php if (empty($subs)): ?>
  <div class="ad-card"><div class="ad-empty"><div class="ad-empty__icon">&#128231;</div><h2>No subscribers yet</h2></div></div>
<?php else: ?>
  <div class="ad-table-wrap"><table class="ad-table">
    <thead><tr><th>Email</th><th>Status</th><th>Subscribed</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($subs as $s): ?>
        <tr><td><?= e($s['email']) ?></td><td><?= ad_badge($s['status']) ?></td><td><?= e(fmt_date($s['created_at'])) ?></td>
        <td><?= ad_delete_form($self, (int) $s['id'], 'newsletter') ?></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
  <?= ad_pagination($total, $perPage, $page, $self) ?>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
