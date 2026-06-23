<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_permission('payments', 'view');
$self = admin_url('pages/payments.php');

if (is_post()) {
    admin_csrf_guard();
    $action = input('action');
    $id = ad_id();
    if ($action === 'delete' && is_super_admin()) {
        DB::delete('payments', 'id = :id', ['id' => $id]);
        flash_set('success', 'Record deleted.');
        redirect('admin/pages/payments.php');
    }
    if ($action === 'status' && admin_can('payments', 'edit')) {
        $st = input('status');
        if (in_array($st, ['pending','success','failed','refunded'], true)) {
            DB::update('payments', ['status' => $st], 'id = :id', ['id' => $id]);
            flash_set('success', 'Payment status updated.');
        }
        redirect('admin/pages/payments.php');
    }
}

$payments = DB::all("SELECT p.*, pk.name AS package_name FROM payments p LEFT JOIN pricing_packages pk ON pk.id=p.package_id ORDER BY p.created_at DESC LIMIT 100");
$razorpayOn = setting('razorpay_enabled', '0') === '1';
$payuOn = setting('payu_enabled', '0') === '1';

$pageTitle = 'Payments';
include __DIR__ . '/../includes/header.php';
?>
<div class="ad-toolbar">
  <span class="ad-badge ad-badge--<?= $razorpayOn ? 'success' : 'muted' ?>">Razorpay: <?= $razorpayOn ? 'Enabled' : 'Disabled' ?></span>
  <span class="ad-badge ad-badge--<?= $payuOn ? 'success' : 'muted' ?>">PayU: <?= $payuOn ? 'Enabled' : 'Disabled' ?></span>
  <?php if (admin_can('settings', 'view')): ?><a href="<?= e(admin_url('pages/settings.php?tab=payment')) ?>" class="ad-btn ad-btn--sm ad-spacer"><i class="fas fa-gear"></i> Gateway Settings</a><?php endif; ?>
</div>
<?php if (empty($payments)): ?>
  <div class="ad-card"><div class="ad-empty"><div class="ad-empty__icon">&#128179;</div><h2>No payments recorded</h2><p>Payment transactions will be listed here. Configure gateways in Settings and add payment links to packages.</p></div></div>
<?php else: ?>
  <div class="ad-table-wrap"><table class="ad-table">
    <thead><tr><th>Customer</th><th>Package</th><th>Amount</th><th>Gateway</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($payments as $p): ?>
        <tr>
          <td><?= e($p['customer_name'] ?: '—') ?><br><small><?= e($p['customer_email']) ?></small></td>
          <td><?= e($p['package_name'] ?: '—') ?></td>
          <td><?= e($p['currency'] . ' ' . number_format((float) $p['amount'], 2)) ?></td>
          <td><?= e($p['gateway'] ?: '—') ?></td>
          <td><?= ad_badge($p['status']) ?></td>
          <td><?= e(fmt_date($p['created_at'], 'M j')) ?></td>
          <td><div class="actions">
            <?php if (admin_can('payments', 'edit')): ?>
            <form method="post" action="<?= e($self) ?>" style="display:flex;gap:4px;"><?= csrf_field() ?><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <select name="status" style="padding:4px;border-radius:6px;background:var(--ad-bg);color:var(--ad-text);border:1px solid var(--ad-border);">
                <?php foreach (['pending','success','failed','refunded'] as $st): ?><option value="<?= $st ?>"<?= $p['status']===$st?' selected':'' ?>><?= ucfirst($st) ?></option><?php endforeach; ?>
              </select><button class="ad-btn ad-btn--sm"><i class="fas fa-save"></i></button></form>
            <?php endif; ?>
          </div></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
