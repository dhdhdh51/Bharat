<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/crud.php';

$cats = function () {
    $out = ['' => '— Uncategorized —'];
    foreach (DB::all('SELECT id, name FROM blog_categories ORDER BY name') as $c) {
        $out[$c['id']] = $c['name'];
    }
    return $out;
};

crud_run([
    'table' => 'blogs',
    'module' => 'blogs',
    'title' => 'Blog Posts',
    'singular' => 'Post',
    'self' => 'blogs.php',
    'order_by' => 'COALESCE(published_at, created_at) DESC',
    'slug_from' => 'title',
    'search' => ['title', 'excerpt', 'tags'],
    'columns' => [
        'featured_image' => ['label' => 'Image', 'render' => fn($r) => '<img class="ad-thumb" src="' . e(img($r['featured_image'], 'blog')) . '" alt="">'],
        'title' => 'Title',
        'author' => 'Author',
        'views' => 'Views',
        'status' => ['label' => 'Status', 'render' => fn($r) => ad_badge($r['status'])],
    ],
    'fields' => [
        ['name' => 'title', 'label' => 'Title', 'required' => true],
        ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'options' => $cats],
        ['name' => 'excerpt', 'label' => 'Excerpt', 'type' => 'textarea', 'help' => 'Short summary shown in listings.'],
        ['name' => 'content', 'label' => 'Content', 'type' => 'richtext'],
        ['name' => 'featured_image', 'label' => 'Featured Image', 'type' => 'image', 'subdir' => 'blog'],
        ['name' => 'author', 'label' => 'Author', 'default' => 'Bharat SEO Team'],
        ['name' => 'reading_time', 'label' => 'Reading Time (min)', 'type' => 'number', 'default' => 5],
        ['name' => 'tags', 'label' => 'Tags (comma separated)'],
        ['name' => 'meta_title', 'label' => 'SEO Meta Title'],
        ['name' => 'meta_description', 'label' => 'SEO Meta Description', 'type' => 'textarea'],
        ['name' => 'published_at', 'label' => 'Publish Date (YYYY-MM-DD HH:MM:SS)', 'help' => 'Leave blank to publish immediately. Set a future date for scheduled posts.'],
        ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['published' => 'Published', 'draft' => 'Draft', 'scheduled' => 'Scheduled']],
    ],
]);
