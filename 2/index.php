<?php
declare(strict_types=1);

// Load class nếu chưa tồn tại
if (!class_exists('UngDung\\DichVu\\XacThucDichVu')) {
    require_once __DIR__ . '/ung-dung/DichVu/XacThucDichVu.php';
}

use UngDung\DichVu\XacThucDichVu;

// Khởi tạo dịch vụ xác thực
$xacThuc = new XacThucDichVu();

// Kiểm tra đăng nhập + chuyển hướng theo role
$xacThuc->chuyenHuongTheoVaiTro();
