<?php

declare(strict_types=1);

/**
 * ========================================
 * TỆPC ẤU HÌNH CHÍNH - CẤU HÌNH & KHỞI ĐỘNG
 * ========================================
 * 
 * Mục đích:
 *   - Thiết lập các hằng số cấu hình (DB, session, app).
 *   - Tự động tải các lớp từ không gian tên UngDung.
 *   - Khởi tạo phiên làm việc.
 *   - Xử lý lỗi toàn cục.
 * 
 * Sử dụng:
 *   - Require tệp này ở đầu mọi trang PHP chính.
 */

// Ngăn chặn truy cập trực tiếp vào tệp này
defined('APP_ACCESS') or define('APP_ACCESS', true);

// ===== CẤU HÌNH KẾT NỐI CƠSỞ DỮ LIỆU =====
define('DB_HOST', 'localhost');      // Máy chủ MySQL
define('DB_USER', 'root');           // Tên đăng nhập MySQL
define('DB_PASS', '');               // Mật khẩu MySQL
define('DB_NAME', 'qlbh_xe');        // Tên cơ sở dữ liệu
define('DB_CHARSET', 'utf8mb4');     // Bộ ký tự UTF-8

// ===== CẤU HÌNH PHIÊN (SESSION) =====
define('SESSION_TIMEOUT', 1800);     // Hết hạn phiên sau 30 phút (giây)
define('MAX_LOGIN_ATTEMPTS', 5);     // Tối đa 5 lần đăng nhập sai
define('LOCKOUT_TIME', 900);         // Khóa tài khoản 15 phút (giây)

// ===== CẤU HÌNH ỨNG DỤNG =====
define('APP_NAME', 'Hệ thống Quản lý Bảo hiểm Xe');  // Tên ứng dụng
define('APP_VERSION', '3.0');                         // Phiên bản
define('DEBUG_MODE', true);                           // Chế độ gỡ lỗi

// ===== THIẾT LẬP MÚINÊN =====
date_default_timezone_set('Asia/Ho_Chi_Minh');  // Múi giờ Việt Nam

// ===== TỰ ĐỘNG TẢI CÁC LỚP (AUTOLOAD) =====
/**
 * Require tệp TuDongTai.php để đăng ký autoloader cho không gian tên UngDung.
 * Điều này cho phép tự động tải các lớp mà không cần require thủ công.
 */
require_once __DIR__ . '/ung-dung/HoTro/TuDongTai.php';

// ===== KHỞI ĐỘNG PHIÊN =====
use UngDung\DichVu\PhienDichVu;

PhienDichVu::batDau();  // Bắt đầu session và kiểm tra timeout

// ===== XỬ LÝ LỖI TOÀN CỤC =====
/**
 * Đăng ký hàm xử lý lỗi tùy chỉnh.
 * Ghi lỗi vào tệp nhật ký và hiển thị thông báo nếu DEBUG_MODE = true.
 */
set_error_handler(static function (int $errno, string $errstr, string $errfile, int $errline): void {
    error_log("[$errno] $errstr in $errfile:$errline");
    
    if (DEBUG_MODE) {
        echo "<div class='alert alert-error'>Lỗi: " . htmlspecialchars($errstr) . "</div>";
    }
});
?>