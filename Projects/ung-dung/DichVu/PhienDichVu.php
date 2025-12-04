<?php

declare(strict_types=1);

namespace UngDung\DichVu;

/**
 * Quản lý session, timeout và tiện ích lưu/đọc dữ liệu phiên.
 */
class PhienDichVu
{
    private const KHOANG_THOI_GIAN = SESSION_TIMEOUT;

    public static function batDau(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_only_cookies', '1');
            session_start();
        }

        $now = time();
        if (!empty($_SESSION['THOI_DIEM_HOAT_DONG']) && ($now - (int) $_SESSION['THOI_DIEM_HOAT_DONG']) > self::KHOANG_THOI_GIAN) {
            session_unset();
            session_destroy();
            header('Location: /dang-nhap.php?timeout=1');
            exit;
        }

        $_SESSION['THOI_DIEM_HOAT_DONG'] = $now;
    }

    public static function lay(string $khoa, $macDinh = null)
    {
        return $_SESSION[$khoa] ?? $macDinh;
    }

    public static function dat(string $khoa, $giaTri): void
    {
        $_SESSION[$khoa] = $giaTri;
    }

    public static function xoa(string $khoa): void
    {
        unset($_SESSION[$khoa]);
    }
}

