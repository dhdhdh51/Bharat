<?php
/**
 * Bharat SEO - Core helper functions
 */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }

/* ----------------------------------------------------------
 * Error logging (to protected /logs/app.log)
 * -------------------------------------------------------- */
function log_error(string $message): void
{
    $dir = BASE_PATH . '/logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    @file_put_contents($dir . '/app.log', $line, FILE_APPEND | LOCK_EX);
}

/* ----------------------------------------------------------
 * Output escaping
 * -------------------------------------------------------- */
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Escape an attribute value. */
function eattr($value): string
{
    return e($value);
}

/* ----------------------------------------------------------
 * Settings cache (key/value store)
 * -------------------------------------------------------- */
function settings_all(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = [];
    foreach (DB::all('SELECT setting_key, setting_value FROM settings') as $r) {
        $cache[$r['setting_key']] = $r['setting_value'];
    }
    return $cache;
}

function setting(string $key, $default = ''): string
{
    $all = settings_all();
    $val = $all[$key] ?? null;
    return ($val === null || $val === '') ? (string) $default : (string) $val;
}

function setting_raw(string $key, $default = '')
{
    $all = settings_all();
    return $all[$key] ?? $default;
}

/* ----------------------------------------------------------
 * URL helpers
 * -------------------------------------------------------- */
function url(string $path = ''): string
{
    $base = rtrim(SITE_URL, '/');
    $path = ltrim($path, '/');
    return $path === '' ? $base . '/' : $base . '/' . $path;
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function upload_url(string $path): string
{
    if ($path === '' || $path === null) {
        return '';
    }
    // Allow absolute URLs to pass through
    if (preg_match('~^https?://~i', $path)) {
        return $path;
    }
    return rtrim(SITE_URL, '/') . '/assets/uploads/' . ltrim($path, '/');
}

function current_url(): string
{
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    return $scheme . '://' . $host . $uri;
}

function redirect(string $path, int $code = 302): void
{
    $location = preg_match('~^https?://~i', $path) ? $path : url($path);
    header('Location: ' . $location, true, $code);
    exit;
}

/* ----------------------------------------------------------
 * String helpers
 * -------------------------------------------------------- */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = trim($text, '-');
    return $text !== '' ? $text : 'item-' . substr(md5((string) microtime(true)), 0, 6);
}

function str_excerpt(?string $text, int $length = 160): string
{
    $text = trim(strip_tags((string) $text));
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length - 1) . '…';
}

/** Split a pipe-delimited field into a clean array. */
function pipe_list(?string $value): array
{
    if ($value === null || trim($value) === '') {
        return [];
    }
    return array_values(array_filter(array_map('trim', explode('|', $value)), fn($v) => $v !== ''));
}

/** Format a date safely. */
function fmt_date(?string $date, string $format = 'M j, Y'): string
{
    if (!$date || $date === '0000-00-00 00:00:00') {
        return '';
    }
    $ts = strtotime($date);
    return $ts ? date($format, $ts) : '';
}

/* ----------------------------------------------------------
 * Image fallback helper - never show a broken image
 * -------------------------------------------------------- */
function img(?string $path, string $type = 'general'): string
{
    if ($path && trim($path) !== '') {
        return upload_url($path);
    }
    return placeholder_image($type);
}

function placeholder_image(string $type = 'general'): string
{
    // Inline SVG branded placeholder (no external request, never breaks)
    $labels = [
        'blog'      => 'Bharat SEO',
        'portfolio' => 'Project',
        'service'   => 'Service',
        'team'      => 'Team',
        'general'   => 'Bharat SEO',
    ];
    $label = $labels[$type] ?? 'Bharat SEO';
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="500" viewBox="0 0 800 500">'
        . '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">'
        . '<stop offset="0" stop-color="#1b2440"/><stop offset="0.5" stop-color="#3a2a6d"/><stop offset="1" stop-color="#0c1022"/>'
        . '</linearGradient></defs>'
        . '<rect width="800" height="500" fill="url(#g)"/>'
        . '<circle cx="400" cy="250" r="120" fill="none" stroke="#4f8cff" stroke-opacity="0.25" stroke-width="2"/>'
        . '<circle cx="400" cy="250" r="80" fill="none" stroke="#9b5cff" stroke-opacity="0.3" stroke-width="2"/>'
        . '<text x="400" y="262" font-family="Arial, sans-serif" font-size="34" fill="#cfd8ff" text-anchor="middle" font-weight="700">' . htmlspecialchars($label) . '</text>'
        . '</svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

/* ----------------------------------------------------------
 * Flash messages
 * -------------------------------------------------------- */
function flash_set(string $type, string $message): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function flash_get(): array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return [];
    }
    $flash = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $flash;
}

/* ----------------------------------------------------------
 * Request helpers
 * -------------------------------------------------------- */
