<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_permission('settings', 'view');
$self = admin_url('pages/settings.php');

// Define which setting keys belong to each tab and their input type
$schema = [
    'general' => [
        'title' => 'General',
        'fields' => [
            'site_name' => ['Site Name', 'text'],
            'tagline' => ['Tagline', 'text'],
            'logo' => ['Logo', 'image'],
            'favicon' => ['Favicon', 'image'],
            'footer_about' => ['Footer About Text', 'textarea'],
            'copyright_text' => ['Copyright Text', 'text'],
            'theme_default' => ['Default Theme', 'select:dark=Dark,light=Light'],
            'announcement_enabled' => ['Show Announcement Bar', 'bool'],
            'announcement_text' => ['Announcement Text', 'text'],
            'announcement_link' => ['Announcement Link', 'text'],
            'maintenance_mode' => ['Maintenance Mode', 'bool'],
            'maintenance_message' => ['Maintenance Message', 'textarea'],
        ],
    ],
    'contact' => [
        'title' => 'Contact',
        'fields' => [
            'contact_email' => ['Contact Email', 'text'],
            'contact_phone' => ['Contact Phone', 'text'],
            'whatsapp_number' => ['WhatsApp Number (digits only, with country code)', 'text'],
            'whatsapp_message' => ['Default WhatsApp Message', 'text'],
            'address' => ['Address', 'textarea'],
            'map_embed' => ['Google Maps Embed Code', 'textarea'],
            'booking_url' => ['Calendar Booking URL', 'text'],
        ],
    ],
    'seo' => [
        'title' => 'SEO & Analytics',
        'fields' => [
            'meta_title' => ['Default Meta Title', 'text'],
            'meta_description' => ['Default Meta Description', 'textarea'],
            'meta_keywords' => ['Default Meta Keywords', 'text'],
            'og_image' => ['Default OG Image', 'image'],
            'google_analytics_id' => ['Google Analytics ID', 'text'],
            'google_search_console' => ['Search Console Verification', 'text'],
            'facebook_pixel' => ['Facebook / Meta Pixel ID', 'text'],
            'robots_txt' => ['Custom robots.txt (optional)', 'textarea'],
        ],
    ],
    'integrations' => [
        'title' => 'Custom Scripts',
        'fields' => [
            'custom_head' => ['Custom <head> Code', 'textarea'],
            'custom_body' => ['Custom Code after <body>', 'textarea'],
            'custom_footer' => ['Custom Footer Code', 'textarea'],
        ],
    ],
    'smtp' => [
        'title' => 'Email / SMTP',
        'fields' => [
            'smtp_enabled' => ['Enable SMTP', 'bool'],
            'smtp_host' => ['SMTP Host', 'text'],
            'smtp_port' => ['SMTP Port', 'text'],
            'smtp_user' => ['SMTP Username', 'text'],
            'smtp_pass' => ['SMTP Password', 'password'],
            'smtp_secure' => ['Encryption', 'select:tls=TLS,ssl=SSL,none=None'],
            'smtp_from' => ['From Email', 'text'],
            'smtp_from_name' => ['From Name', 'text'],
            'notify_email' => ['Lead Notification Email', 'text'],
        ],
    ],
    'payment' => [
        'title' => 'Payments',
        'fields' => [
            'razorpay_enabled' => ['Enable Razorpay', 'bool'],
            'razorpay_key' => ['Razorpay Key ID', 'text'],
            'razorpay_secret' => ['Razorpay Secret', 'password'],
            'payu_enabled' => ['Enable PayU', 'bool'],
            'payu_key' => ['PayU Merchant Key', 'text'],
            'payu_salt' => ['PayU Salt', 'password'],
        ],
    ],
];

$tab = input('tab', 'general');
if (!isset($schema[$tab])) $tab = 'general';

