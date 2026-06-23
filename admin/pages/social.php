<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/crud.php';

crud_run([
    'table' => 'social_links',
    'module' => 'settings',
    'title' => 'Social Links',
    'singular' => 'Social Link',
    'self' => 'social.php',
    'order_by' => 'sort_order, id',
    'columns' => [
        'platform' => 'Platform',
        'url' => ['label' => 'URL', 'render' => fn($r) => e(str_excerpt($r['url'], 50))],
        'status' => ['label' => 'Status', 'render' => fn($r) => ad_badge($r['status'])],
    ],
    'fields' => [
        ['name' => 'platform', 'label' => 'Platform Name', 'required' => true],
        ['name' => 'icon', 'label' => 'Font Awesome Brand Icon', 'help' => 'e.g. facebook, instagram, linkedin, youtube, x-twitter'],
        ['name' => 'url', 'label' => 'Profile URL', 'required' => true],
        ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0],
        ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
    ],
]);
