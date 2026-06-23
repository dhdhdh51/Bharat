<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_permission('leads', 'view');

$statuses = ['new','contacted','follow_up','qualified','won','lost'];
$self = admin_url('pages/leads.php');

// ---- CSV export (before any output) ----
if (input('export') === 'csv' && admin_can('leads', 'view')) {
    $rows = DB::all("SELECT * FROM leads ORDER BY created_at DESC");
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="leads-' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','Name','Business','Phone','Email','Website','City','Service','Budget','Source','Status','Message','Date']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['id'],$r['full_name'],$r['business_name'],$r['phone'],$r['email'],$r['website_url'],$r['city'],$r['service_needed'],$r['budget'],$r['source'],$r['status'],$r['message'],$r['created_at']]);
    }
    fclose($out);
    exit;
}

// ---- POST actions ----
if (is_post()) {
    admin_csrf_guard();
    $action = input('action');
    $id = ad_id();

    if ($action === 'delete') {
        require_permission('leads', 'delete');
        DB::delete('leads', 'id = :id', ['id' => $id]);
        flash_set('success', 'Lead deleted.');
        redirect('admin/pages/leads.php');
    }
    if ($action === 'update_status' && admin_can('leads', 'edit')) {
        $st = input('status');
        if (in_array($st, $statuses, true)) {
            DB::update('leads', ['status' => $st], 'id = :id', ['id' => $id]);
            flash_set('success', 'Status updated.');
        }
        redirect('admin/pages/leads.php?action=view&id=' . $id);
    }
    if ($action === 'assign' && admin_can('leads', 'edit')) {
        $assignTo = (int) input('assigned_to', 0) ?: null;
        DB::update('leads', ['assigned_to' => $assignTo], 'id = :id', ['id' => $id]);
        flash_set('success', 'Lead assigned.');
        redirect('admin/pages/leads.php?action=view&id=' . $id);
    }
    if ($action === 'add_note' && admin_can('leads', 'edit')) {
        $note = clean_text(input('note'), 2000);
        if ($note !== '') {
            DB::insert('lead_notes', ['lead_id' => $id, 'admin_id' => (int) $_SESSION['admin_id'], 'note' => $note]);
            flash_set('success', 'Note added.');
        }
        redirect('admin/pages/leads.php?action=view&id=' . $id);
    }
}

$action = input('action');

