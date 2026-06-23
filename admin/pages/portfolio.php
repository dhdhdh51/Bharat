<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/crud.php';

crud_run([
    'table' => 'portfolio',
    'module' => 'portfolio',
    'title' => 'Portfolio',
    'singular' => 'Project',
    'self' => 'portfolio.php',
    'order_by' => 'sort_order, id',
    'slug_from' => 'title',
    'search' => ['title', 'category', 'client_industry'],
    'columns' => [
        'featured_image' => ['label' => 'Image', 'render' => fn($r) => '<img class="ad-thumb" src="' . e(img($r['featured_image'], 'portfolio')) . '" alt="">'],
        'title' => 'Title',
        'category' => 'Category',
        'status' => ['label' => 'Status', 'render' => fn($r) => ad_badge($r['status'])],
    ],
    'fields' => [
        ['name' => 'title', 'label' => 'Project Title', 'required' => true],
        ['name' => 'category', 'label' => 'Category', 'help' => 'e.g. Social Media, SEO, Paid Advertising'],
        ['name' => 'client_industry', 'label' => 'Client Industry'],
        ['name' => 'services_provided', 'label' => 'Services Provided (pipe | separated)'],
        ['name' => 'result_metric', 'label' => 'Headline Result', 'help' => 'e.g. +340% engagement'],
        ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
        ['name' => 'featured_image', 'label' => 'Featured Image', 'type' => 'image', 'subdir' => 'portfolio'],
        ['name' => 'project_url', 'label' => 'Project URL (optional)'],
        ['name' => 'meta_title', 'label' => 'SEO Meta Title'],
        ['name' => 'meta_description', 'label' => 'SEO Meta Description', 'type' => 'textarea'],
        ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0],
        ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['published' => 'Published', 'draft' => 'Draft']],
    ],
]);
