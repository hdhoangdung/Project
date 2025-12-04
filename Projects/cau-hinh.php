<?php

declare(strict_types=1);

/**
 * Tệp cấu hình và bootstrap ứng dụng.
 * Thiết lập hằng số, timezone, autoload và error handler.
 */

defined('APP_ACCESS') or define('APP_ACCESS', true);

// ===== THIẾT LẬP HỆ THỐNG =====
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'qlbh_xe');
define('DB_CHARSET', 'utf8mb4');

define('SESSION_TIMEOUT', 1800);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900);

define('APP_NAME', 'Hệ thống Quản lý Bảo hiểm Xe');
define('APP_VERSION', '3.0');
define('DEBUG_MODE', true);

date_default_timezone_set('Asia/Ho_Chi_Minh');

// ===== AUTOLOAD & KHOI DONG =====
require_once __DIR__ . '/ung-dung/HoTro/TuDongTai.php';

use UngDung\DichVu\PhienDichVu;

PhienDichVu::batDau();

// ===== ERROR HANDLER =====
set_error_handler(static function (int $errno, string $errstr, string $errfile, int $errline): void {
    error_log("[$errno] $errstr in $errfile:$errline");
    if (DEBUG_MODE) {
        echo "<div class='alert alert-error'>Lỗi: " . htmlspecialchars($errstr) . "</div>";
    }
});