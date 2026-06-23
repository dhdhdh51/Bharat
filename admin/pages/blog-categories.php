<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/crud.php';

crud_run([
    'table' => 'blog_categories',
    'module' => 'blog_categories',
    'title' => 'Blog Categories',
    'singular' => 'Category',
    'self' => 'blog-categories.php',
    'order_by' => 'name',
    'slug_from' => 'name',
    'search' => ['name'],
    'columns' => [
        'name' => 'Name',
        'slug' => 'Slug',
    ],
    'fields' => [
        ['name' => 'name', 'label' => 'Category Name', 'required' => true],
        ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
    ],
]);
