<?php
/** Admin sidebar navigation (permission-aware). */
if (!defined('BASE_PATH')) { exit('Forbidden'); }
$cur = basename($_SERVER['SCRIPT_NAME'] ?? '');
$newLeads = (int) DB::value("SELECT COUNT(*) FROM leads WHERE status='new'", [], 0);
$unreadMsgs = (int) DB::value("SELECT COUNT(*) FROM contact_messages WHERE status='unread'", [], 0);
$pendingComments = (int) DB::value("SELECT COUNT(*) FROM blog_comments WHERE status='pending'", [], 0);

/** Render a nav link if permitted. */
function ad_link(string $file, string $icon, string $label, string $module, int $badge = 0): void
{
    global $cur;
    if (!admin_can($module, 'view')) return;
    $active = ($cur === $file) ? ' class="active"' : '';
    echo '<a href="' . e(admin_url('pages/' . $file)) . '"' . $active . '>'
        . '<i class="fas fa-' . $icon . '"></i> <span>' . e($label) . '</span>'
        . ($badge > 0 ? '<span class="badge">' . $badge . '</span>' : '')
        . '</a>';
}
?>
<aside class="ad-sidebar" id="adSidebar">
  <div class="ad-sidebar__brand">
    <span class="ad-sidebar__mark">B</span>
    <span><?= e(setting('site_name', SITE_NAME)) ?></span>
  </div>
  <nav class="ad-nav">
    <a href="<?= e(admin_url()) ?>"<?= ($cur === 'index.php' && str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin/index.php')) ? ' class="active"' : '' ?>><i class="fas fa-gauge-high"></i> <span>Dashboard</span></a>

    <div class="ad-nav__group">Leads &amp; Sales</div>
    <?php
      ad_link('leads.php', 'filter', 'Leads', 'leads', $newLeads);
      ad_link('contacts.php', 'envelope', 'Contact Messages', 'contacts', $unreadMsgs);
      ad_link('newsletter.php', 'paper-plane', 'Newsletter', 'newsletter');
      ad_link('packages.php', 'tags', 'Pricing Packages', 'packages');
      ad_link('payments.php', 'credit-card', 'Payments', 'payments');
    ?>

    <div class="ad-nav__group">Content</div>
    <?php
      ad_link('blogs.php', 'newspaper', 'Blog Posts', 'blogs');
      ad_link('blog-categories.php', 'folder-tree', 'Blog Categories', 'blog_categories');
      ad_link('comments.php', 'comments', 'Comments', 'comments', $pendingComments);
      ad_link('portfolio.php', 'briefcase', 'Portfolio', 'portfolio');
      ad_link('case-studies.php', 'chart-line', 'Case Studies', 'case_studies');
      ad_link('testimonials.php', 'quote-left', 'Testimonials', 'testimonials');
      ad_link('faqs.php', 'circle-question', 'FAQs', 'faqs');
      ad_link('team.php', 'users', 'Team', 'team');
      ad_link('client-logos.php', 'building', 'Client Logos', 'settings');
      ad_link('pages.php', 'file-lines', 'Pages', 'pages');
      ad_link('media.php', 'images', 'Media Library', 'media');
    ?>

    <div class="ad-nav__group">Services</div>
    <?php
      ad_link('services.php', 'layer-group', 'Services', 'services');
      ad_link('service-categories.php', 'sitemap', 'Service Categories', 'service_categories');
    ?>

    <div class="ad-nav__group">SEO</div>
    <?php
      ad_link('seo.php', 'magnifying-glass-chart', 'Page SEO', 'seo');
      ad_link('redirects.php', 'route', 'Redirects', 'redirects');
      ad_link('seo-tools.php', 'list-check', 'SEO Tools', 'seo');
    ?>

    <div class="ad-nav__group">Settings</div>
    <?php
      ad_link('settings.php', 'gear', 'Site Settings', 'settings');
      ad_link('menus.php', 'bars', 'Menus', 'settings');
      ad_link('social.php', 'share-nodes', 'Social Links', 'settings');
      ad_link('stats.php', 'chart-simple', 'Agency Stats', 'settings');
      if (is_super_admin()) {
        ad_link('users.php', 'user-shield', 'Users &amp; Roles', 'settings');
        ad_link('activity.php', 'clock-rotate-left', 'Activity Log', 'settings');
        ad_link('backup.php', 'database', 'Database Backup', 'settings');
      }
    ?>
  </nav>
</aside>
