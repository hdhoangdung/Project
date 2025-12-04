<?php

declare(strict_types=1);

namespace UngDung\KhoDuLieu;

use UngDung\DichVu\CoSoDuLieu;

class NguoiDungKho
{
    private CoSoDuLieu $csdl;

    public function __construct(?CoSoDuLieu $csdl = null)
    {
        $this->csdl = $csdl ?? CoSoDuLieu::layInstance();
    }

    public function timTheoTenDangNhap(string $tenDangNhap): ?array
    {
        $sql = "
            SELECT tk.*, nv.MaNV, nv.HoTen, nv.PhongBan
            FROM TaiKhoan tk
            JOIN NhanVien nv ON tk.MaNV = nv.MaNV
            WHERE tk.TenDangNhap = ? AND tk.TrangThai = 1
        ";

        return $this->csdl->layDong($sql, [$tenDangNhap]);
    }

    public function tangThatBai(array $nguoiDung): array
    {
        $lanThatBai = (int) ($nguoiDung['SoLanDangNhapSai'] ?? 0) + 1;
        if ($lanThatBai >= MAX_LOGIN_ATTEMPTS) {
            $khoaDen = date('Y-m-d H:i:s', time() + LOCKOUT_TIME);
            $this->csdl->truyVan(
                'UPDATE TaiKhoan SET SoLanDangNhapSai = ?, ThoiGianKhoa = ? WHERE MaTK = ?',
                [$lanThatBai, $khoaDen, $nguoiDung['MaTK']]
            );
            return ['ok' => false, 'thong_diep' => 'Tài khoản đã bị khóa do nhập sai quá nhiều lần.'];
        }

        $this->csdl->truyVan(
            'UPDATE TaiKhoan SET SoLanDangNhapSai = ? WHERE MaTK = ?',
            [$lanThatBai, $nguoiDung['MaTK']]
        );

        $conLai = MAX_LOGIN_ATTEMPTS - $lanThatBai;
        return ['ok' => false, 'thong_diep' => "Mật khẩu không đúng. Còn {$conLai} lần thử."];
    }

    public function resetThatBai(string $maTk): void
    {
        $this->csdl->truyVan(
            'UPDATE TaiKhoan SET SoLanDangNhapSai = 0, ThoiGianKhoa = NULL WHERE MaTK = ?',
            [$maTk]
        );
    }
}

