<?php
/**
 * Bharat SEO - SEO meta tags + JSON-LD schema generation
 */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }

class SEO
{
    public static array $data = [];

    /** Set SEO data for the current page. Pulls overrides from page_seo table by key. */
    public static function set(array $data, ?string $pageKey = null): void
    {
        if ($pageKey) {
            $row = DB::row('SELECT * FROM page_seo WHERE page_key = ?', [$pageKey]);
            if ($row) {
                if (!empty($row['meta_title']))       $data['title'] = $row['meta_title'];
                if (!empty($row['meta_description'])) $data['description'] = $row['meta_description'];
                if (!empty($row['meta_keywords']))    $data['keywords'] = $row['meta_keywords'];
                if (!empty($row['canonical_url']))    $data['canonical'] = $row['canonical_url'];
                if (!empty($row['og_image']))         $data['image'] = upload_url($row['og_image']);
                if (!empty($row['noindex']))          $data['noindex'] = (bool) $row['noindex'];
            }
        }
        self::$data = array_merge(self::defaults(), $data);
    }

    private static function defaults(): array
    {
        return [
            'title'       => setting('meta_title', SITE_NAME),
            'description' => setting('meta_description', ''),
            'keywords'    => setting('meta_keywords', ''),
            'canonical'   => current_url_clean(),
            'image'       => self::defaultImage(),
            'type'        => 'website',
            'noindex'     => false,
            'schema'      => [],
        ];
    }

    private static function defaultImage(): string
    {
        $og = setting('og_image', '');
        return $og !== '' ? upload_url($og) : asset('images/og-default.jpg');
    }

    public static function get(string $key, $default = '')
    {
        return self::$data[$key] ?? $default;
    }

    /** Render <head> meta tags. */
    public static function renderMeta(): string
    {
        $d = self::$data ?: self::defaults();
        $title = $d['title'] ?: SITE_NAME;
        $out = '';
        $out .= '<title>' . e($title) . '</title>' . "\n";
        $out .= '<meta name="description" content="' . e($d['description']) . '">' . "\n";
        if (!empty($d['keywords'])) {
            $out .= '<meta name="keywords" content="' . e($d['keywords']) . '">' . "\n";
        }
        $robots = !empty($d['noindex']) ? 'noindex, nofollow' : 'index, follow';
        $out .= '<meta name="robots" content="' . $robots . '">' . "\n";
        $out .= '<link rel="canonical" href="' . e($d['canonical']) . '">' . "\n";

        // Open Graph
        $out .= '<meta property="og:site_name" content="' . e(SITE_NAME) . '">' . "\n";
        $out .= '<meta property="og:type" content="' . e($d['type']) . '">' . "\n";
        $out .= '<meta property="og:title" content="' . e($title) . '">' . "\n";
        $out .= '<meta property="og:description" content="' . e($d['description']) . '">' . "\n";
        $out .= '<meta property="og:url" content="' . e($d['canonical']) . '">' . "\n";
        $out .= '<meta property="og:image" content="' . e($d['image']) . '">' . "\n";

        // Twitter
        $out .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
        $out .= '<meta name="twitter:title" content="' . e($title) . '">' . "\n";
        $out .= '<meta name="twitter:description" content="' . e($d['description']) . '">' . "\n";
        $out .= '<meta name="twitter:image" content="' . e($d['image']) . '">' . "\n";

        return $out;
    }

    /** Render JSON-LD schema blocks. */
    public static function renderSchema(): string
    {
        $blocks = [];

        // Organization + WebSite (always present)
        $blocks[] = [
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => SITE_NAME,
            'url'      => rtrim(SITE_URL, '/'),
            'logo'     => self::defaultImage(),
            'description' => setting('footer_about', ''),
            'sameAs'   => self::socialUrls(),
            'contactPoint' => [
                '@type'       => 'ContactPoint',
                'telephone'   => setting('contact_phone', ''),
                'contactType' => 'customer service',
                'email'       => setting('contact_email', ''),
            ],
        ];

        $blocks[] = [
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            'name'     => SITE_NAME,
            'url'      => rtrim(SITE_URL, '/'),
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => rtrim(SITE_URL, '/') . '/blog?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];

        // LocalBusiness
        $blocks[] = [
            '@context' => 'https://schema.org',
            '@type'    => 'LocalBusiness',
            'name'     => SITE_NAME,
            'image'    => self::defaultImage(),
            'telephone'=> setting('contact_phone', ''),
            'email'    => setting('contact_email', ''),
            'address'  => [
                '@type'         => 'PostalAddress',
                'streetAddress' => setting('address', ''),
            ],
            'url' => rtrim(SITE_URL, '/'),
        ];

        // Page-specific schema (Service, Article, FAQ, Breadcrumb, etc.)
        foreach ((self::$data['schema'] ?? []) as $custom) {
            $blocks[] = $custom;
        }

        $out = '';
        foreach ($blocks as $b) {
            $out .= '<script type="application/ld+json">' . json_encode($b, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
        }
        return $out;
    }

    public static function socialUrls(): array
    {
        $urls = [];
        foreach (DB::all("SELECT url FROM social_links WHERE status='active' ORDER BY sort_order") as $s) {
            if (!empty($s['url'])) {
                $urls[] = $s['url'];
            }
        }
        return $urls;
    }

    /** Build a breadcrumb schema + return for output. */
    public static function breadcrumb(array $items): array
    {
        $list = [];
        $pos = 1;
        foreach ($items as $name => $u) {
            $list[] = [
                '@type'    => 'ListItem',
                'position' => $pos++,
                'name'     => $name,
                'item'     => preg_match('~^https?://~', $u) ? $u : url($u),
            ];
        }
        return [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $list,
        ];
    }

    /** FAQ schema from rows with question/answer. */
    public static function faqSchema(array $faqs): array
    {
        $main = [];
        foreach ($faqs as $f) {
            $main[] = [
                '@type'          => 'Question',
                'name'           => $f['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => strip_tags($f['answer']),
                ],
            ];
        }
        return ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $main];
    }
}

/** Canonical current URL without query string. */
function current_url_clean(): string
{
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
    return $scheme . '://' . $host . $path;
}

/** Render a visible HTML breadcrumb bar. */
function breadcrumb_html(array $items): string
{
    if (count($items) < 2) {
        return '';
    }
    $html = '<nav class="breadcrumb" aria-label="Breadcrumb"><div class="container"><ol>';
    $last = array_key_last($items);
    foreach ($items as $name => $u) {
        if ($name === $last) {
            $html .= '<li aria-current="page">' . e($name) . '</li>';
        } else {
            $href = preg_match('~^https?://~', $u) ? $u : url($u);
            $html .= '<li><a href="' . e($href) . '">' . e($name) . '</a></li>';
        }
    }
    $html .= '</ol></div></nav>';
    return $html;
}
