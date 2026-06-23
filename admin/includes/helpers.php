<?php
/** Admin shared helpers for CRUD pages. */
if (!defined('BASE_PATH')) { exit('Forbidden'); }

/** Status badge. */
function ad_badge(string $status): string
{
    $label = ucwords(str_replace('_', ' ', $status));
    return '<span class="ad-badge ad-badge--' . e($status) . '">' . e($label) . '</span>';
}

/** Admin pagination markup. */
function ad_pagination(int $total, int $perPage, int $page, string $baseUrl): string
{
    $pages = (int) ceil($total / max(1, $perPage));
    if ($pages <= 1) return '';
    $sep = str_contains($baseUrl, '?') ? '&' : '?';
    $out = '<div class="ad-pagination">';
    for ($i = 1; $i <= $pages; $i++) {
        if ($i == 1 || $i == $pages || abs($i - $page) <= 2) {
            if ($i === $page) $out .= '<span class="active">' . $i . '</span>';
            else $out .= '<a href="' . e($baseUrl . $sep . 'page=' . $i) . '">' . $i . '</a>';
        } elseif (abs($i - $page) === 3) {
            $out .= '<span>…</span>';
        }
    }
    return $out . '</div>';
}

/**
 * Handle an optional image upload field in an admin form.
 * Returns the new relative path, the existing value, or '' .
 */
function ad_handle_upload(string $field, string $subdir, string $existing = ''): string
{
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $existing;
    }
    $res = upload_file($_FILES[$field], $subdir);
    if (!empty($res['ok'])) {
        // record in media library
        DB::insert('media_library', [
            'file_name'   => $res['name'] ?? basename($res['path']),
            'file_path'   => $res['path'],
            'file_type'   => $res['mime'] ?? null,
            'file_size'   => $res['size'] ?? null,
            'uploaded_by' => (int) ($_SESSION['admin_id'] ?? 0) ?: null,
        ]);
        return $res['path'];
    }
    if (!empty($res['error'])) {
        flash_set('error', 'Image upload: ' . $res['error']);
    }
    return $existing;
}

/** Ensure unique slug for a table; appends -2, -3 etc. */
function ad_unique_slug(string $table, string $slug, ?int $excludeId = null): string
{
    $slug = slugify($slug);
    $base = $slug;
    $i = 1;
    while (true) {
        $sql = "SELECT id FROM `$table` WHERE slug = ?" . ($excludeId ? " AND id != " . (int) $excludeId : "");
        $exists = DB::value($sql, [$slug]);
        if (!$exists) return $slug;
        $i++;
        $slug = $base . '-' . $i;
    }
}

/** Get a validated positive integer id from query/post. */
function ad_id(string $key = 'id'): int
{
    return max(0, (int) input($key, 0));
}

/** Render delete form button. */
function ad_delete_form(string $action, int $id, string $module = '', string $label = 'Delete'): string
{
    if ($module !== '' && !admin_can($module, 'delete')) return '';
    $out = '<form method="post" action="' . e($action) . '" data-confirm="Delete this item? This cannot be undone." style="display:inline;">';
    $out .= csrf_field();
    $out .= '<input type="hidden" name="action" value="delete">';
    $out .= '<input type="hidden" name="id" value="' . $id . '">';
    $out .= '<button class="ad-btn ad-btn--sm ad-btn--danger" type="submit"><i class="fas fa-trash"></i></button>';
    $out .= '</form>';
    return $out;
}
