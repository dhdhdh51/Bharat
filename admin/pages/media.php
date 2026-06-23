<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_permission('media', 'view');
$self = admin_url('pages/media.php');

if (is_post()) {
    admin_csrf_guard();
    $action = input('action');
    if ($action === 'upload' && admin_can('media', 'create')) {
        if (!empty($_FILES['file']) && ($_FILES['file']['error'] ?? 1) === UPLOAD_ERR_OK) {
            $res = upload_file($_FILES['file'], 'media');
            if (!empty($res['ok'])) {
                DB::insert('media_library', [
                    'file_name' => $res['name'], 'file_path' => $res['path'],
                    'file_type' => $res['mime'] ?? null, 'file_size' => $res['size'] ?? null,
                    'alt_text' => clean_text(input('alt_text'), 255),
                    'uploaded_by' => (int) $_SESSION['admin_id'],
                ]);
                flash_set('success', 'File uploaded.');
            } else {
                flash_set('error', $res['error'] ?? 'Upload failed.');
            }
        } else {
            flash_set('error', 'Please choose a file.');
        }
        redirect('admin/pages/media.php');
    }
    if ($action === 'delete' && admin_can('media', 'delete')) {
        $id = ad_id();
        $m = DB::row('SELECT * FROM media_library WHERE id = ?', [$id]);
        if ($m) {
            $path = UPLOAD_DIR . '/' . $m['file_path'];
            if (is_file($path)) @unlink($path);
            DB::delete('media_library', 'id = :id', ['id' => $id]);
            flash_set('success', 'File deleted.');
        }
        redirect('admin/pages/media.php');
    }
    if ($action === 'alt' && admin_can('media', 'edit')) {
        DB::update('media_library', ['alt_text' => clean_text(input('alt_text'), 255)], 'id = :id', ['id' => ad_id()]);
        flash_set('success', 'Alt text updated.');
        redirect('admin/pages/media.php');
    }
}

$page = max(1, (int) input('page', 1));
$perPage = 24;
$total = (int) DB::value("SELECT COUNT(*) FROM media_library", [], 0);
$offset = ($page - 1) * $perPage;
$items = DB::all("SELECT * FROM media_library ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");

$pageTitle = 'Media Library';
include __DIR__ . '/../includes/header.php';
?>
<?php if (admin_can('media', 'create')): ?>
<div class="ad-card" style="margin-bottom:18px;">
  <div class="ad-card__head"><h3>Upload File</h3></div>
  <form method="post" action="<?= e($self) ?>" enctype="multipart/form-data" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
    <?= csrf_field() ?><input type="hidden" name="action" value="upload">
    <div class="ad-field" style="margin:0;"><label>File</label><input type="file" name="file" accept="image/*" required></div>
    <div class="ad-field" style="margin:0;flex:1;min-width:180px;"><label>Alt Text</label><input type="text" name="alt_text" placeholder="Describe the image"></div>
    <button class="ad-btn ad-btn--primary"><i class="fas fa-upload"></i> Upload</button>
  </form>
</div>
<?php endif; ?>

<?php if (empty($items)): ?>
  <div class="ad-card"><div class="ad-empty"><div class="ad-empty__icon">&#128247;</div><h2>No media yet</h2><p>Upload images to reuse across the site.</p></div></div>
<?php else: ?>
  <div class="ad-grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr));">
    <?php foreach ($items as $m): ?>
      <div class="ad-card" style="padding:10px;">
        <img src="<?= e(upload_url($m['file_path'])) ?>" alt="<?= e($m['alt_text']) ?>" style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:8px;margin-bottom:8px;">
        <input type="text" value="<?= e($m['file_path']) ?>" readonly onclick="this.select()" style="width:100%;font-size:0.72rem;padding:5px;border-radius:6px;border:1px solid var(--ad-border);background:var(--ad-bg);color:var(--ad-muted);margin-bottom:6px;">
        <form method="post" action="<?= e($self) ?>" style="display:flex;gap:4px;margin-bottom:6px;"><?= csrf_field() ?><input type="hidden" name="action" value="alt"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><input type="text" name="alt_text" value="<?= e($m['alt_text']) ?>" placeholder="Alt text" style="flex:1;font-size:0.72rem;padding:5px;border-radius:6px;border:1px solid var(--ad-border);background:var(--ad-bg);color:var(--ad-text);"><button class="ad-btn ad-btn--sm" title="Save alt"><i class="fas fa-save"></i></button></form>
        <?php if (admin_can('media','delete')): ?><?= ad_delete_form($self, (int)$m['id'], 'media') ?><?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
  <?= ad_pagination($total, $perPage, $page, $self) ?>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
