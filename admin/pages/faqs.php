<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/crud.php';

crud_run([
    'table' => 'faqs',
    'module' => 'faqs',
    'title' => 'FAQs',
    'singular' => 'FAQ',
    'self' => 'faqs.php',
    'order_by' => 'sort_order, id',
    'search' => ['question', 'answer'],
    'columns' => [
        'question' => ['label' => 'Question', 'render' => fn($r) => e(str_excerpt($r['question'], 80))],
        'category' => 'Category',
        'status' => ['label' => 'Status', 'render' => fn($r) => ad_badge($r['status'])],
    ],
    'fields' => [
        ['name' => 'question', 'label' => 'Question', 'required' => true, 'col' => 'full'],
        ['name' => 'answer', 'label' => 'Answer', 'type' => 'textarea', 'required' => true],
        ['name' => 'category', 'label' => 'Category', 'default' => 'general'],
        ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0],
        ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['published' => 'Published', 'draft' => 'Draft']],
    ],
]);