if (is_post()) {
    admin_csrf_guard();
    require_permission('settings', 'edit');
    $postTab = input('tab', 'general');
    $fields = $schema[$postTab]['fields'] ?? [];
    foreach ($fields as $key => $def) {
        $type = $def[1];
        if ($type === 'image') {
            $val = ad_handle_upload($key, 'settings', (string) input($key . '_existing', ''));
        } elseif ($type === 'bool') {
            $val = input($key) ? '1' : '0';
        } elseif (str_starts_with($type, 'select') || $type === 'text' || $type === 'password') {
            $val = clean_text((string) input($key), 5000);
        } else { // textarea (allow raw for scripts/map embed)
            $val = (string) ($_POST[$key] ?? '');
        }
        // upsert
        $exists = DB::value('SELECT id FROM settings WHERE setting_key = ?', [$key]);
        if ($exists) {
            DB::update('settings', ['setting_value' => $val], 'setting_key = :k', ['k' => $key]);
        } else {
            DB::insert('settings', ['group_name' => $postTab, 'setting_key' => $key, 'setting_value' => $val]);
        }
    }
    activity_log((int) $_SESSION['admin_id'], 'update_settings', 'Tab: ' . $postTab);
    flash_set('success', 'Settings saved.');
    redirect('admin/pages/settings.php?tab=' . $postTab);
}

$pageTitle = 'Site Settings';
include __DIR__ . '/../includes/header.php';
?>
<div class="ad-tabs">
  <?php foreach ($schema as $k => $s): ?>
    <a href="<?= e($self . '?tab=' . $k) ?>"<?= $tab === $k ? ' class="active"' : '' ?>><?= e($s['title']) ?></a>
  <?php endforeach; ?>
</div>
<div class="ad-card">
  <form method="post" action="<?= e($self) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="tab" value="<?= e($tab) ?>">
    <div class="ad-form-grid">
      <?php foreach ($schema[$tab]['fields'] as $key => $def):
        [$label, $type] = [$def[0], $def[1]];
        $val = setting_raw($key, '');
        $full = in_array($type, ['textarea','image']) ? ' full' : '';
      ?>
        <div class="ad-field<?= $full ?>">
          <?php if ($type !== 'bool'): ?><label><?= e($label) ?></label><?php endif; ?>
          <?php if ($type === 'textarea'): ?>
            <textarea name="<?= e($key) ?>" <?= in_array($key,['custom_head','custom_body','custom_footer','map_embed']) ? 'style="font-family:monospace;"' : '' ?>><?= e($val) ?></textarea>
          <?php elseif ($type === 'bool'): ?>
            <label class="ad-checkbox"><input type="checkbox" name="<?= e($key) ?>" value="1"<?= $val === '1' ? ' checked' : '' ?>> <?= e($label) ?></label>
          <?php elseif ($type === 'password'): ?>
            <input type="password" name="<?= e($key) ?>" value="<?= e($val) ?>" autocomplete="new-password">
          <?php elseif ($type === 'image'): ?>
            <?php if ($val): ?><img src="<?= e(upload_url($val)) ?>" class="ad-thumb" style="width:90px;height:90px;margin-bottom:8px;" alt=""><?php endif; ?>
            <input type="hidden" name="<?= e($key) ?>_existing" value="<?= e($val) ?>">
            <input type="file" name="<?= e($key) ?>" accept="image/*">
          <?php elseif (str_starts_with($type, 'select')): ?>
            <?php $opts = []; foreach (explode(',', substr($type, 7)) as $pair) { [$ov,$ol] = array_pad(explode('=', $pair), 2, $pair); $opts[$ov] = $ol; } ?>
            <select name="<?= e($key) ?>"><?php foreach ($opts as $ov => $ol): ?><option value="<?= e($ov) ?>"<?= $val === $ov ? ' selected' : '' ?>><?= e($ol) ?></option><?php endforeach; ?></select>
          <?php else: ?>
            <input type="text" name="<?= e($key) ?>" value="<?= e($val) ?>">
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if (admin_can('settings', 'edit')): ?>
    <div style="margin-top:18px;"><button class="ad-btn ad-btn--primary"><i class="fas fa-floppy-disk"></i> Save Settings</button></div>
    <?php endif; ?>
  </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
