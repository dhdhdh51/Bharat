<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_admin();
if (!is_super_admin()) {
    http_response_code(403);
    include __DIR__ . '/../includes/denied.php';
    exit;
}
$self = admin_url('pages/users.php');
$roles = DB::all("SELECT id, name FROM roles ORDER BY id");
$roleMap = [];
foreach ($roles as $r) { $roleMap[$r['id']] = $r['name']; }

if (is_post()) {
    admin_csrf_guard();
    $action = input('action');
    $id = ad_id();

    if ($action === 'delete') {
        if ($id === (int) $_SESSION['admin_id']) {
            flash_set('error', 'You cannot delete your own account.');
        } else {
            DB::delete('admins', 'id = :id', ['id' => $id]);
            flash_set('success', 'User deleted.');
        }
        redirect('admin/pages/users.php');
    }

    // Save
    $name = clean_text(input('name'), 120);
    $email = clean_text(input('email'), 150);
    $roleId = (int) input('role_id', 4);
    $status = input('status') === 'inactive' ? 'inactive' : 'active';
    $password = (string) input('password');

    $errors = [];
    if ($name === '') $errors[] = 'Name is required.';
    if (!valid_email($email)) $errors[] = 'Valid email is required.';
    $dupe = DB::value('SELECT id FROM admins WHERE email = ?' . ($id ? ' AND id != ' . $id : ''), [$email]);
    if ($dupe) $errors[] = 'Email already in use.';
    if (!$id && strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        redirect('admin/pages/users.php?action=' . ($id ? 'edit&id=' . $id : 'new'));
    }

    $data = ['name' => $name, 'email' => $email, 'role_id' => $roleId, 'status' => $status];
    if ($password !== '') { $data['password'] = password_hash($password, PASSWORD_DEFAULT); }

    if ($id) {
        DB::update('admins', $data, 'id = :id', ['id' => $id]);
        flash_set('success', 'User updated.');
    } else {
        DB::insert('admins', $data);
        flash_set('success', 'User created.');
    }
    activity_log((int) $_SESSION['admin_id'], $id ? 'update_user' : 'create_user', $email);
    redirect('admin/pages/users.php');
}

$action = input('action');
if ($action === 'new' || $action === 'edit') {
    $row = $action === 'edit' ? DB::row('SELECT * FROM admins WHERE id = ?', [ad_id()]) : null;
    if ($action === 'edit' && !$row) { flash_set('error', 'User not found.'); redirect('admin/pages/users.php'); }
    $pageTitle = ($row ? 'Edit' : 'Add') . ' User';
    include __DIR__ . '/../includes/header.php';
    ?>
    <a href="<?= e($self) ?>" class="ad-btn ad-btn--sm ad-btn--ghost" style="margin-bottom:16px;"><i class="fas fa-arrow-left"></i> Back</a>
    <div class="ad-card"><form method="post" action="<?= e($self) ?>">
      <?= csrf_field() ?><input type="hidden" name="action" value="save"><?php if ($row): ?><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><?php endif; ?>
      <div class="ad-form-grid">
        <div class="ad-field"><label>Name <span class="req">*</span></label><input type="text" name="name" value="<?= e($row['name'] ?? '') ?>" required></div>
        <div class="ad-field"><label>Email <span class="req">*</span></label><input type="email" name="email" value="<?= e($row['email'] ?? '') ?>" required></div>
        <div class="ad-field"><label>Role</label><select name="role_id"><?php foreach ($roleMap as $rid => $rn): ?><option value="<?= (int)$rid ?>"<?= (int)($row['role_id'] ?? 4) === (int)$rid ? ' selected' : '' ?>><?= e($rn) ?></option><?php endforeach; ?></select></div>
        <div class="ad-field"><label>Status</label><select name="status"><option value="active"<?= ($row['status'] ?? 'active') === 'active' ? ' selected' : '' ?>>Active</option><option value="inactive"<?= ($row['status'] ?? '') === 'inactive' ? ' selected' : '' ?>>Inactive</option></select></div>
        <div class="ad-field full"><label>Password <?= $row ? '(leave blank to keep current)' : '<span class="req">*</span>' ?></label><input type="password" name="password" autocomplete="new-password"><small>Minimum 8 characters.</small></div>
      </div>
      <div style="margin-top:18px;"><button class="ad-btn ad-btn--primary">Save User</button></div>
    </form></div>
    <?php
    include __DIR__ . '/../includes/footer.php';
    return;
}

$users = DB::all("SELECT a.*, r.name AS role_name FROM admins a LEFT JOIN roles r ON r.id=a.role_id ORDER BY a.id");
$pageTitle = 'Users & Roles';
include __DIR__ . '/../includes/header.php';
?>
<div class="ad-toolbar"><a href="<?= e($self . '?action=new') ?>" class="ad-btn ad-btn--primary"><i class="fas fa-plus"></i> Add User</a></div>
<div class="ad-table-wrap"><table class="ad-table">
  <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th>Actions</th></tr></thead>
  <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><strong style="color:var(--ad-text);"><?= e($u['name']) ?></strong></td>
        <td><?= e($u['email']) ?></td>
        <td><?= e($u['role_name']) ?></td>
        <td><?= ad_badge($u['status']) ?></td>
        <td><?= e($u['last_login'] ? fmt_date($u['last_login'], 'M j, H:i') : '—') ?></td>
        <td><div class="actions">
          <a href="<?= e($self . '?action=edit&id=' . (int)$u['id']) ?>" class="ad-btn ad-btn--sm"><i class="fas fa-pen"></i></a>
          <?php if ((int)$u['id'] !== (int)$_SESSION['admin_id']): ?><?= ad_delete_form($self, (int)$u['id']) ?><?php endif; ?>
        </div></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table></div>
<div class="ad-card" style="margin-top:18px;">
  <div class="ad-card__head"><h3>Roles & Permissions</h3></div>
  <p style="color:var(--ad-muted);font-size:0.88rem;">Super Admin has full access. SEO Manager manages services, SEO, redirects &amp; pages. Content Manager manages blogs, pages, FAQs, testimonials, team &amp; media. Sales Manager manages leads, contacts, packages &amp; payments.</p>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