// ---- Detail view ----
if ($action === 'view') {
    $lead = DB::row("SELECT l.*, a.name AS assigned_name FROM leads l LEFT JOIN admins a ON a.id=l.assigned_to WHERE l.id = ?", [ad_id()]);
    if (!$lead) { flash_set('error', 'Lead not found.'); redirect('admin/pages/leads.php'); }
    $notes = DB::all("SELECT n.*, a.name AS admin_name FROM lead_notes n LEFT JOIN admins a ON a.id=n.admin_id WHERE n.lead_id = ? ORDER BY n.created_at DESC", [$lead['id']]);
    $admins = DB::all("SELECT id, name FROM admins WHERE status='active' ORDER BY name");
    $pageTitle = 'Lead: ' . $lead['full_name'];
    include __DIR__ . '/../includes/header.php';
    ?>
    <a href="<?= e($self) ?>" class="ad-btn ad-btn--sm ad-btn--ghost" style="margin-bottom:16px;"><i class="fas fa-arrow-left"></i> Back to Leads</a>
    <div class="ad-grid" style="grid-template-columns:repeat(auto-fit,minmax(min(100%,320px),1fr));">
      <div class="ad-card">
        <div class="ad-card__head"><h2><?= e($lead['full_name']) ?></h2><?= ad_badge($lead['status']) ?></div>
        <div class="ad-table-wrap"><table class="ad-table">
          <tbody>
            <tr><th>Business</th><td><?= e($lead['business_name'] ?: '—') ?></td></tr>
            <tr><th>Phone</th><td><?= $lead['phone'] ? '<a href="tel:' . e($lead['phone']) . '">' . e($lead['phone']) . '</a>' : '—' ?></td></tr>
            <tr><th>Email</th><td><?= $lead['email'] ? '<a href="mailto:' . e($lead['email']) . '">' . e($lead['email']) . '</a>' : '—' ?></td></tr>
            <tr><th>Website</th><td><?= e($lead['website_url'] ?: '—') ?></td></tr>
            <tr><th>City</th><td><?= e($lead['city'] ?: '—') ?></td></tr>
            <tr><th>Service</th><td><?= e($lead['service_needed'] ?: '—') ?></td></tr>
            <tr><th>Budget</th><td><?= e($lead['budget'] ?: '—') ?></td></tr>
            <tr><th>Source</th><td><?= e($lead['source']) ?></td></tr>
            <tr><th>Message</th><td><?= nl2br(e($lead['message'] ?: '—')) ?></td></tr>
            <tr><th>Received</th><td><?= e(fmt_date($lead['created_at'], 'M j, Y H:i')) ?></td></tr>
          </tbody>
        </table></div>
        <?php if ($lead['phone'] || $lead['email']): ?>
        <div style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;">
          <?php if ($lead['phone']): ?><a href="<?= e('https://wa.me/' . preg_replace('/\D+/', '', $lead['phone'])) ?>" target="_blank" class="ad-btn ad-btn--sm ad-btn--success"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a><?php endif; ?>
          <?php if ($lead['email']): ?><a href="mailto:<?= e($lead['email']) ?>" class="ad-btn ad-btn--sm"><i class="fas fa-envelope"></i> Email</a><?php endif; ?>
        </div>
        <?php endif; ?>
      </div>

      <div style="display:flex;flex-direction:column;gap:18px;">
        <?php if (admin_can('leads', 'edit')): ?>
        <div class="ad-card">
          <div class="ad-card__head"><h3>Update Status</h3></div>
          <form method="post" action="<?= e($self) ?>" style="display:flex;gap:8px;">
            <?= csrf_field() ?><input type="hidden" name="action" value="update_status"><input type="hidden" name="id" value="<?= (int) $lead['id'] ?>">
            <select name="status" style="flex:1;padding:9px;border-radius:10px;border:1px solid var(--ad-border);background:var(--ad-bg);color:var(--ad-text);">
              <?php foreach ($statuses as $s): ?><option value="<?= e($s) ?>"<?= $lead['status'] === $s ? ' selected' : '' ?>><?= e(ucwords(str_replace('_',' ',$s))) ?></option><?php endforeach; ?>
            </select>
            <button class="ad-btn ad-btn--primary">Save</button>
          </form>
        </div>
        <div class="ad-card">
          <div class="ad-card__head"><h3>Assign To</h3></div>
          <form method="post" action="<?= e($self) ?>" style="display:flex;gap:8px;">
            <?= csrf_field() ?><input type="hidden" name="action" value="assign"><input type="hidden" name="id" value="<?= (int) $lead['id'] ?>">
            <select name="assigned_to" style="flex:1;padding:9px;border-radius:10px;border:1px solid var(--ad-border);background:var(--ad-bg);color:var(--ad-text);">
              <option value="">— Unassigned —</option>
              <?php foreach ($admins as $a): ?><option value="<?= (int) $a['id'] ?>"<?= (int) $lead['assigned_to'] === (int) $a['id'] ? ' selected' : '' ?>><?= e($a['name']) ?></option><?php endforeach; ?>
            </select>
            <button class="ad-btn ad-btn--primary">Assign</button>
          </form>
        </div>
        <div class="ad-card">
          <div class="ad-card__head"><h3>Internal Notes</h3></div>
          <form method="post" action="<?= e($self) ?>" style="margin-bottom:14px;">
            <?= csrf_field() ?><input type="hidden" name="action" value="add_note"><input type="hidden" name="id" value="<?= (int) $lead['id'] ?>">
            <div class="ad-field"><textarea name="note" placeholder="Add a note..." style="min-height:70px;"></textarea></div>
            <button class="ad-btn ad-btn--primary ad-btn--sm">Add Note</button>
          </form>
          <?php if (empty($notes)): ?><p style="color:var(--ad-muted);">No notes yet.</p><?php else: ?>
            <ul style="display:flex;flex-direction:column;gap:10px;">
              <?php foreach ($notes as $n): ?>
                <li style="padding:10px;border-radius:10px;background:var(--ad-surface-2);font-size:0.85rem;"><?= nl2br(e($n['note'])) ?><div style="color:var(--ad-dim);margin-top:4px;font-size:0.75rem;"><?= e($n['admin_name'] ?: 'Admin') ?> · <?= e(fmt_date($n['created_at'], 'M j, H:i')) ?></div></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php
    include __DIR__ . '/../includes/footer.php';
    return;
}

