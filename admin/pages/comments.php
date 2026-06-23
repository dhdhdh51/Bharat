<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_permission('comments', 'view');
$self = admin_url('pages/comments.php');

if (is_post()) {
    admin_csrf_guard();
    $action = input('action');
    $id = ad_id();
    if ($action === 'delete') {
        require_permission('comments', 'delete');
        DB::delete('blog_comments', 'id = :id', ['id' => $id]);
        flash_set('success', 'Comment deleted.');
        redirect('admin/pages/comments.php');
    }
    if ($action === 'set' && admin_can('comments', 'edit')) {
        $st = input('status');
        if (in_array($st, ['pending','approved','spam'], true)) {
            DB::update('blog_comments', ['status' => $st], 'id = :id', ['id' => $id]);
            flash_set('success', 'Comment ' . $st . '.');
        }
        redirect('admin/pages/comments.php' . (input('f') ? '?f=' . urlencode(input('f')) : ''));
    }
}

$filter = input('f', '');
$where = '1=1'; $params = [];
if (in_array($filter, ['pending','approved','spam'], true)) { $where .= ' AND c.status = ?'; $params[] = $filter; }
$comments = DB::all("SELECT c.*, b.title AS blog_title, b.slug AS blog_slug FROM blog_comments c LEFT JOIN blogs b ON b.id=c.blog_id WHERE $where ORDER BY c.created_at DESC LIMIT 100", $params);

$pageTitle = 'Comments';
include __DIR__ . '/../includes/header.php';
?>
<div class="ad-tabs">
  <a href="<?= e($self) ?>"<?= $filter === '' ? ' class="active"' : '' ?>>All</a>
  <a href="<?= e($self . '?f=pending') ?>"<?= $filter === 'pending' ? ' class="active"' : '' ?>>Pending</a>
  <a href="<?= e($self . '?f=approved') ?>"<?= $filter === 'approved' ? ' class="active"' : '' ?>>Approved</a>
  <a href="<?= e($self . '?f=spam') ?>"<?= $filter === 'spam' ? ' class="active"' : '' ?>>Spam</a>
</div>
<?php if (empty($comments)): ?>
  <div class="ad-card"><div class="ad-empty"><div class="ad-empty__icon">&#128172;</div><h2>No comments</h2></div></div>
<?php else: ?>
  <div class="ad-table-wrap"><table class="ad-table">
    <thead><tr><th>Author</th><th>Comment</th><th>Post</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($comments as $c): ?>
        <tr>
          <td><strong style="color:var(--ad-text);"><?= e($c['name']) ?></strong><br><small><?= e($c['email']) ?></small></td>
          <td><?= e(str_excerpt($c['comment'], 140)) ?></td>
          <td><?= $c['blog_title'] ? '<a href="' . e(url('blog/' . $c['blog_slug'])) . '" target="_blank">' . e(str_excerpt($c['blog_title'], 40)) . '</a>' : '—' ?></td>
          <td><?= ad_badge($c['status']) ?></td>
          <td><div class="actions">
            <?php if (admin_can('comments', 'edit')): ?>
              <?php if ($c['status'] !== 'approved'): ?><form method="post" action="<?= e($self) ?>" style="display:inline;"><?= csrf_field() ?><input type="hidden" name="action" value="set"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><input type="hidden" name="status" value="approved"><input type="hidden" name="f" value="<?= e($filter) ?>"><button class="ad-btn ad-btn--sm ad-btn--success" title="Approve"><i class="fas fa-check"></i></button></form><?php endif; ?>
              <?php if ($c['status'] !== 'spam'): ?><form method="post" action="<?= e($self) ?>" style="display:inline;"><?= csrf_field() ?><input type="hidden" name="action" value="set"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><input type="hidden" name="status" value="spam"><input type="hidden" name="f" value="<?= e($filter) ?>"><button class="ad-btn ad-btn--sm" title="Mark spam"><i class="fas fa-ban"></i></button></form><?php endif; ?>
            <?php endif; ?>
            <?= ad_delete_form($self, (int) $c['id'], 'comments') ?>
          </div></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
