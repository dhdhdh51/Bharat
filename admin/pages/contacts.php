<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_permission('contacts', 'view');
$self = admin_url('pages/contacts.php');

if (is_post()) {
    admin_csrf_guard();
    $action = input('action');
    $id = ad_id();
    if ($action === 'delete') {
        require_permission('contacts', 'delete');
        DB::delete('contact_messages', 'id = :id', ['id' => $id]);
        flash_set('success', 'Message deleted.');
        redirect('admin/pages/contacts.php');
    }
    if ($action === 'mark' && admin_can('contacts', 'edit')) {
        $st = input('status');
        if (in_array($st, ['unread','read','replied'], true)) {
            DB::update('contact_messages', ['status' => $st], 'id = :id', ['id' => $id]);
        }
        redirect('admin/pages/contacts.php');
    }
}

$page = max(1, (int) input('page', 1));
$perPage = 20;
$total = (int) DB::value("SELECT COUNT(*) FROM contact_messages", [], 0);
$offset = ($page - 1) * $perPage;
$msgs = DB::all("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");

$pageTitle = 'Contact Messages';
include __DIR__ . '/../includes/header.php';
?>
<?php if (empty($msgs)): ?>
  <div class="ad-card"><div class="ad-empty"><div class="ad-empty__icon">&#9993;</div><h2>No messages</h2><p>Contact form submissions will appear here.</p></div></div>
<?php else: ?>
  <div class="ad-table-wrap"><table class="ad-table">
    <thead><tr><th>From</th><th>Subject</th><th>Message</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($msgs as $m): ?>
        <tr>
          <td><strong style="color:var(--ad-text);"><?= e($m['name']) ?></strong><br><small><?= e($m['email']) ?></small><?= $m['phone'] ? '<br><small>' . e($m['phone']) . '</small>' : '' ?></td>
          <td><?= e($m['subject'] ?: '—') ?></td>
          <td><?= e(str_excerpt($m['message'], 120)) ?></td>
          <td><?= ad_badge($m['status']) ?></td>
          <td><?= e(fmt_date($m['created_at'], 'M j')) ?></td>
          <td><div class="actions">
            <?php if (admin_can('contacts', 'edit')): ?>
            <form method="post" action="<?= e($self) ?>" style="display:inline;"><?= csrf_field() ?><input type="hidden" name="action" value="mark"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>"><input type="hidden" name="status" value="<?= $m['status'] === 'replied' ? 'unread' : 'replied' ?>"><button class="ad-btn ad-btn--sm ad-btn--success" title="Toggle replied"><i class="fas fa-check"></i></button></form>
            <?php endif; ?>
            <a href="mailto:<?= e($m['email']) ?>" class="ad-btn ad-btn--sm"><i class="fas fa-reply"></i></a>
            <?= ad_delete_form($self, (int) $m['id'], 'contacts') ?>
          </div></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
  <?= ad_pagination($total, $perPage, $page, $self) ?>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
