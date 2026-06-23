<?php
/** Dynamic XML sitemap */
require_once __DIR__ . '/includes/bootstrap.php';
header('Content-Type: application/xml; charset=utf-8');

$base = rtrim(SITE_URL, '/');
$urls = [];

/** Add a URL entry. */
$add = function (string $loc, ?string $lastmod = null, string $freq = 'weekly', string $priority = '0.7') use (&$urls, $base) {
    $loc = preg_match('~^https?://~', $loc) ? $loc : $base . '/' . ltrim($loc, '/');
    $urls[] = [
        'loc' => $loc,
        'lastmod' => $lastmod ? date('Y-m-d', strtotime($lastmod)) : date('Y-m-d'),
        'freq' => $freq,
        'priority' => $priority,
    ];
};

// Static pages
$add('/', null, 'daily', '1.0');
$add('/about');
$add('/services', null, 'weekly', '0.9');
$add('/portfolio');
$add('/case-studies');
$add('/pricing', null, 'weekly', '0.8');
$add('/blog', null, 'daily', '0.8');
$add('/industries');
$add('/free-audit', null, 'monthly', '0.8');
$add('/contact', null, 'monthly', '0.6');
$add('/sitemap', null, 'monthly', '0.3');

// Dynamic content (only if DB is reachable)
if (DB::ok()) {
    foreach (DB::all("SELECT slug, updated_at FROM services WHERE status='published'") as $r) {
        $add('service/' . $r['slug'], $r['updated_at'], 'monthly', '0.8');
    }
    foreach (DB::all("SELECT slug, updated_at FROM blogs WHERE status='published' AND (published_at IS NULL OR published_at <= NOW())") as $r) {
        $add('blog/' . $r['slug'], $r['updated_at'], 'weekly', '0.7');
    }
    foreach (DB::all("SELECT slug, updated_at FROM portfolio WHERE status='published'") as $r) {
        $add('portfolio/' . $r['slug'], $r['updated_at'], 'monthly', '0.6');
    }
    foreach (DB::all("SELECT slug, updated_at FROM case_studies WHERE status='published'") as $r) {
        $add('case-studies/' . $r['slug'], $r['updated_at'], 'monthly', '0.6');
    }
    foreach (DB::all("SELECT slug, updated_at FROM pages WHERE status='published'") as $r) {
        $add('/' . $r['slug'], $r['updated_at'], 'yearly', '0.4');
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
    echo "    <lastmod>" . $u['lastmod'] . "</lastmod>\n";
    echo "    <changefreq>" . $u['freq'] . "</changefreq>\n";
    echo "    <priority>" . $u['priority'] . "</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>';
