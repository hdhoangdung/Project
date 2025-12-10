<?php

declare(strict_types=1);

namespace UngDung\KhoDuLieu;

use UngDung\DichVu\CoSoDuLieu;

/**
 * ====================================
 * KHO DỮ LIỆU NGƯỜI DÙNG - QUẢN LÝ TÀI KHOẢN
 * ====================================
 * 
 * Repository quản lý tài khoản người dùng, mật khẩu, trạng thái khóa.
 * 
 * Chịu trách nhiệm:
 *   - Tìm kiếm tài khoản theo username.
 *   - Quản lý số lần đăng nhập sai.
 *   - Khóa / mở khóa tài khoản.
 * 
 * Sử dụng:
 *   $khoNguoiDung = new NguoiDungKho();
 *   $nguoiDung = $khoNguoiDung->timTheoTenDangNhap('ketoan');
 *   $khoNguoiDung->tangThatBai($nguoiDung);
 */
class NguoiDungKho
{
    /** Kết nối CSDL */
    private CoSoDuLieu $csdl;

    public function __construct(?CoSoDuLieu $csdl = null)
    {
        $this->csdl = $csdl ?? CoSoDuLieu::layInstance();
    }

    /**
     * Tìm tài khoản theo tên đăng nhập.
     * 
     * JOIN với bảng NhanVien để lấy thêm thông tin nhân viên.
     * Chỉ tìm tài khoản đang hoạt động (TrangThai = 1).
     * 
     * @param string $tenDangNhap Tên đăng nhập
     * 
     * @return array<string, mixed>|null 
     *         Dữ liệu tài khoản với thông tin nhân viên hoặc null nếu không tìm thấy
     */
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

    /**
     * Tăng số lần đăng nhập sai.
     * 
     * Nếu vượt quá MAX_LOGIN_ATTEMPTS, tài khoản sẽ bị khóa (ThoiGianKhoa).
     * 
     * @param array $nguoiDung Dữ liệu tài khoản từ DB
     * 
     * @return array{ok:bool,thong_diep:string} 
     *         Nếu vượt quá lần, trả về thông báo khóa;
     *         Nếu chưa, trả về thông báo còn bao nhiêu lần thử.
     */
    public function tangThatBai(array $nguoiDung): array
    {
        // Lấy số lần sai hiện tại
        $lanThatBai = (int) ($nguoiDung['SoLanDangNhapSai'] ?? 0) + 1;

        // Nếu đã vượt quá giới hạn, khóa tài khoản
        if ($lanThatBai >= MAX_LOGIN_ATTEMPTS) {
            $khoaDen = date('Y-m-d H:i:s', time() + LOCKOUT_TIME);
            $this->csdl->truyVan(
                'UPDATE TaiKhoan SET SoLanDangNhapSai = ?, ThoiGianKhoa = ? WHERE MaTK = ?',
                [$lanThatBai, $khoaDen, $nguoiDung['MaTK']]
            );
            return ['ok' => false, 'thong_diep' => 'Tài khoản đã bị khóa do nhập sai quá nhiều lần.'];
        }

        // Chưa vượt, chỉ tăng số lần sai
        $this->csdl->truyVan(
            'UPDATE TaiKhoan SET SoLanDangNhapSai = ? WHERE MaTK = ?',
            [$lanThatBai, $nguoiDung['MaTK']]
        );

        $conLai = MAX_LOGIN_ATTEMPTS - $lanThatBai;
        return ['ok' => false, 'thong_diep' => "Mật khẩu không đúng. Còn {$conLai} lần thử."];
    }

    /**
     * Reset số lần đăng nhập sai (sau khi đăng nhập thành công).
     * 
     * @param string $maTk Mã tài khoản
     */
    public function resetThatBai(string $maTk): void
    {
        $this->csdl->truyVan(
            'UPDATE TaiKhoan SET SoLanDangNhapSai = 0, ThoiGianKhoa = NULL WHERE MaTK = ?',
            [$maTk]
        );
    }
}
?>

