<?php
/**
 * Bharat SEO - Configuration Example
 * --------------------------------------------------
 * Copy this file to "config.php" and fill in your own values.
 * NEVER commit your real config.php to version control.
 */

// ---- Database ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'bharat_seo');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ---- Site ----
// Base URL WITHOUT trailing slash. Example: https://www.bharatseo.com
// For local development you may use: http://localhost/Bharat
define('SITE_URL', 'http://localhost');
define('SITE_NAME', 'Bharat SEO');

// ---- Environment ----
// 'development' shows errors on screen, 'production' hides them.
define('APP_ENV', 'development');

// ---- Security ----
// Change this to a long random string. Used for CSRF + password reset tokens.
define('APP_KEY', 'change-this-to-a-long-random-secret-string');

// ---- Paths (auto-detected, normally no need to change) ----
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_DIR', BASE_PATH . '/assets/uploads');
define('UPLOAD_URL', SITE_URL . '/assets/uploads');

// ---- Mail (fallback defaults; can be overridden in Admin > SMTP settings) ----
define('MAIL_FROM', 'no-reply@bharatseo.com');
define('MAIL_FROM_NAME', 'Bharat SEO');
