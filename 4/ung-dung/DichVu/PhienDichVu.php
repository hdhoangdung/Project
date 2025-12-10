<?php

declare(strict_types=1);

namespace UngDung\DichVu;

/**
 * ====================================
 * DỊCH VỤ PHIÊN - QUẢN LÝ SESSION
 * ====================================
 * 
 * Quản lý lifecycle của session (phiên làm việc):
 *   - Khởi tạo session an toàn.
 *   - Kiểm tra timeout tự động.
 *   - Lưu/đọc/xóa dữ liệu từ $_SESSION.
 * 
 * Sử dụng:
 *   PhienDichVu::batDau();           // Gọi ở đầu mỗi trang
 *   PhienDichVu::dat('user_id', 5);  // Lưu dữ liệu
 *   $userId = PhienDichVu::lay('user_id');  // Lấy dữ liệu
 */
class PhienDichVu
{
    // Khoảng thời gian không hoạt động tối đa (từ cấu hình)
    private const KHOANG_THOI_GIAN = SESSION_TIMEOUT;

    /**
     * Khởi tạo và bảo vệ session.
     * Được gọi ở đầu mọi trang chính.
     * 
     * Tính năng:
     *   - Tạo session nếu chưa có.
     *   - Thiết lập cookie httponly và use_only_cookies.
     *   - Kiểm tra timeout tự động.
     *   - Cập nhật lại thời điểm hoạt động cuối cùng.
     */
    public static function batDau(): void
    {
        // Nếu session chưa bắt đầu, hãy khởi tạo
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', '1');  // Không cho JavaScript truy cập cookie
            ini_set('session.use_only_cookies', '1');  // Chỉ dùng cookie, không URL
            session_start();
        }

        // Kiểm tra timeout: nếu quá lâu không hoạt động thì hủy session
        $now = time();
        if (!empty($_SESSION['THOI_DIEM_HOAT_DONG']) && ($now - (int) $_SESSION['THOI_DIEM_HOAT_DONG']) > self::KHOANG_THOI_GIAN) {
            session_unset();      // Xóa tất cả dữ liệu session
            session_destroy();    // Hủy session
            header('Location: /dang-nhap.php?timeout=1');  // Chuyển hướng tới login
            exit;
        }

        // Cập nhật lại thời điểm hoạt động
        $_SESSION['THOI_DIEM_HOAT_DONG'] = $now;
    }

    /**
     * Lấy một giá trị từ session.
     * 
     * @param string $khoa        Khóa cần lấy
     * @param mixed  $macDinh     Giá trị mặc định nếu khóa không tồn tại
     * @return mixed  Giá trị từ $_SESSION hoặc giá trị mặc định
     */
    public static function lay(string $khoa, $macDinh = null)
    {
        return $_SESSION[$khoa] ?? $macDinh;
    }

    /**
     * Lưu một giá trị vào session.
     * 
     * @param string $khoa   Khóa
     * @param mixed  $giaTri Giá trị
     */
    public static function dat(string $khoa, $giaTri): void
    {
        $_SESSION[$khoa] = $giaTri;
    }

    /**
     * Xóa một giá trị từ session.
     * 
     * @param string $khoa Khóa cần xóa
     */
    public static function xoa(string $khoa): void
    {
        unset($_SESSION[$khoa]);
    }
}
?>

