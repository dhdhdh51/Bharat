<?php
/**
 * Bharat SEO - Application bootstrap
 * Loads config, core libs, starts session, sets error handling.
 */

// Resolve config
$configFile = __DIR__ . '/../config/config.php';
if (!is_file($configFile)) {
    $configFile = __DIR__ . '/../config/config.example.php';
}
require_once $configFile;

// Error handling based on environment
if (defined('APP_ENV') && APP_ENV === 'production') {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/logs/php_errors.log');

// Core libraries
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/seo.php';

// Global exception handler -> friendly page, never raw stack trace to user
set_exception_handler(function (Throwable $e) {
    log_error('Uncaught: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
    }
    if (defined('APP_ENV') && APP_ENV !== 'production') {
        echo '<pre style="padding:20px;font-family:monospace;">Error: '
            . htmlspecialchars($e->getMessage()) . "\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        $errPage = BASE_PATH . '/templates/error-500.php';
        if (is_file($errPage)) {
            include $errPage;
        } else {
            echo 'Something went wrong. Please try again later.';
        }
    }
    exit;
});

// Start a secure session
secure_session_start();
