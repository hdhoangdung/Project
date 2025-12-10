<?php
/**
 * Vehicle Insurance Management System - Base Configuration (Module 0A)
 * Foundation settings only - no business logic
 */

// Application Root Path
define('APP_ROOT', dirname(dirname(__FILE__)));
define('APP_URL', 'http://localhost');
define('DEBUG', true); // Set to false in production

// Database Configuration
define('DB_DRIVER', 'mysql');
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'qlbh');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Asset & Upload Paths
define('ASSETS_PATH', APP_ROOT . '/public/assets');
define('UPLOADS_PATH', ASSETS_PATH . '/uploads');
define('CSS_PATH', ASSETS_PATH . '/css');
define('JS_PATH', ASSETS_PATH . '/js');
define('LOGS_PATH', APP_ROOT . '/logs');
define('VIEWS_PATH', APP_ROOT . '/app/Views');

// Session Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Pagination
define('ITEMS_PER_PAGE', 20);

// Environment Setup
date_default_timezone_set('Asia/Ho_Chi_Minh');

error_reporting(E_ALL);
if (!DEBUG) {
    ini_set('display_errors', 0);
} else {
    ini_set('display_errors', 1);
}

// Return configuration array for reference (optional)
return [
    'app' => [
        'root' => APP_ROOT,
        'url' => APP_URL,
        'debug' => DEBUG,
    ],
    'database' => [
        'driver' => DB_DRIVER,
        'host' => DB_HOST,
        'port' => DB_PORT,
        'name' => DB_NAME,
        'user' => DB_USER,
        'pass' => DB_PASS,
        'charset' => DB_CHARSET,
    ],
    'paths' => [
        'assets' => ASSETS_PATH,
        'uploads' => UPLOADS_PATH,
        'logs' => LOGS_PATH,
        'views' => VIEWS_PATH,
    ],
];

