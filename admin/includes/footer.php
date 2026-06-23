<?php
/** Admin layout footer. */
if (!defined('BASE_PATH')) { exit('Forbidden'); }
?>
    </div><!-- /.ad-content -->
    <footer style="padding:16px 24px;border-top:1px solid var(--ad-border);color:var(--ad-muted);font-size:0.8rem;display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;">
      <span>&copy; <?= date('Y') ?> <?= e(setting('site_name', SITE_NAME)) ?> Admin Panel</span>
      <span>v1.0</span>
    </footer>
  </div><!-- /.ad-main -->
</div><!-- /.ad-layout -->
<script src="<?= e(admin_url('assets/js/admin.js')) ?>?v=1.0"></script>
</body>
</html>
