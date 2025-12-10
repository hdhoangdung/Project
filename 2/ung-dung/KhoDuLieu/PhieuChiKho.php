<?php

declare(strict_types=1);

namespace UngDung\KhoDuLieu;

use UngDung\DichVu\CoSoDuLieu;
use UngDung\HoTro\DuLieu;

/**
 * ====================================
 * KHO DỮ LIỆU PHIẾU CHI - QUẢN LÝ PHIẾU CHI
 * ====================================
 * 
 * Lớp Repository làm việc với bảng `phieuchi` và các bảng liên quan.
 * 
 * Chịu trách nhiệm:
 *   - Tạo / cập nhật / xóa mềm phiếu chi.
 *   - Lọc danh sách phiếu chi theo ngày, loại chi, từ khóa.
 *   - Lấy danh sách yêu cầu bồi thường đã duyệt nhưng chưa lập phiếu chi.
 *   - Chuẩn bị dữ liệu cho màn hình in phiếu chi.
 * 
 * Sử dụng:
 *   $phieuChi = new PhieuChiKho();
 *   $ketQua = $phieuChi->tao($duLieu, $nguoiDung);
 *   $danhSach = $phieuChi->danhSach($boLoc);
 */
class PhieuChiKho
{
    /**
     * Kết nối CSDL được bọc trong lớp dịch vụ CoSoDuLieu.
     * Đảm bảo tính singleton và quản lý kết nối tập trung.
     */
    private CoSoDuLieu $csdl;

    /**
     * Constructor - Khởi tạo với kết nối CSDL.
     * 
     * @param CoSoDuLieu|null $csdl Kết nối CSDL tùy chỉnh (dùng cho test).
     *                              Nếu null, sẽ dùng Singleton mặc định.
     */
    public function __construct(?CoSoDuLieu $csdl = null)
    {
        $this->csdl = $csdl ?? CoSoDuLieu::layInstance();
    }

