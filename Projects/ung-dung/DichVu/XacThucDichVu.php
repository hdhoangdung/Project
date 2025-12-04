<?php

declare(strict_types=1);

namespace UngDung\DichVu;

/**
 * Quản lý xác thực người dùng và phân quyền.
 */
class XacThucDichVu
{
    private const KHOA_NGUOI_DUNG = 'NGUOI_DUNG';

    public function __construct()
    {
        PhienDichVu::batDau();
    }

    public function daDangNhap(): bool
    {
        return (bool) PhienDichVu::lay(self::KHOA_NGUOI_DUNG);
    }

    public function nguoiDung(): ?array
    {
        /** @var array|null $user */
        $user = PhienDichVu::lay(self::KHOA_NGUOI_DUNG);
        return $user;
    }

    public function dangNhap(array $thongTinNguoiDung): void
    {
        PhienDichVu::dat(self::KHOA_NGUOI_DUNG, $thongTinNguoiDung);
    }

    public function dangXuat(): void
    {
        PhienDichVu::xoa(self::KHOA_NGUOI_DUNG);
        session_destroy();
    }

    /**
     * @param string|string[] $vaiTro
     */
    public function batBuocVaiTro($vaiTro, string $chuaDangNhap = '/dang-nhap.php', string $saiQuyen = '/'): void
    {
        if (!$this->daDangNhap()) {
            header('Location: ' . $chuaDangNhap);
            exit;
        }

        $nguoiDung = $this->nguoiDung();
        $role = $nguoiDung['vai_tro'] ?? '';
        $allowed = is_array($vaiTro) ? in_array($role, $vaiTro, true) : $role === $vaiTro;

        if (!$allowed) {
            header('Location: ' . $saiQuyen);
            exit;
        }
    }

    public function chuyenHuongTheoVaiTro(): void
    {
        if (!$this->daDangNhap()) {
            return;
        }

        $base = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        header('Location: ' . $base . '/ke-toan/index.php');
        exit;
    }
}

