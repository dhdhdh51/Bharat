<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/crud.php';

crud_run([
    'table' => 'pages',
    'module' => 'pages',
    'title' => 'Pages',
    'singular' => 'Page',
    'self' => 'pages.php',
    'order_by' => 'id DESC',
    'slug_from' => 'title',
    'search' => ['title', 'content'],
    'columns' => [
        'title' => 'Title',
        'slug' => ['label' => 'URL', 'render' => fn($r) => '<a href="' . e(url('/' . $r['slug'])) . '" target="_blank">/' . e($r['slug']) . '</a>'],
        'status' => ['label' => 'Status', 'render' => fn($r) => ad_badge($r['status'])],
    ],
    'fields' => [
        ['name' => 'title', 'label' => 'Page Title', 'required' => true],
        ['name' => 'content', 'label' => 'Content', 'type' => 'richtext'],
        ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['published' => 'Published', 'draft' => 'Draft']],
    ],
]);