function input(string $key, $default = '')
{
    $val = $_POST[$key] ?? $_GET[$key] ?? $default;
    if (is_string($val)) {
        $val = trim($val);
    }
    return $val;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function client_ip(): string
{
    return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function is_json_request(): bool
{
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $xrw = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    return str_contains($accept, 'application/json') || strtolower($xrw) === 'xmlhttprequest';
}

function json_response(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/* ----------------------------------------------------------
 * Validation helpers
 * -------------------------------------------------------- */
function valid_email(?string $email): bool
{
    return $email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function valid_phone(?string $phone): bool
{
    if (!$phone) {
        return false;
    }
    $digits = preg_replace('/\D+/', '', $phone);
    return strlen($digits) >= 7 && strlen($digits) <= 15;
}

/* ----------------------------------------------------------
 * Social / Contact helpers
 * -------------------------------------------------------- */
function whatsapp_link(?string $prefill = null): string
{
    $number = preg_replace('/\D+/', '', setting('whatsapp_number', ''));
    if ($number === '') {
        return '#';
    }
    $msg = $prefill ?? setting('whatsapp_message', 'Hi Bharat SEO');
    return 'https://wa.me/' . $number . '?text=' . rawurlencode($msg);
}

function call_link(): string
{
    $phone = setting('contact_phone', '');
    return $phone !== '' ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : '#';
}

/* ----------------------------------------------------------
 * Pagination helper
 * -------------------------------------------------------- */
function paginate(int $total, int $perPage, int $current, string $baseUrl): string
{
    $pages = (int) ceil($total / max(1, $perPage));
    if ($pages <= 1) {
        return '';
    }
    $sep = str_contains($baseUrl, '?') ? '&' : '?';
    $html = '<nav class="pagination" aria-label="Pagination"><ul>';
    $prevDisabled = $current <= 1 ? ' disabled' : '';
    $html .= '<li class="' . trim($prevDisabled) . '"><a href="' . ($current > 1 ? e($baseUrl . $sep . 'page=' . ($current - 1)) : '#') . '" aria-label="Previous">&laquo;</a></li>';
    for ($i = 1; $i <= $pages; $i++) {
        if ($i == 1 || $i == $pages || abs($i - $current) <= 2) {
            $active = $i === $current ? ' class="active"' : '';
            $html .= '<li' . $active . '><a href="' . e($baseUrl . $sep . 'page=' . $i) . '">' . $i . '</a></li>';
        } elseif (abs($i - $current) === 3) {
            $html .= '<li class="ellipsis"><span>…</span></li>';
        }
    }
    $html .= '<li class="' . ($current >= $pages ? 'disabled' : '') . '"><a href="' . ($current < $pages ? e($baseUrl . $sep . 'page=' . ($current + 1)) : '#') . '" aria-label="Next">&raquo;</a></li>';
    $html .= '</ul></nav>';
    return $html;
}

/* ----------------------------------------------------------
 * Maintenance mode
 * -------------------------------------------------------- */
function is_maintenance(): bool
{
    return setting('maintenance_mode', '0') === '1';
}

/* ----------------------------------------------------------
 * Activity log
 * -------------------------------------------------------- */
function activity_log(?int $adminId, string $action, string $details = ''): void
{
    DB::insert('activity_logs', [
        'admin_id'   => $adminId,
        'action'     => $action,
        'details'    => mb_substr($details, 0, 500),
        'ip_address' => client_ip(),
    ]);
}

/* ----------------------------------------------------------
 * Simple mailer (SMTP via socket if enabled, else mail())
 * -------------------------------------------------------- */
function send_mail(string $to, string $subject, string $htmlBody): bool
{
    $fromEmail = setting('smtp_from', defined('MAIL_FROM') ? MAIL_FROM : 'no-reply@localhost');
    $fromName  = setting('smtp_from_name', defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'Bharat SEO');

    if (setting('smtp_enabled', '0') === '1' && setting('smtp_host') !== '') {
        $sent = smtp_send($to, $subject, $htmlBody, $fromEmail, $fromName);
        if ($sent) {
            return true;
        }
        // fall through to mail() if SMTP failed
    }

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $fromName . ' <' . $fromEmail . '>',
    ];
    return @mail($to, $subject, $htmlBody, implode("\r\n", $headers));
}

/** Minimal SMTP client (supports STARTTLS / SSL, AUTH LOGIN). */
function smtp_send(string $to, string $subject, string $body, string $fromEmail, string $fromName): bool
{
    $host = setting('smtp_host');
    $port = (int) (setting('smtp_port', '587'));
    $user = setting('smtp_user');
    $pass = setting('smtp_pass');
    $secure = strtolower(setting('smtp_secure', 'tls'));

    if ($host === '') {
        return false;
    }

    $transport = ($secure === 'ssl') ? 'ssl://' . $host : $host;

    try {
        $fp = @fsockopen($transport, $port, $errno, $errstr, 12);
        if (!$fp) {
            log_error("SMTP connect failed: $errstr ($errno)");
            return false;
        }
        $read = function () use ($fp) { return fgets($fp, 512); };
        $cmd  = function (string $c) use ($fp, $read) { fputs($fp, $c . "\r\n"); return $read(); };

        $read();
        $cmd('EHLO ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        if ($secure === 'tls') {
            $cmd('STARTTLS');
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($fp);
                return false;
            }
            $cmd('EHLO ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        }
        if ($user !== '') {
            $cmd('AUTH LOGIN');
            $cmd(base64_encode($user));
            $cmd(base64_encode($pass));
        }
        $cmd('MAIL FROM:<' . $fromEmail . '>');
        $cmd('RCPT TO:<' . $to . '>');
        $cmd('DATA');
        $headers = "From: $fromName <$fromEmail>\r\n";
        $headers .= "To: <$to>\r\n";
        $headers .= "Subject: $subject\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        fputs($fp, $headers . "\r\n" . $body . "\r\n.\r\n");
        $read();
        $cmd('QUIT');
        fclose($fp);
        return true;
    } catch (Throwable $e) {
        log_error('SMTP error: ' . $e->getMessage());
        return false;
    }
}

/** Render a small "no data" state for empty sections. */
function empty_state(string $message = 'Nothing to show here yet.', string $icon = 'inbox'): string
{
    return '<div class="empty-state"><div class="empty-state__icon" aria-hidden="true">&#128229;</div>'
        . '<p>' . e($message) . '</p></div>';
}
