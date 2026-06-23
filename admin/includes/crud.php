<?php
/**
 * Generic CRUD engine for admin modules.
 * Drives list + create/edit form + save + delete for simple entities.
 *
 * Config keys:
 *  - table (string)         DB table
 *  - module (string)        permission module
 *  - title (string)         plural title
 *  - singular (string)      singular label
 *  - self (string)          this page filename (e.g. 'faqs.php')
 *  - order_by (string)      default 'sort_order, id'
 *  - search (array)         columns to LIKE-search
 *  - slug_from (string)     field name to build slug from (table must have slug)
 *  - per_page (int)         default 20
 *  - columns (array)        list columns: ['key'=>'Label'] or ['key'=>['label'=>..,'render'=>fn($row)]]
 *  - fields (array)         form fields, each:
 *      ['name','label','type'=>text|textarea|richtext|email|number|int|select|checkbox|image|color,
 *       'required'=>bool,'options'=>[val=>label] or fn(),'subdir'=>'media','max'=>int,'help'=>'','default'=>'','col'=>'full']
 */
if (!defined('BASE_PATH')) { exit('Forbidden'); }

function crud_run(array $cfg): void
{
    $module   = $cfg['module'];
    $table    = $cfg['table'];
    $self      = $cfg['self'];
    $singular = $cfg['singular'] ?? 'Item';
    $title    = $cfg['title'] ?? $singular . 's';
    $orderBy  = $cfg['order_by'] ?? 'id DESC';
    $perPage  = $cfg['per_page'] ?? 20;
    $fields   = $cfg['fields'] ?? [];
    $listUrl  = admin_url('pages/' . $self);

    require_permission($module, 'view');

    // ---- Handle POST (save / delete) ----
    if (is_post()) {
        admin_csrf_guard();
        $postAction = input('action');

        if ($postAction === 'delete') {
            require_permission($module, 'delete');
            $id = ad_id();
            if ($id > 0) {
                DB::delete($table, 'id = :id', ['id' => $id]);
                activity_log((int)($_SESSION['admin_id'] ?? 0), 'delete_' . $module, $singular . ' #' . $id);
                flash_set('success', $singular . ' deleted.');
            }
            redirect('admin/pages/' . $self);
        }

        // Save (create or update)
        $id = ad_id();
        require_permission($module, $id ? 'edit' : 'create');

        $data = [];
        $errors = [];
        foreach ($fields as $f) {
            $name = $f['name'];
            $type = $f['type'] ?? 'text';
            switch ($type) {
                case 'image':
                    $data[$name] = ad_handle_upload($name, $f['subdir'] ?? 'media', (string) input($name . '_existing', ''));
                    break;
                case 'checkbox':
                    $data[$name] = input($name) ? 1 : 0;
                    break;
                case 'richtext':
                    $data[$name] = clean_html($_POST[$name] ?? '');
                    break;
                case 'int':
                case 'number':
                    $data[$name] = (int) input($name, $f['default'] ?? 0);
                    break;
                default:
                    $data[$name] = clean_text((string) input($name), $f['max'] ?? 5000);
            }
            if (!empty($f['required']) && trim((string) $data[$name]) === '') {
                $errors[$name] = $f['label'] . ' is required.';
            }
            if ($type === 'email' && $data[$name] !== '' && !valid_email($data[$name])) {
                $errors[$name] = 'Enter a valid email.';
            }
        }

        if (!empty($cfg['slug_from'])) {
            $slugSource = input('slug') !== '' ? input('slug') : (string) ($data[$cfg['slug_from']] ?? input($cfg['slug_from']));
            if (trim($slugSource) !== '') {
                $data['slug'] = ad_unique_slug($table, $slugSource, $id ?: null);
            }
        }

        if (empty($errors)) {
            if ($id) {
                DB::update($table, $data, 'id = :id', ['id' => $id]);
                activity_log((int)($_SESSION['admin_id'] ?? 0), 'update_' . $module, $singular . ' #' . $id);
                flash_set('success', $singular . ' updated.');
            } else {
                $newId = DB::insert($table, $data);
                activity_log((int)($_SESSION['admin_id'] ?? 0), 'create_' . $module, $singular . ' #' . $newId);
                flash_set('success', $singular . ' created.');
            }
            redirect('admin/pages/' . $self);
        }

        // Validation failed: re-render form with submitted values
        $GLOBALS['crud_errors'] = $errors;
        $GLOBALS['crud_form'] = array_merge(['id' => $id], $_POST);
        $action = $id ? 'edit' : 'new';
    } else {
        $action = input('action');
    }

    // ---- Render form (new/edit) ----
    if ($action === 'new' || $action === 'edit') {
        $row = $GLOBALS['crud_form'] ?? null;
        if (!$row && $action === 'edit') {
            $row = DB::row("SELECT * FROM `$table` WHERE id = ?", [ad_id()]);
            if (!$row) { flash_set('error', $singular . ' not found.'); redirect('admin/pages/' . $self); }
        }
        $errors = $GLOBALS['crud_errors'] ?? [];
        $isEdit = !empty($row['id']);
        $pageTitle = ($isEdit ? 'Edit ' : 'Add ') . $singular;
        include __DIR__ . '/header.php';
        echo '<a href="' . e($listUrl) . '" class="ad-btn ad-btn--sm ad-btn--ghost" style="margin-bottom:16px;"><i class="fas fa-arrow-left"></i> Back to ' . e($title) . '</a>';
        echo '<div class="ad-card"><form method="post" action="' . e($listUrl) . '" enctype="multipart/form-data">';
        echo csrf_field();
        echo '<input type="hidden" name="action" value="save">';
        if ($isEdit) echo '<input type="hidden" name="id" value="' . (int) $row['id'] . '">';
        echo '<div class="ad-form-grid">';
        foreach ($fields as $f) {
            crud_field_html($f, $row, $errors);
        }
        echo '</div>';
        echo '<div style="margin-top:18px;display:flex;gap:10px;"><button class="ad-btn ad-btn--primary" type="submit"><i class="fas fa-floppy-disk"></i> Save ' . e($singular) . '</button>';
        echo '<a href="' . e($listUrl) . '" class="ad-btn ad-btn--ghost">Cancel</a></div>';
        echo '</form></div>';
        include __DIR__ . '/footer.php';
        return;
    }

    // ---- Render list ----
    $page = max(1, (int) input('page', 1));
    $q = trim((string) input('q', ''));
    $where = '1=1';
    $params = [];
    if ($q !== '' && !empty($cfg['search'])) {
        $parts = [];
        foreach ($cfg['search'] as $col) { $parts[] = "`$col` LIKE ?"; $params[] = '%' . $q . '%'; }
        $where = '(' . implode(' OR ', $parts) . ')';
    }
    $total = (int) DB::value("SELECT COUNT(*) FROM `$table` WHERE $where", $params, 0);
    $offset = ($page - 1) * $perPage;
    $rows = DB::all("SELECT * FROM `$table` WHERE $where ORDER BY $orderBy LIMIT $perPage OFFSET $offset", $params);

    $pageTitle = $title;
    include __DIR__ . '/header.php';
    ?>
    <div class="ad-toolbar">
      <?php if (admin_can($module, 'create')): ?>
        <a href="<?= e($listUrl . '?action=new') ?>" class="ad-btn ad-btn--primary"><i class="fas fa-plus"></i> Add <?= e($singular) ?></a>
      <?php endif; ?>
      <?php if (!empty($cfg['search'])): ?>
      <form method="get" action="<?= e($listUrl) ?>" class="ad-spacer">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search...">
        <button class="ad-btn ad-btn--sm" type="submit"><i class="fas fa-magnifying-glass"></i></button>
        <?php if ($q !== ''): ?><a href="<?= e($listUrl) ?>" class="ad-btn ad-btn--sm ad-btn--ghost">Clear</a><?php endif; ?>
      </form>
      <?php endif; ?>
    </div>
    <?php if (empty($rows)): ?>
      <div class="ad-card"><div class="ad-empty"><div class="ad-empty__icon">&#128230;</div><h2>No <?= e(strtolower($title)) ?> yet</h2><p>Click "Add <?= e($singular) ?>" to create your first entry.</p></div></div>
    <?php else: ?>
      <div class="ad-table-wrap"><table class="ad-table">
        <thead><tr>
          <?php foreach ($cfg['columns'] as $key => $col): $label = is_array($col) ? $col['label'] : $col; ?>
            <th><?= e($label) ?></th>
          <?php endforeach; ?>
          <th style="width:120px;">Actions</th>
        </tr></thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <?php foreach ($cfg['columns'] as $key => $col): ?>
                <td><?php
                  if (is_array($col) && isset($col['render'])) { echo $col['render']($row); }
                  else { echo e((string) ($row[$key] ?? '')); }
                ?></td>
              <?php endforeach; ?>
              <td><div class="actions">
                <?php if (admin_can($module, 'edit')): ?>
                  <a href="<?= e($listUrl . '?action=edit&id=' . (int) $row['id']) ?>" class="ad-btn ad-btn--sm"><i class="fas fa-pen"></i></a>
                <?php endif; ?>
                <?= ad_delete_form($listUrl, (int) $row['id'], $module) ?>
              </div></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table></div>
      <?= ad_pagination($total, $perPage, $page, $listUrl . ($q !== '' ? '?q=' . urlencode($q) : '')) ?>
    <?php endif; ?>
    <?php
    include __DIR__ . '/footer.php';
}

