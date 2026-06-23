<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/crud.php';

crud_run([
    'table' => 'agency_stats',
    'module' => 'settings',
    'title' => 'Agency Stats',
    'singular' => 'Stat',
    'self' => 'stats.php',
    'order_by' => 'sort_order, id',
    'columns' => [
        'label' => 'Label',
        'value' => ['label' => 'Value', 'render' => fn($r) => e($r['value'] . $r['suffix'])],
        'sort_order' => 'Order',
    ],
    'fields' => [
        ['name' => 'label', 'label' => 'Label', 'required' => true],
        ['name' => 'value', 'label' => 'Value (number)', 'required' => true],
        ['name' => 'suffix', 'label' => 'Suffix (e.g. +, K+, x)'],
        ['name' => 'icon', 'label' => 'Icon (optional)'],
        ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0],
    ],
]);
