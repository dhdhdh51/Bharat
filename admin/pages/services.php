<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/crud.php';

$cats = function () {
    $out = ['' => '— None —'];
    foreach (DB::all('SELECT id, name FROM service_categories ORDER BY sort_order, name') as $c) {
        $out[$c['id']] = $c['name'];
    }
    return $out;
};

crud_run([
    'table' => 'services',
    'module' => 'services',
    'title' => 'Services',
    'singular' => 'Service',
    'self' => 'services.php',
    'order_by' => 'sort_order, id',
    'slug_from' => 'title',
    'search' => ['title', 'short_description'],
    'columns' => [
        'title' => 'Title',
        'slug' => ['label' => 'URL', 'render' => fn($r) => '<a href="' . e(url('service/' . $r['slug'])) . '" target="_blank">/service/' . e($r['slug']) . '</a>'],
        'is_featured' => ['label' => 'Featured', 'render' => fn($r) => $r['is_featured'] ? '⭐' : '—'],
        'status' => ['label' => 'Status', 'render' => fn($r) => ad_badge($r['status'])],
    ],
    'fields' => [
        ['name' => 'title', 'label' => 'Service Title', 'required' => true],
        ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'options' => $cats],
        ['name' => 'icon', 'label' => 'Font Awesome Icon', 'help' => 'e.g. share-nodes, instagram, google, code'],
        ['name' => 'short_description', 'label' => 'Short Description', 'type' => 'textarea'],
        ['name' => 'overview', 'label' => 'Overview', 'type' => 'textarea'],
        ['name' => 'benefits', 'label' => 'Benefits (pipe | separated)', 'type' => 'textarea', 'help' => 'Separate each with |'],
        ['name' => 'deliverables', 'label' => 'Deliverables (pipe | separated)', 'type' => 'textarea', 'help' => 'Separate each with |'],
        ['name' => 'process', 'label' => 'Process Steps (pipe | separated)', 'type' => 'textarea', 'help' => 'Separate each with |'],
        ['name' => 'image', 'label' => 'Image', 'type' => 'image', 'subdir' => 'services'],
        ['name' => 'banner', 'label' => 'Banner / OG Image', 'type' => 'image', 'subdir' => 'services'],
        ['name' => 'meta_title', 'label' => 'SEO Meta Title'],
        ['name' => 'meta_description', 'label' => 'SEO Meta Description', 'type' => 'textarea'],
        ['name' => 'is_featured', 'label' => 'Featured on homepage', 'type' => 'checkbox'],
        ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0],
        ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['published' => 'Published', 'draft' => 'Draft']],
    ],
]);
