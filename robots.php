<?php
/** Dynamic robots.txt */
require_once __DIR__ . '/includes/bootstrap.php';
header('Content-Type: text/plain; charset=utf-8');

$custom = trim((string) setting_raw('robots_txt', ''));
if ($custom !== '') {
    echo $custom;
    exit;
}

$base = rtrim(SITE_URL, '/');
echo "User-agent: *\n";
if (is_maintenance()) {
    echo "Disallow: /\n";
} else {
    echo "Allow: /\n";
    echo "Disallow: /admin/\n";
    echo "Disallow: /api/\n";
    echo "Disallow: /includes/\n";
    echo "Disallow: /config/\n";
}
echo "\nSitemap: $base/sitemap.xml\n";
