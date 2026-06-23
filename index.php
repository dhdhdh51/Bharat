<?php
/**
 * Bharat SEO - Front Controller / Router
 */
require_once __DIR__ . '/includes/bootstrap.php';

/* ---- Resolve the request path relative to the app base ---- */
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$uriPath = rawurldecode($uriPath);
if ($scriptDir !== '' && $scriptDir !== '/' && str_starts_with($uriPath, $scriptDir)) {
    $uriPath = substr($uriPath, strlen($scriptDir));
}
$route = trim($uriPath, '/');

// Allow explicit ?route= override (used by ErrorDocument directives)
if (isset($_GET['route']) && $route === '') {
    $route = trim((string) $_GET['route'], '/');
}
if ($route === 'index.php') {
    $route = '';
}

/* ---- Database availability guard ---- */
if (!DB::ok()) {
    http_response_code(503);
    if (is_file(BASE_PATH . '/templates/db-error.php')) {
        include BASE_PATH . '/templates/db-error.php';
    } else {
        echo '<h1>Service temporarily unavailable</h1><p>We are unable to reach the database. Please try again shortly.</p>';
    }
    exit;
}

/* ---- 301/302 redirects from admin-managed table ---- */
$redirect = DB::row('SELECT * FROM redirects WHERE source_path = ?', ['/' . $route]);
if (!$redirect && $route !== '') {
    $redirect = DB::row('SELECT * FROM redirects WHERE source_path = ?', [$route]);
}
if ($redirect) {
    DB::run('UPDATE redirects SET hits = hits + 1 WHERE id = ?', [$redirect['id']]);
    redirect($redirect['target_url'], (int) ($redirect['status_code'] ?: 301));
}

/* ---- Maintenance mode (public site only; admins bypass) ---- */
if (is_maintenance() && !str_starts_with($route, 'admin') && empty($_SESSION['admin_id'])) {
    http_response_code(503);
    header('Retry-After: 3600');
    include BASE_PATH . '/templates/maintenance.php';
    exit;
}

/* ---- Split route into segments ---- */
$segments = $route === '' ? [] : explode('/', $route);
$first = $segments[0] ?? '';
$slug  = $segments[1] ?? '';

/* ---- Static page map ---- */
$staticPages = [
    ''             => 'home',
    'home'         => 'home',
    'about'        => 'about',
    'services'     => 'services',
    'portfolio'    => 'portfolio',
    'case-studies' => 'case-studies',
    'pricing'      => 'pricing',
    'blog'         => 'blog',
    'contact'      => 'contact',
    'free-audit'   => 'free-audit',
    'industries'   => 'industries',
    'sitemap'      => 'sitemap',
    'payment-success' => 'payment-success',
    'payment-failed'  => 'payment-failed',
];

/* ---- CMS / legal pages handled via pages table ---- */
$cmsSlugs = ['privacy-policy', 'terms-conditions', 'disclaimer'];

$template = null;
$GLOBALS['route_slug'] = $slug;

if (array_key_exists($route, $staticPages)) {
    $template = $staticPages[$route];
} elseif ($first === 'service' && $slug !== '') {
    $template = 'service-detail';
} elseif ($first === 'blog' && $slug !== '') {
    $template = 'blog-detail';
} elseif ($first === 'portfolio' && $slug !== '') {
    $template = 'portfolio-detail';
} elseif (($first === 'case-studies' || $first === 'case-study') && $slug !== '') {
    $template = 'case-study-detail';
} elseif (in_array($route, $cmsSlugs, true) || ($first === 'page' && $slug !== '')) {
    $GLOBALS['route_slug'] = ($first === 'page') ? $slug : $route;
    $template = 'page';
}

/* ---- Render ---- */
$templateFile = $template ? BASE_PATH . '/templates/' . $template . '.php' : null;

if ($templateFile && is_file($templateFile)) {
    include $templateFile;
} else {
    http_response_code(404);
    include BASE_PATH . '/templates/404.php';
}
