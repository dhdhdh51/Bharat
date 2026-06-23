<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/crud.php';

crud_run([
    'table' => 'client_logos',
    'module' => 'settings',
    'title' => 'Client Logos',
    'singular' => 'Client Logo',
    'self' => 'client-logos.php',
    'order_by' => 'sort_order, id',
    'columns' => [
        'logo' => ['label' => 'Logo', 'render' => fn($r) => $r['logo'] ? '<img class="ad-thumb" src="' . e(upload_url($r['logo'])) . '" alt="">' : '—'],
        'name' => 'Name',
        'sort_order' => 'Order',
    ],
    'fields' => [
        ['name' => 'name', 'label' => 'Client Name', 'required' => true],
        ['name' => 'logo', 'label' => 'Logo Image', 'type' => 'image', 'subdir' => 'settings'],
        ['name' => 'url', 'label' => 'Website URL (optional)'],
        ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0],
    ],
]);
