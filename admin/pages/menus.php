<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/crud.php';

crud_run([
    'table' => 'menus',
    'module' => 'settings',
    'title' => 'Menu Items',
    'singular' => 'Menu Item',
    'self' => 'menus.php',
    'order_by' => 'location, sort_order, id',
    'search' => ['title', 'url'],
    'columns' => [
        'location' => 'Location',
        'title' => 'Title',
        'url' => 'URL',
        'is_button' => ['label' => 'Button', 'render' => fn($r) => $r['is_button'] ? 'Yes' : '—'],
        'status' => ['label' => 'Status', 'render' => fn($r) => ad_badge($r['status'])],
    ],
    'fields' => [
        ['name' => 'location', 'label' => 'Location', 'type' => 'select', 'options' => ['header' => 'Header', 'footer' => 'Footer']],
        ['name' => 'title', 'label' => 'Title', 'required' => true],
        ['name' => 'url', 'label' => 'URL', 'required' => true, 'help' => 'e.g. /about or https://...'],
        ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0],
        ['name' => 'is_button', 'label' => 'Show as button (header CTA)', 'type' => 'checkbox'],
        ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
    ],
]);
