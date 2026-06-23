<?php
/**
 * Bharat SEO - Security helpers
 * CSRF, honeypot, rate limiting, sanitization, secure file uploads.
 */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }

/* ----------------------------------------------------------
 * Session bootstrap with secure cookie params
 * -------------------------------------------------------- */
function secure_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('BHARATSEOSESS');
    session_start();
    if (!isset($_SESSION['_created'])) {
        $_SESSION['_created'] = time();
    } elseif (time() - $_SESSION['_created'] > 1800) {
        // Rotate session id every 30 min to mitigate fixation
        session_regenerate_id(true);
        $_SESSION['_created'] = time();
    }
}

/* ----------------------------------------------------------
 * CSRF protection
 * -------------------------------------------------------- */
function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        secure_session_start();
    }
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify(?string $token): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        secure_session_start();
    }
    $stored = $_SESSION['_csrf'] ?? '';
    return $token !== null && $stored !== '' && hash_equals($stored, $token);
}

/** Throw / respond if CSRF fails (use in form handlers). */
function csrf_check(): bool
{
    return csrf_verify($_POST['_csrf'] ?? null);
}

/* ----------------------------------------------------------
 * Honeypot anti-spam
 * Add hp_field() inside forms; check with honeypot_ok().
 * -------------------------------------------------------- */
function honeypot_field(): string
{
    // Visually hidden; bots fill it, humans don't.
    return '<div style="position:absolute;left:-9999px;top:-9999px;" aria-hidden="true">'
        . '<label>Leave this field empty<input type="text" name="website_hp" tabindex="-1" autocomplete="off"></label>'
        . '<input type="hidden" name="form_ts" value="' . time() . '">'
        . '</div>';
}

function honeypot_ok(): bool
{
    // If the honeypot field is filled, it's a bot.
    if (!empty($_POST['website_hp'])) {
        return false;
    }
    // Submitted too fast (< 2s) is suspicious.
    $ts = (int) ($_POST['form_ts'] ?? 0);
    if ($ts > 0 && (time() - $ts) < 2) {
        return false;
    }
    return true;
}

/* ----------------------------------------------------------
 * Rate limiting (session + file based, no extra deps)
 * -------------------------------------------------------- */
function rate_limit(string $key, int $maxAttempts = 5, int $windowSeconds = 60): bool
{
    $dir = BASE_PATH . '/logs/rate';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $file = $dir . '/' . md5($key . '|' . client_ip()) . '.json';
    $now = time();
    $data = ['count' => 0, 'start' => $now];
    if (is_file($file)) {
        $raw = json_decode((string) @file_get_contents($file), true);
        if (is_array($raw)) {
            $data = $raw;
        }
    }
    if ($now - ($data['start'] ?? $now) > $windowSeconds) {
        $data = ['count' => 0, 'start' => $now];
    }
    $data['count'] = ($data['count'] ?? 0) + 1;
    @file_put_contents($file, json_encode($data), LOCK_EX);
    return $data['count'] <= $maxAttempts;
}

/* ----------------------------------------------------------
 * Sanitization
 * -------------------------------------------------------- */
function clean(?string $value): string
{
    return trim(filter_var((string) ($value ?? ''), FILTER_UNSAFE_RAW, FILTER_FLAG_NO_ENCODE_QUOTES));
}

function clean_text(?string $value, int $maxLen = 5000): string
{
    $value = strip_tags((string) ($value ?? ''));
    return mb_substr(trim($value), 0, $maxLen);
}

/** Allow a limited safe subset of HTML (for rich admin content). */
function clean_html(?string $value): string
{
    $allowed = '<p><br><b><strong><i><em><u><ul><ol><li><a><h1><h2><h3><h4><h5><h6>'
        . '<blockquote><img><figure><figcaption><table><thead><tbody><tr><th><td><span><div><pre><code><hr>';
    $value = strip_tags((string) ($value ?? ''), $allowed);
    // Remove on* event handlers and javascript: URLs
    $value = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $value);
    $value = preg_replace('/javascript\s*:/i', '', $value);
    return $value;
}

/* ----------------------------------------------------------
 * Secure file upload
 * -------------------------------------------------------- */
function upload_file(array $file, string $subdir = 'media', array $opts = []): array
{
    $maxSize = $opts['max_size'] ?? (5 * 1024 * 1024); // 5MB
    $allowedExt = $opts['ext'] ?? ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    $allowedMime = $opts['mime'] ?? [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
    ];

    if (!isset($file['error']) || is_array($file['error'])) {
        return ['ok' => false, 'error' => 'Invalid upload.'];
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'error' => 'No file selected.', 'empty' => true];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Upload failed (code ' . $file['error'] . ').'];
    }
    if ($file['size'] > $maxSize) {
        return ['ok' => false, 'error' => 'File too large. Max ' . round($maxSize / 1048576, 1) . 'MB.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return ['ok' => false, 'error' => 'File type not allowed.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowedMime, true)) {
        return ['ok' => false, 'error' => 'Invalid file content (' . e($mime) . ').'];
    }

    $targetDir = UPLOAD_DIR . '/' . trim($subdir, '/');
    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0755, true);
    }
    $safeName = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = $targetDir . '/' . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        // Fallback for CLI/testing environments
        if (!@rename($file['tmp_name'], $dest)) {
            return ['ok' => false, 'error' => 'Could not save file.'];
        }
    }
    @chmod($dest, 0644);

    $relative = trim($subdir, '/') . '/' . $safeName;
    return ['ok' => true, 'path' => $relative, 'name' => $safeName, 'mime' => $mime, 'size' => $file['size']];
}
