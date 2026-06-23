<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/crud.php';

crud_run([
    'table' => 'service_categories',
    'module' => 'service_categories',
    'title' => 'Service Categories',
    'singular' => 'Category',
    'self' => 'service-categories.php',
    'order_by' => 'sort_order, id',
    'slug_from' => 'name',
    'search' => ['name'],
    'columns' => [
        'name' => 'Name',
        'slug' => 'Slug',
        'sort_order' => 'Order',
    ],
    'fields' => [
        ['name' => 'name', 'label' => 'Category Name', 'required' => true],
        ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
        ['name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'default' => 0],
    ],
]);