    /**
     * Tạo mới một phiếu chi.
     * 
     * Tính năng:
     *   - Lưu phiếu chi từ yêu cầu bồi thường hoặc chi phí khác.
     *   - Cập nhật trạng thái yêu cầu bồi thường nếu có.
     *   - Ghi lại người tạo (MaNV).
     * 
     * @param array $duLieu    Dữ liệu từ form:
     *                         - ma_yc: Mã yêu cầu bồi thường (nullable)
     *                         - ngay_chi: Ngày chi (Y-m-d)
     *                         - so_tien: Số tiền (float)
     *                         - ghi_chu: Ghi chú (string)
     *                         - noi_dung: Nội dung chi (nếu chi khác)
     * @param array $nguoiDung Thông tin người dùng từ session:
     *                         - ma_nv: Mã nhân viên (bắt buộc)
     * 
     * @return array{ok:bool,thong_diep:string,ma_pc:string}
     *         - ok: true nếu tạo thành công
     *         - thong_diep: Thông báo kết quả
     *         - ma_pc: Mã phiếu chi vừa tạo
     */
    public function tao(array $duLieu, array $nguoiDung): array
    {
        // Lấy mã nhân viên từ session - bắt buộc
        $maNv = $nguoiDung['ma_nv'] ?? null;
        if (!$maNv) {
            return ['ok' => false, 'thong_diep' => 'Không xác định nhân viên thực hiện. Vui lòng đăng nhập lại.'];
        }

        // INSERT vào bảng phieuchi
        $sql = 'INSERT INTO phieuchi (MaYC, NgayChi, SoTien, GhiChu, MaNV, TrangThai)
                VALUES (?, ?, ?, ?, ?, \'Đã chi trả\')';

        $maYc = $duLieu['ma_yc'] ?: null;
        $ghiChu = $duLieu['ghi_chu'] ?: ($duLieu['noi_dung'] ?? '');
        
        $thanhCong = $this->csdl->truyVan($sql, [
            $maYc,
            $duLieu['ngay_chi'],
            $duLieu['so_tien'],
            $ghiChu,
            $maNv,
        ]);

        // Lấy ID tự tăng
        $maPhieu = (string) $this->csdl->ketNoi()->insert_id;

        // Nếu là chi bồi thường, cập nhật trạng thái yêu cầu
        if ($thanhCong && $maYc) {
            $this->csdl->truyVan(
                'UPDATE yeucauboithuong SET TrangThai = \'Đã chi trả\' WHERE MaYC = ?',
                [$maYc]
            );
        }

        return [
            'ok' => (bool) $thanhCong,
            'thong_diep' => $thanhCong ? 'Thêm phiếu chi thành công!' : 'Không thể tạo phiếu chi.',
            'ma_pc' => $maPhieu,
        ];
    }

    /**
     * Cập nhật thông tin phiếu chi.
     * 
     * @param array $duLieu Dữ liệu cập nhật:
     *                      - ma_pc: Mã phiếu chi (bắt buộc)
     *                      - so_tien: Số tiền mới
     *                      - ngay_chi: Ngày chi mới
     *                      - ghi_chu: Ghi chú mới
     * 
     * @return array{ok:bool,thong_diep:string,ma_pc:string}
     */
    public function capNhat(array $duLieu): array
    {
        $sql = 'UPDATE phieuchi SET SoTien = ?, NgayChi = ?, GhiChu = ? WHERE MaPC = ?';
        $thanhCong = $this->csdl->truyVan($sql, [
            $duLieu['so_tien'],
            $duLieu['ngay_chi'],
            $duLieu['ghi_chu'],
            $duLieu['ma_pc'],
        ]);

        return [
            'ok' => (bool) $thanhCong,
            'thong_diep' => $thanhCong ? 'Cập nhật phiếu chi thành công!' : 'Không thể cập nhật phiếu chi.',
            'ma_pc' => $duLieu['ma_pc'],
        ];
    }

    /**
     * Xóa mềm phiếu chi (đánh dấu là "Đã hủy").
     * 
     * @param string $maPc Mã phiếu chi cần xóa
     * 
     * @return array{ok:bool,thong_diep:string,ma_pc:string}
     */
    public function xoa(string $maPc): array
    {
        $thanhCong = $this->csdl->truyVan(
            'UPDATE phieuchi SET TrangThai = \'Đã hủy\' WHERE MaPC = ?',
            [$maPc]
        );
        return [
            'ok' => (bool) $thanhCong,
            'thong_diep' => $thanhCong ? 'Xóa phiếu chi thành công!' : 'Không thể xóa phiếu chi.',
            'ma_pc' => $maPc,
        ];
    }

    /**
     * Lấy danh sách phiếu chi với bộ lọc.
     * 
     * Bộ lọc hỗ trợ:
     *   - tu_ngay, den_ngay: Khoảng ngày chi
     *   - tu_khoa: Tìm kiếm theo mã phiếu, ghi chú
     *   - loai_chi: 'boi_thuong' hoặc 'khac'
     * 
     * @param array $boLoc Bộ lọc (mặc định rỗng = toàn bộ)
     * 
     * @return array<int, array<string, mixed>> Danh sách phiếu chi
     */
    public function danhSach(array $boLoc = []): array
    {
        $dieuKien = ['1=1'];
        
        if (!empty($boLoc['tu_ngay'])) {
            $dieuKien[] = "pc.NgayChi >= '" . DuLieu::lamSach($boLoc['tu_ngay']) . "'";
        }
        if (!empty($boLoc['den_ngay'])) {
            $dieuKien[] = "pc.NgayChi <= '" . DuLieu::lamSach($boLoc['den_ngay']) . "'";
        }
        if (!empty($boLoc['tu_khoa'])) {
            $tuKhoa = DuLieu::lamSach($boLoc['tu_khoa']);
            $dieuKien[] = "(pc.MaPC LIKE '%{$tuKhoa}%' OR pc.GhiChu LIKE '%{$tuKhoa}%')";
        }
        if (!empty($boLoc['loai_chi'])) {
            if ($boLoc['loai_chi'] === 'boi_thuong') {
                $dieuKien[] = 'pc.MaYC IS NOT NULL';
            } elseif ($boLoc['loai_chi'] === 'khac') {
                $dieuKien[] = 'pc.MaYC IS NULL';
            }
        }

        // JOIN với bảng liên quan để lấy thông tin khách hàng, xe
        $sql = "
            SELECT pc.*, yc.MoTaSuCo, yc.NgaySuCo, k.HoTen, x.BienSo
            FROM phieuchi pc
            LEFT JOIN yeucauboithuong yc ON pc.MaYC = yc.MaYC
            LEFT JOIN hopdong h ON yc.MaHD = h.MaHD
            LEFT JOIN khachhang k ON h.MaKH = k.MaKH
            LEFT JOIN xeoto x ON h.MaXe = x.MaXe
            WHERE " . implode(' AND ', $dieuKien) . "
            ORDER BY pc.NgayChi DESC, pc.MaPC DESC
        ";

        return $this->csdl->layTatCa($sql);
    }

    /**
     * Tính tổng tiền chi theo bộ lọc.
     * 
     * @param array $boLoc Bộ lọc (giống danhSach)
     * 
     * @return float Tổng tiền chi
     */
    public function tongChi(array $boLoc = []): float
    {
        $dieuKien = ['1=1'];
        if (!empty($boLoc['tu_ngay'])) {
            $dieuKien[] = "NgayChi >= '" . DuLieu::lamSach($boLoc['tu_ngay']) . "'";
        }
        if (!empty($boLoc['den_ngay'])) {
            $dieuKien[] = "NgayChi <= '" . DuLieu::lamSach($boLoc['den_ngay']) . "'";
        }
        if (!empty($boLoc['loai_chi'])) {
            if ($boLoc['loai_chi'] === 'boi_thuong') {
                $dieuKien[] = 'MaYC IS NOT NULL';
            } elseif ($boLoc['loai_chi'] === 'khac') {
                $dieuKien[] = 'MaYC IS NULL';
            }
        }
        if (!empty($boLoc['tu_khoa'])) {
            $tuKhoa = DuLieu::lamSach($boLoc['tu_khoa']);
            $dieuKien[] = "(MaPC LIKE '%{$tuKhoa}%' OR GhiChu LIKE '%{$tuKhoa}%')";
        }

        $sql = 'SELECT COALESCE(SUM(SoTien),0) AS Tong FROM phieuchi WHERE ' . implode(' AND ', $dieuKien);
        return (float) $this->csdl->layGiaTri($sql);
    }

    /**
     * Lấy danh sách yêu cầu bồi thường đã duyệt nhưng chưa lập phiếu chi.
     * 
     * Dùng để populate dropdown chọn yêu cầu khi tạo phiếu chi.
     * 
     * @return array<int, array<string, mixed>> Danh sách yêu cầu
     */
    public function danhSachYeuCau(): array
    {
        $sql = "
            SELECT yc.MaYC, yc.SoTienDuyet, yc.MoTaSuCo, yc.NgaySuCo,
                   k.HoTen, x.BienSo
            FROM yeucauboithuong yc
            JOIN hopdong h ON yc.MaHD = h.MaHD
            JOIN khachhang k ON h.MaKH = k.MaKH
            JOIN xeoto x ON h.MaXe = x.MaXe
            WHERE yc.TrangThai = 'Đã duyệt' AND yc.MaYC NOT IN (
                SELECT MaYC FROM phieuchi WHERE MaYC IS NOT NULL
            )
            ORDER BY yc.NgayYeuCau DESC
        ";

        return $this->csdl->layTatCa($sql);
    }

    /**
     * Lấy thông tin chi tiết phiếu chi để in.
     * 
     * JOIN với tất cả bảng liên quan để chuẩn bị dữ liệu đầy đủ cho template in.
     * 
     * @param string $maPc Mã phiếu chi
     * 
     * @return array<string, mixed>|null Dữ liệu phiếu chi hoặc null nếu không tìm thấy
     */
    public function thongTinIn(string $maPc): ?array
    {
        $sql = "
            SELECT pc.*, yc.MaYC, yc.MoTaSuCo, yc.NgaySuCo, yc.DiaDiemSuCo,
                   k.HoTen, k.DiaChi, k.SoDienThoai, k.CCCD,
                   x.BienSo, x.HangXe, x.DongXe,
                   n.HoTen AS TenNV
            FROM phieuchi pc
            LEFT JOIN yeucauboithuong yc ON pc.MaYC = yc.MaYC
            LEFT JOIN hopdong h ON yc.MaHD = h.MaHD
            LEFT JOIN khachhang k ON h.MaKH = k.MaKH
            LEFT JOIN xeoto x ON h.MaXe = x.MaXe
            LEFT JOIN nhanvien n ON pc.MaNV = n.MaNV
            WHERE pc.MaPC = ?
        ";

        return $this->csdl->layDong($sql, [$maPc]);
    }
}
?>

