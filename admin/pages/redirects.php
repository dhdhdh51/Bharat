<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/crud.php';

crud_run([
    'table' => 'redirects',
    'module' => 'redirects',
    'title' => '301 Redirects',
    'singular' => 'Redirect',
    'self' => 'redirects.php',
    'order_by' => 'id DESC',
    'search' => ['source_path', 'target_url'],
    'columns' => [
        'source_path' => 'From',
        'target_url' => ['label' => 'To', 'render' => fn($r) => e(str_excerpt($r['target_url'], 50))],
        'status_code' => 'Code',
        'hits' => 'Hits',
    ],
    'fields' => [
        ['name' => 'source_path', 'label' => 'Source Path', 'required' => true, 'help' => 'e.g. /old-page'],
        ['name' => 'target_url', 'label' => 'Target URL', 'required' => true, 'help' => 'e.g. /new-page or https://...'],
        ['name' => 'status_code', 'label' => 'Status Code', 'type' => 'select', 'options' => ['301' => '301 (Permanent)', '302' => '302 (Temporary)']],
    ],
]);
