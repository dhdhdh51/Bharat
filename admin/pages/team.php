<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/crud.php';

crud_run([
    'table' => 'team_members',
    'module' => 'team',
    'title' => 'Team Members',
    'singular' => 'Team Member',
    'self' => 'team.php',
    'order_by' => 'sort_order, id',
    'search' => ['name', 'designation'],
    'columns' => [
        'image' => ['label' => 'Photo', 'render' => fn($r) => '<img class="ad-thumb" src="' . e(img($r['image'], 'team')) . '" alt="">'],
        'name' => 'Name',
        'designation' => 'Designation',
        'status' => ['label' => 'Status', 'render' => fn($r) => ad_badge($r['status'])],
    ],
    'fields' => [
        ['name' => 'name', 'label' => 'Name', 'required' => true],
        ['name' => 'designation', 'label' => 'Designation'],
        ['name' => 'image', 'label' => 'Photo', 'type' => 'image', 'subdir' => 'team'],
        ['name' => 'bio', 'label' => 'Bio', 'type' => 'textarea'],
        ['name' => 'social_linkedin', 'label' => 'LinkedIn URL'],
        ['name' => 'social_twitter', 'label' => 'Twitter URL'],
        ['name' => 'social_instagram', 'label' => 'Instagram URL'],
        ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0],
        ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
    ],
]);