/** Render a single form field. */
function crud_field_html(array $f, ?array $row, array $errors): void
{
    $name = $f['name'];
    $type = $f['type'] ?? 'text';
    $label = $f['label'] ?? ucfirst($name);
    $val = $row[$name] ?? ($f['default'] ?? '');
    $req = !empty($f['required']) ? ' <span class="req">*</span>' : '';
    $colClass = ($f['col'] ?? (in_array($type, ['textarea','richtext','image']) ? 'full' : '')) === 'full' ? ' full' : '';
    $err = $errors[$name] ?? '';

    echo '<div class="ad-field' . $colClass . '">';
    if ($type !== 'checkbox') {
        echo '<label>' . e($label) . $req . '</label>';
    }
    switch ($type) {
        case 'textarea':
            echo '<textarea name="' . e($name) . '">' . e($val) . '</textarea>';
            break;
        case 'richtext':
            echo '<textarea name="' . e($name) . '" style="min-height:240px;">' . e($val) . '</textarea>';
            echo '<small>Basic HTML allowed (headings, lists, links, images).</small>';
            break;
        case 'select':
            $options = $f['options'] ?? [];
            if (is_callable($options)) $options = $options();
            echo '<select name="' . e($name) . '">';
            foreach ($options as $ov => $ol) {
                echo '<option value="' . e($ov) . '"' . ((string) $val === (string) $ov ? ' selected' : '') . '>' . e($ol) . '</option>';
            }
            echo '</select>';
            break;
        case 'checkbox':
            echo '<label class="ad-checkbox"><input type="checkbox" name="' . e($name) . '" value="1"' . (!empty($val) ? ' checked' : '') . '> ' . e($label) . '</label>';
            break;
        case 'image':
            if ($val) {
                echo '<img src="' . e(upload_url($val)) . '" class="ad-thumb" style="width:90px;height:90px;margin-bottom:8px;" alt="">';
            }
            echo '<input type="hidden" name="' . e($name) . '_existing" value="' . e($val) . '">';
            echo '<input type="file" name="' . e($name) . '" accept="image/*" data-preview="#prev_' . e($name) . '">';
            echo '<img id="prev_' . e($name) . '" class="ad-thumb" style="width:90px;height:90px;margin-top:8px;display:none;" alt="">';
            break;
        case 'number':
        case 'int':
            echo '<input type="number" name="' . e($name) . '" value="' . e($val) . '">';
            break;
        case 'email':
            echo '<input type="email" name="' . e($name) . '" value="' . e($val) . '">';
            break;
        case 'color':
            echo '<input type="text" name="' . e($name) . '" value="' . e($val) . '" placeholder="#4f8cff">';
            break;
        default:
            $extra = !empty($f['slug_source']) ? ' data-slug-source="' . e($f['slug_source']) . '"' : '';
            $extra .= !empty($f['is_slug']) ? ' data-slug-target' : '';
            echo '<input type="text" name="' . e($name) . '" value="' . e($val) . '"' . $extra . '>';
    }
    if (!empty($f['help'])) echo '<small>' . e($f['help']) . '</small>';
    if ($err) echo '<small style="color:var(--ad-danger);">' . e($err) . '</small>';
    echo '</div>';
}
