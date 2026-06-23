<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/crud.php';

crud_run([
    'table' => 'testimonials',
    'module' => 'testimonials',
    'title' => 'Testimonials',
    'singular' => 'Testimonial',
    'self' => 'testimonials.php',
    'order_by' => 'sort_order, id DESC',
    'search' => ['client_name', 'company', 'review'],
    'columns' => [
        'client_name' => 'Client',
        'company' => 'Company',
        'rating' => ['label' => 'Rating', 'render' => fn($r) => str_repeat('★', max(1, min(5, (int) $r['rating'])))],
        'status' => ['label' => 'Status', 'render' => fn($r) => ad_badge($r['status'])],
    ],
    'fields' => [
        ['name' => 'client_name', 'label' => 'Client Name', 'required' => true],
        ['name' => 'company', 'label' => 'Company'],
        ['name' => 'designation', 'label' => 'Designation'],
        ['name' => 'rating', 'label' => 'Rating (1-5)', 'type' => 'number', 'default' => 5],
        ['name' => 'review', 'label' => 'Review', 'type' => 'textarea', 'required' => true],
        ['name' => 'image', 'label' => 'Client Photo', 'type' => 'image', 'subdir' => 'team'],
        ['name' => 'video_url', 'label' => 'Video Testimonial URL'],
        ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0],
        ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['published' => 'Published', 'draft' => 'Draft']],
    ],
]);
