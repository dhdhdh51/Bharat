<?php
/**
 * Admin authentication + authorization.
 */
require_once __DIR__ . '/../../includes/bootstrap.php';

/** Return the currently logged-in admin row, or null. */
function current_admin(): ?array
{
    static $cached = false;
    static $admin = null;
    if ($cached) {
        return $admin;
    }
    $cached = true;
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    $admin = DB::row(
        'SELECT a.*, r.name AS role_name, r.slug AS role_slug FROM admins a LEFT JOIN roles r ON r.id = a.role_id WHERE a.id = ? AND a.status = "active"',
        [(int) $_SESSION['admin_id']]
    );
    return $admin;
}

/** Require a logged-in admin or redirect to login. */
function require_admin(): array
{
    $admin = current_admin();
    if (!$admin) {
        $_SESSION['admin_redirect'] = $_SERVER['REQUEST_URI'] ?? '';
        redirect('admin/login.php');
    }
    return $admin;
}

/** Role slug of current admin. */
function admin_role(): string
{
    $a = current_admin();
    return $a['role_slug'] ?? '';
}

function is_super_admin(): bool
{
    return admin_role() === 'super-admin';
}

/**
 * Permission map per role for modules.
 * Super admin gets everything. Others get scoped access.
 */
function admin_can(string $module, string $action = 'view'): bool
{
    $role = admin_role();
    if ($role === 'super-admin') {
        return true;
    }
    $matrix = [
        'seo-manager' => [
            'dashboard' => ['view'],
            'services' => ['view','create','edit','delete'],
            'service_categories' => ['view','create','edit','delete'],
            'seo' => ['view','create','edit','delete'],
            'redirects' => ['view','create','edit','delete'],
            'pages' => ['view','create','edit','delete'],
            'portfolio' => ['view','create','edit','delete'],
            'case_studies' => ['view','create','edit','delete'],
            'media' => ['view','create','edit','delete'],
            'settings' => ['view','edit'],
        ],
        'content-manager' => [
            'dashboard' => ['view'],
            'blogs' => ['view','create','edit','delete'],
            'blog_categories' => ['view','create','edit','delete'],
            'comments' => ['view','edit','delete'],
            'pages' => ['view','create','edit','delete'],
            'faqs' => ['view','create','edit','delete'],
            'testimonials' => ['view','create','edit','delete'],
            'team' => ['view','create','edit','delete'],
            'media' => ['view','create','edit','delete'],
            'portfolio' => ['view','create','edit','delete'],
            'case_studies' => ['view','create','edit','delete'],
        ],
        'sales-manager' => [
            'dashboard' => ['view'],
            'leads' => ['view','create','edit','delete'],
            'contacts' => ['view','edit','delete'],
            'newsletter' => ['view','delete'],
            'packages' => ['view','create','edit','delete'],
            'payments' => ['view','edit'],
        ],
    ];
    $allowed = $matrix[$role][$module] ?? [];
    return in_array($action, $allowed, true);
}

/** Guard a module page; show access-denied if not permitted. */
function require_permission(string $module, string $action = 'view'): void
{
    require_admin();
    if (!admin_can($module, $action)) {
        http_response_code(403);
        include BASE_PATH . '/admin/includes/denied.php';
        exit;
    }
}

/** Verify CSRF for admin POST or die with flash. */
function admin_csrf_guard(): void
{
    if (is_post() && !csrf_check()) {
        flash_set('error', 'Security token expired. Please try again.');
        redirect($_SERVER['REQUEST_URI'] ?? 'admin/');
    }
}

/** Attempt login. Returns [bool ok, string message]. */
function admin_login(string $email, string $password): array
{
    if (!rate_limit('admin_login_' . $email, 6, 600)) {
        return [false, 'Too many login attempts. Please wait 10 minutes and try again.'];
    }
    $admin = DB::row('SELECT * FROM admins WHERE email = ?', [$email]);
    if (!$admin) {
        return [false, 'Invalid email or password.'];
    }
    if ($admin['status'] !== 'active') {
        return [false, 'This account is inactive. Contact your administrator.'];
    }
    if (!empty($admin['locked_until']) && strtotime($admin['locked_until']) > time()) {
        return [false, 'Account temporarily locked due to failed attempts. Try again later.'];
    }
    if (!password_verify($password, $admin['password'])) {
        $attempts = (int) $admin['failed_attempts'] + 1;
        $update = ['failed_attempts' => $attempts];
        if ($attempts >= 5) {
            $update['locked_until'] = date('Y-m-d H:i:s', time() + 900); // 15 min lock
        }
        DB::update('admins', $update, 'id = :id', ['id' => $admin['id']]);
        return [false, 'Invalid email or password.'];
    }
    // Success
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['_created'] = time();
    DB::update('admins', [
        'failed_attempts' => 0,
        'locked_until'    => null,
        'last_login'      => date('Y-m-d H:i:s'),
    ], 'id = :id', ['id' => $admin['id']]);
    // Rehash if needed
    if (password_needs_rehash($admin['password'], PASSWORD_DEFAULT)) {
        DB::update('admins', ['password' => password_hash($password, PASSWORD_DEFAULT)], 'id = :id', ['id' => $admin['id']]);
    }
    activity_log((int) $admin['id'], 'login', 'Admin logged in');
    return [true, 'Welcome back!'];
}

function admin_logout(): void
{
    $a = current_admin();
    if ($a) {
        activity_log((int) $a['id'], 'logout', 'Admin logged out');
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** URL helper for admin pages. */
function admin_url(string $path = ''): string
{
    return url('admin/' . ltrim($path, '/'));
}
