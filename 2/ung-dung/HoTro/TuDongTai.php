<?php

declare(strict_types=1);

/**
 * ====================================
 * TỰ ĐỘNG TẢI - AUTOLOADER CHO KHÔNG GIAN TÊN
 * ====================================
 * 
 * Mục đích:
 *   - Tự động tải các lớp từ thư mục ung-dung/ mà không cần require/include thủ công.
 *   - Tuân theo chuẩn PSR-4 Autoloading Standard.
 * 
 * Ví dụ:
 *   - UngDung\DichVu\CoSoDuLieu → ung-dung/DichVu/CoSoDuLieu.php
 *   - UngDung\KhoDuLieu\PhieuThuKho → ung-dung/KhoDuLieu/PhieuThuKho.php
 */

spl_autoload_register(static function (string $class): void {
    // Tiền tố không gian tên ứng dụng
    $prefix = 'UngDung\\';
    // Thư mục gốc chứa các lớp
    $baseDir = __DIR__ . '/../';

    // Kiểm tra xem tên lớp có bắt đầu bằng UngDung\ hay không
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    // Loại bỏ tiền tố "UngDung\" từ tên lớp
    $relativeClass = substr($class, strlen($prefix));
    // Tạo đường dẫn tệp bằng cách thay thế \ bằng DIRECTORY_SEPARATOR
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    // Nếu tệp tồn tại, require nó
    if (file_exists($file)) {
        require_once $file;
    }
});
?>