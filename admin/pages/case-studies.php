<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/crud.php';

crud_run([
    'table' => 'case_studies',
    'module' => 'case_studies',
    'title' => 'Case Studies',
    'singular' => 'Case Study',
    'self' => 'case-studies.php',
    'order_by' => 'sort_order, id',
    'slug_from' => 'title',
    'search' => ['title', 'client_name', 'industry'],
    'columns' => [
        'title' => 'Title',
        'client_name' => 'Client',
        'industry' => 'Industry',
        'status' => ['label' => 'Status', 'render' => fn($r) => ad_badge($r['status'])],
    ],
    'fields' => [
        ['name' => 'title', 'label' => 'Title', 'required' => true],
        ['name' => 'client_name', 'label' => 'Client Name'],
        ['name' => 'industry', 'label' => 'Industry'],
        ['name' => 'featured_image', 'label' => 'Featured Image', 'type' => 'image', 'subdir' => 'portfolio'],
        ['name' => 'challenge', 'label' => 'The Challenge', 'type' => 'textarea'],
        ['name' => 'strategy', 'label' => 'The Strategy', 'type' => 'textarea'],
        ['name' => 'execution', 'label' => 'The Execution', 'type' => 'textarea'],
        ['name' => 'results', 'label' => 'The Results', 'type' => 'textarea'],
        ['name' => 'reach_growth', 'label' => 'Reach Growth (e.g. +340%)'],
        ['name' => 'leads_growth', 'label' => 'Leads Growth'],
        ['name' => 'revenue_impact', 'label' => 'Revenue Impact'],
        ['name' => 'roi_metric', 'label' => 'ROI / ROAS'],
        ['name' => 'before_metric', 'label' => 'Before Metric'],
        ['name' => 'after_metric', 'label' => 'After Metric'],
        ['name' => 'testimonial', 'label' => 'Client Testimonial', 'type' => 'textarea'],
        ['name' => 'gallery', 'label' => 'Gallery image paths (pipe | separated)', 'type' => 'textarea', 'help' => 'Upload via Media Library then paste relative paths separated by |'],
        ['name' => 'meta_title', 'label' => 'SEO Meta Title'],
        ['name' => 'meta_description', 'label' => 'SEO Meta Description', 'type' => 'textarea'],
        ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0],
        ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['published' => 'Published', 'draft' => 'Draft']],
    ],
]);
