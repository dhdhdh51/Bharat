<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
$admin = require_admin();

// Stats
$totalLeads   = (int) DB::value("SELECT COUNT(*) FROM leads", [], 0);
$newLeads     = (int) DB::value("SELECT COUNT(*) FROM leads WHERE status='new'", [], 0);
$wonLeads     = (int) DB::value("SELECT COUNT(*) FROM leads WHERE status='won'", [], 0);
$blogViews    = (int) DB::value("SELECT COALESCE(SUM(views),0) FROM blogs", [], 0);
$totalBlogs   = (int) DB::value("SELECT COUNT(*) FROM blogs WHERE status='published'", [], 0);
$unreadMsgs   = (int) DB::value("SELECT COUNT(*) FROM contact_messages WHERE status='unread'", [], 0);
$subscribers  = (int) DB::value("SELECT COUNT(*) FROM newsletters WHERE status='subscribed'", [], 0);
$pendingComments = (int) DB::value("SELECT COUNT(*) FROM blog_comments WHERE status='pending'", [], 0);

$recentLeads = DB::all("SELECT * FROM leads ORDER BY created_at DESC LIMIT 6");
$topBlogs    = DB::all("SELECT title, slug, views FROM blogs WHERE status='published' ORDER BY views DESC LIMIT 5");
$recentActivity = DB::all("SELECT al.*, a.name AS admin_name FROM activity_logs al LEFT JOIN admins a ON a.id=al.admin_id ORDER BY al.created_at DESC LIMIT 8");

$pageTitle = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>
<div class="ad-grid ad-stats" style="margin-bottom:24px;">
  <div class="ad-card ad-stat"><div class="ad-stat__top"><div><div class="ad-stat__value"><?= $totalLeads ?></div><div class="ad-stat__label">Total Leads</div></div><span class="ad-stat__icon"><i class="fas fa-filter"></i></span></div></div>
  <div class="ad-card ad-stat"><div class="ad-stat__top"><div><div class="ad-stat__value"><?= $newLeads ?></div><div class="ad-stat__label">New Leads</div></div><span class="ad-stat__icon" style="color:var(--ad-cyan);"><i class="fas fa-bell"></i></span></div></div>
  <div class="ad-card ad-stat"><div class="ad-stat__top"><div><div class="ad-stat__value"><?= number_format($blogViews) ?></div><div class="ad-stat__label">Blog Views</div></div><span class="ad-stat__icon" style="color:var(--ad-purple);"><i class="fas fa-eye"></i></span></div></div>
  <div class="ad-card ad-stat"><div class="ad-stat__top"><div><div class="ad-stat__value"><?= $subscribers ?></div><div class="ad-stat__label">Subscribers</div></div><span class="ad-stat__icon" style="color:var(--ad-gold);"><i class="fas fa-paper-plane"></i></span></div></div>
  <div class="ad-card ad-stat"><div class="ad-stat__top"><div><div class="ad-stat__value"><?= $unreadMsgs ?></div><div class="ad-stat__label">Unread Messages</div></div><span class="ad-stat__icon"><i class="fas fa-envelope"></i></span></div></div>
  <div class="ad-card ad-stat"><div class="ad-stat__top"><div><div class="ad-stat__value"><?= $totalBlogs ?></div><div class="ad-stat__label">Published Posts</div></div><span class="ad-stat__icon"><i class="fas fa-newspaper"></i></span></div></div>
</div>

<!-- Quick actions -->
<div class="ad-card" style="margin-bottom:24px;">
  <div class="ad-card__head"><h2>Quick Actions</h2></div>
  <div class="ad-quick">
    <?php if (admin_can('blogs','create')): ?><a href="<?= e(admin_url('pages/blogs.php?action=new')) ?>"><i class="fas fa-pen"></i><span>Add Blog Post</span></a><?php endif; ?>
    <?php if (admin_can('portfolio','create')): ?><a href="<?= e(admin_url('pages/portfolio.php?action=new')) ?>"><i class="fas fa-briefcase"></i><span>Add Portfolio</span></a><?php endif; ?>
    <?php if (admin_can('services','create')): ?><a href="<?= e(admin_url('pages/services.php?action=new')) ?>"><i class="fas fa-layer-group"></i><span>Add Service</span></a><?php endif; ?>
    <?php if (admin_can('testimonials','create')): ?><a href="<?= e(admin_url('pages/testimonials.php?action=new')) ?>"><i class="fas fa-quote-left"></i><span>Add Testimonial</span></a><?php endif; ?>
    <?php if (admin_can('leads','view')): ?><a href="<?= e(admin_url('pages/leads.php')) ?>"><i class="fas fa-filter"></i><span>View Leads</span></a><?php endif; ?>
    <?php if (admin_can('settings','view')): ?><a href="<?= e(admin_url('pages/settings.php')) ?>"><i class="fas fa-gear"></i><span>Settings</span></a><?php endif; ?>
  </div>
</div>

<div class="ad-grid" style="grid-template-columns:repeat(auto-fit,minmax(min(100%,360px),1fr));">
  <!-- Recent leads -->
  <?php if (admin_can('leads','view')): ?>
  <div class="ad-card">
    <div class="ad-card__head"><h3>Recent Leads</h3><a href="<?= e(admin_url('pages/leads.php')) ?>" class="ad-btn ad-btn--sm">View all</a></div>
    <?php if (empty($recentLeads)): ?>
      <p style="color:var(--ad-muted);">No leads yet.</p>
    <?php else: ?>
    <div class="ad-table-wrap"><table class="ad-table">
      <thead><tr><th>Name</th><th>Service</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
        <?php foreach ($recentLeads as $l): ?>
          <tr><td><?= e($l['full_name']) ?></td><td><?= e($l['service_needed'] ?: '—') ?></td><td><?= ad_badge($l['status']) ?></td><td><?= e(fmt_date($l['created_at'], 'M j')) ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table></div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- Top blogs -->
  <div class="ad-card">
    <div class="ad-card__head"><h3>Most Viewed Posts</h3></div>
    <?php if (empty($topBlogs)): ?>
      <p style="color:var(--ad-muted);">No blog data yet.</p>
    <?php else: ?>
      <div class="ad-table-wrap"><table class="ad-table">
        <thead><tr><th>Title</th><th>Views</th></tr></thead>
        <tbody><?php foreach ($topBlogs as $b): ?><tr><td><a href="<?= e(url('blog/' . $b['slug'])) ?>" target="_blank"><?= e($b['title']) ?></a></td><td><?= number_format((int)$b['views']) ?></td></tr><?php endforeach; ?></tbody>
      </table></div>
    <?php endif; ?>
  </div>

  <!-- Activity -->
  <?php if (is_super_admin()): ?>
  <div class="ad-card">
    <div class="ad-card__head"><h3>Recent Activity</h3></div>
    <?php if (empty($recentActivity)): ?>
      <p style="color:var(--ad-muted);">No activity logged.</p>
    <?php else: ?>
      <ul style="display:flex;flex-direction:column;gap:10px;">
        <?php foreach ($recentActivity as $a): ?>
          <li style="font-size:0.85rem;color:var(--ad-muted);"><strong style="color:var(--ad-text);"><?= e($a['admin_name'] ?: 'System') ?></strong> — <?= e(str_replace('_',' ',$a['action'])) ?> <span style="color:var(--ad-dim);">· <?= e(fmt_date($a['created_at'], 'M j, H:i')) ?></span></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
