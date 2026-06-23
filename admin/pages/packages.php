<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/crud.php';

crud_run([
    'table' => 'pricing_packages',
    'module' => 'packages',
    'title' => 'Pricing Packages',
    'singular' => 'Package',
    'self' => 'packages.php',
    'order_by' => 'sort_order, id',
    'search' => ['name', 'description'],
    'columns' => [
        'name' => 'Name',
        'price' => ['label' => 'Price', 'render' => fn($r) => e($r['currency'] . ' ' . $r['price'] . ' ' . $r['duration'])],
        'is_popular' => ['label' => 'Popular', 'render' => fn($r) => $r['is_popular'] ? '⭐' : '—'],
        'status' => ['label' => 'Status', 'render' => fn($r) => ad_badge($r['status'])],
    ],
    'fields' => [
        ['name' => 'name', 'label' => 'Package Name', 'required' => true],
        ['name' => 'price', 'label' => 'Price (e.g. 49,999 or Custom)', 'required' => true],
        ['name' => 'currency', 'label' => 'Currency', 'default' => 'INR'],
        ['name' => 'duration', 'label' => 'Duration (e.g. /month)'],
        ['name' => 'description', 'label' => 'Short Description', 'type' => 'textarea'],
        ['name' => 'features', 'label' => 'Features (one per line, use | to separate)', 'type' => 'textarea', 'help' => 'Separate each feature with a pipe | character.'],
        ['name' => 'cta_text', 'label' => 'Button Text', 'default' => 'Get Started'],
        ['name' => 'payment_link', 'label' => 'Payment / Checkout Link (optional)'],
        ['name' => 'notes', 'label' => 'Notes (optional)'],
        ['name' => 'is_popular', 'label' => 'Mark as Most Popular', 'type' => 'checkbox'],
        ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0],
        ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['published' => 'Published', 'draft' => 'Draft']],
    ],
]);