// ---- List with filters ----
$fStatus = input('status', '');
$fSource = input('source', '');
$fSearch = trim((string) input('q', ''));
$page = max(1, (int) input('page', 1));
$perPage = 20;

$where = '1=1'; $params = [];
if (in_array($fStatus, $statuses, true)) { $where .= ' AND status = ?'; $params[] = $fStatus; }
if ($fSource !== '') { $where .= ' AND source = ?'; $params[] = $fSource; }
if ($fSearch !== '') { $where .= ' AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR business_name LIKE ?)'; $like = '%' . $fSearch . '%'; array_push($params, $like, $like, $like, $like); }

$total = (int) DB::value("SELECT COUNT(*) FROM leads WHERE $where", $params, 0);
$offset = ($page - 1) * $perPage;
$leads = DB::all("SELECT * FROM leads WHERE $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset", $params);
$sources = DB::all("SELECT DISTINCT source FROM leads ORDER BY source");

$pageTitle = 'Leads';
include __DIR__ . '/../includes/header.php';
?>
<div class="ad-toolbar">
  <form method="get" action="<?= e($self) ?>">
    <input type="search" name="q" value="<?= e($fSearch) ?>" placeholder="Search name, email, phone...">
    <select name="status"><option value="">All statuses</option><?php foreach ($statuses as $s): ?><option value="<?= e($s) ?>"<?= $fStatus === $s ? ' selected' : '' ?>><?= e(ucwords(str_replace('_',' ',$s))) ?></option><?php endforeach; ?></select>
    <select name="source"><option value="">All sources</option><?php foreach ($sources as $s): ?><option value="<?= e($s['source']) ?>"<?= $fSource === $s['source'] ? ' selected' : '' ?>><?= e($s['source']) ?></option><?php endforeach; ?></select>
    <button class="ad-btn ad-btn--sm" type="submit"><i class="fas fa-filter"></i> Filter</button>
  </form>
  <a href="<?= e($self . '?export=csv') ?>" class="ad-btn ad-btn--sm ad-spacer"><i class="fas fa-file-csv"></i> Export CSV</a>
</div>
<?php if (empty($leads)): ?>
  <div class="ad-card"><div class="ad-empty"><div class="ad-empty__icon">&#128229;</div><h2>No leads found</h2><p>Leads from your website forms will appear here.</p></div></div>
<?php else: ?>
  <div class="ad-table-wrap"><table class="ad-table">
    <thead><tr><th>Name</th><th>Contact</th><th>Service</th><th>Source</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($leads as $l): ?>
        <tr>
          <td><strong style="color:var(--ad-text);"><?= e($l['full_name']) ?></strong><?= $l['business_name'] ? '<br><small>' . e($l['business_name']) . '</small>' : '' ?></td>
          <td><?= e($l['email'] ?: '') ?><?= $l['phone'] ? '<br><small>' . e($l['phone']) . '</small>' : '' ?></td>
          <td><?= e($l['service_needed'] ?: '—') ?></td>
          <td><?= e($l['source']) ?></td>
          <td><?= ad_badge($l['status']) ?></td>
          <td><?= e(fmt_date($l['created_at'], 'M j')) ?></td>
          <td><div class="actions">
            <a href="<?= e($self . '?action=view&id=' . (int) $l['id']) ?>" class="ad-btn ad-btn--sm"><i class="fas fa-eye"></i></a>
            <?= ad_delete_form($self, (int) $l['id'], 'leads') ?>
          </div></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
  <?= ad_pagination($total, $perPage, $page, $self . '?q=' . urlencode($fSearch) . '&status=' . urlencode($fStatus) . '&source=' . urlencode($fSource)) ?>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
