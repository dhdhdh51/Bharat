<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_permission('seo', 'view');
$self = admin_url('pages/seo.php');

if (is_post()) {
    admin_csrf_guard();
    require_permission('seo', 'edit');
    $action = input('action');
    if ($action === 'delete') {
        DB::delete('page_seo', 'id = :id', ['id' => ad_id()]);
        flash_set('success', 'SEO entry deleted.');
        redirect('admin/pages/seo.php');
    }
    $id = ad_id();
    $pageKey = clean_text(input('page_key'), 190);
    if ($pageKey === '') {
        flash_set('error', 'Page key is required.');
    } else {
        $data = [
            'page_key' => $pageKey,
            'meta_title' => clean_text(input('meta_title'), 255),
            'meta_description' => clean_text(input('meta_description'), 500),
            'meta_keywords' => clean_text(input('meta_keywords'), 500),
            'canonical_url' => clean_text(input('canonical_url'), 255),
            'og_image' => ad_handle_upload('og_image', 'settings', (string) input('og_image_existing', '')),
            'noindex' => input('noindex') ? 1 : 0,
        ];
        if ($id) {
            DB::update('page_seo', $data, 'id = :id', ['id' => $id]);
        } else {
            // upsert by page_key
            $existing = DB::value('SELECT id FROM page_seo WHERE page_key = ?', [$pageKey]);
            if ($existing) DB::update('page_seo', $data, 'id = :id', ['id' => $existing]);
            else DB::insert('page_seo', $data);
        }
        flash_set('success', 'SEO settings saved.');
    }
    redirect('admin/pages/seo.php');
}

$action = input('action');
if ($action === 'new' || $action === 'edit') {
    $row = $action === 'edit' ? DB::row('SELECT * FROM page_seo WHERE id = ?', [ad_id()]) : null;
    $pageTitle = ($row ? 'Edit' : 'Add') . ' Page SEO';
    include __DIR__ . '/../includes/header.php';
    ?>
    <a href="<?= e($self) ?>" class="ad-btn ad-btn--sm ad-btn--ghost" style="margin-bottom:16px;"><i class="fas fa-arrow-left"></i> Back</a>
    <div class="ad-card"><form method="post" action="<?= e($self) ?>" enctype="multipart/form-data">
      <?= csrf_field() ?><input type="hidden" name="action" value="save"><?php if ($row): ?><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><?php endif; ?>
      <div class="ad-form-grid">
        <div class="ad-field"><label>Page Key <span class="req">*</span></label><input type="text" name="page_key" value="<?= e($row['page_key'] ?? '') ?>" placeholder="home, about, service:slug, blog:slug" required><small>Use the route key, e.g. "home", "about", "service:seo".</small></div>
        <div class="ad-field"><label>Meta Title</label><input type="text" name="meta_title" value="<?= e($row['meta_title'] ?? '') ?>"></div>
        <div class="ad-field full"><label>Meta Description</label><textarea name="meta_description"><?= e($row['meta_description'] ?? '') ?></textarea></div>
        <div class="ad-field"><label>Meta Keywords</label><input type="text" name="meta_keywords" value="<?= e($row['meta_keywords'] ?? '') ?>"></div>
        <div class="ad-field"><label>Canonical URL</label><input type="text" name="canonical_url" value="<?= e($row['canonical_url'] ?? '') ?>"></div>
        <div class="ad-field full"><label>OG Image</label><?php if (!empty($row['og_image'])): ?><img src="<?= e(upload_url($row['og_image'])) ?>" class="ad-thumb" style="width:120px;height:auto;margin-bottom:8px;"><?php endif; ?><input type="hidden" name="og_image_existing" value="<?= e($row['og_image'] ?? '') ?>"><input type="file" name="og_image" accept="image/*"></div>
        <div class="ad-field"><label class="ad-checkbox"><input type="checkbox" name="noindex" value="1"<?= !empty($row['noindex']) ? ' checked' : '' ?>> No-index this page</label></div>
      </div>
      <div style="margin-top:18px;"><button class="ad-btn ad-btn--primary">Save SEO</button></div>
    </form></div>
    <?php
    include __DIR__ . '/../includes/footer.php';
    return;
}

$rows = DB::all("SELECT * FROM page_seo ORDER BY page_key");
$pageTitle = 'Page SEO';
include __DIR__ . '/../includes/header.php';
?>
<div class="ad-toolbar">
  <a href="<?= e($self . '?action=new') ?>" class="ad-btn ad-btn--primary"><i class="fas fa-plus"></i> Add Page SEO</a>
  <a href="<?= e(admin_url('pages/seo-tools.php')) ?>" class="ad-btn ad-btn--sm ad-spacer"><i class="fas fa-list-check"></i> SEO Tools</a>
</div>
<?php if (empty($rows)): ?>
  <div class="ad-card"><div class="ad-empty"><div class="ad-empty__icon">&#128269;</div><h2>No custom SEO entries</h2><p>Add per-page SEO overrides here.</p></div></div>
<?php else: ?>
  <div class="ad-table-wrap"><table class="ad-table">
    <thead><tr><th>Page Key</th><th>Meta Title</th><th>No-index</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr><td><code><?= e($r['page_key']) ?></code></td><td><?= e(str_excerpt($r['meta_title'], 60)) ?></td><td><?= $r['noindex'] ? '<span class="ad-badge ad-badge--warning">Yes</span>' : 'No' ?></td>
        <td><div class="actions"><a href="<?= e($self . '?action=edit&id=' . (int)$r['id']) ?>" class="ad-btn ad-btn--sm"><i class="fas fa-pen"></i></a><?= ad_delete_form($self, (int)$r['id'], 'seo') ?></div></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
