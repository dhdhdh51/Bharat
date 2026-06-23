<?php
/**
 * Shared init for API endpoints.
 * Provides bootstrap + a helper to enforce POST/CSRF/honeypot/rate-limit.
 */
require_once __DIR__ . '/../includes/bootstrap.php';

/**
 * Guard a form endpoint. On failure, emits a JSON error and exits.
 * Returns nothing on success.
 */
function api_guard(string $rateKey, int $maxAttempts = 8, int $window = 120): void
{
    if (!is_post()) {
        json_response(['success' => false, 'message' => 'Invalid request method.'], 405);
    }
    if (!DB::ok()) {
        json_response(['success' => false, 'message' => 'Service unavailable. Please try again shortly.'], 503);
    }
    if (!csrf_check()) {
        json_response(['success' => false, 'message' => 'Your session expired. Please refresh the page and try again.'], 419);
    }
    if (!honeypot_ok()) {
        // Pretend success to fool bots, but do nothing.
        json_response(['success' => true, 'message' => 'Thank you!']);
    }
    if (!rate_limit($rateKey, $maxAttempts, $window)) {
        json_response(['success' => false, 'message' => 'Too many attempts. Please wait a minute and try again.'], 429);
    }
}

/** Build a branded HTML email wrapper. */
function mail_template(string $title, string $bodyHtml): string
{
    $name = e(setting('site_name', SITE_NAME));
    return '<div style="font-family:Arial,sans-serif;max-width:600px;margin:auto;background:#0a0e1a;color:#e8ecf6;border-radius:12px;overflow:hidden;border:1px solid #1d2436;">'
        . '<div style="background:linear-gradient(135deg,#4f8cff,#9b5cff);padding:20px 24px;font-size:20px;font-weight:bold;color:#fff;">' . $name . '</div>'
        . '<div style="padding:24px;">'
        . '<h2 style="color:#fff;margin-top:0;">' . e($title) . '</h2>'
        . $bodyHtml
        . '</div>'
        . '<div style="padding:16px 24px;border-top:1px solid #1d2436;font-size:12px;color:#9aa6c4;">This is an automated message from ' . $name . '.</div>'
        . '</div>';
}
