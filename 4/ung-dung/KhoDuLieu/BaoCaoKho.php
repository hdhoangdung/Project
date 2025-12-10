<?php

declare(strict_types=1);

namespace UngDung\KhoDuLieu;

use UngDung\DichVu\CoSoDuLieu;

/**
 * ====================================
 * KHO DỮ LIỆU BÁO CÁO - QUẢN LÝ THỐNG KÊ TÀI CHÍNH
 * ====================================
 * 
 * Repository cung cấp các phương thức thống kê và báo cáo tài chính.
 * 
 * Chịu trách nhiệm:
 *   - Tính toán tổng thu, tổng chi, lợi nhuận.
 *   - Thống kê chi tiết theo ngày.
 *   - Xếp hạng khách hàng, yêu cầu bồi thường.
 *   - Lấy giao dịch gần đây.
 * 
 * Sử dụng:
 *   $baoCao = new BaoCaoKho();
 *   $tongThu = $baoCao->tongThu('2025-01-01', '2025-01-31');
 *   $topKhach = $baoCao->topKhachHang('2025-01-01', '2025-01-31');
 */
class BaoCaoKho
{
    /** Kết nối CSDL */
    private CoSoDuLieu $csdl;

    public function __construct(?CoSoDuLieu $csdl = null)
    {
        $this->csdl = $csdl ?? CoSoDuLieu::layInstance();
    }

    /**
     * Tính tổng tiền thu trong khoảng ngày.
     * 
     * @param string|null $tuNgay  Ngày bắt đầu (Y-m-d). Nếu null, tính toàn bộ.
     * @param string|null $denNgay Ngày kết thúc (Y-m-d)
     * 
     * @return float Tổng tiền thu (VNĐ)
     */
    public function tongThu(?string $tuNgay = null, ?string $denNgay = null): float
    {
        $sql = "SELECT COALESCE(SUM(SoTien), 0) FROM PhieuThu WHERE TrangThai = 'Hoạt động'";
        $params = [];
        
        if ($tuNgay && $denNgay) {
            $sql .= ' AND NgayThu BETWEEN ? AND ?';
            $params = [$tuNgay, $denNgay];
        }
        
        return (float) $this->csdl->layGiaTri($sql, $params);
    }

    /**
     * Tính tổng tiền chi trong khoảng ngày.
     * 
     * @param string|null $tuNgay  Ngày bắt đầu (Y-m-d)
     * @param string|null $denNgay Ngày kết thúc (Y-m-d)
     * 
     * @return float Tổng tiền chi (VNĐ)
     */
    public function tongChi(?string $tuNgay = null, ?string $denNgay = null): float
    {
        $sql = "SELECT COALESCE(SUM(SoTien), 0) FROM PhieuChi WHERE TrangThai = 'Đã chi trả'";
        $params = [];
        
        if ($tuNgay && $denNgay) {
            $sql .= ' AND NgayChi BETWEEN ? AND ?';
            $params = [$tuNgay, $denNgay];
        }
        
        return (float) $this->csdl->layGiaTri($sql, $params);
    }

    /**
     * Đếm số giao dịch trong tháng hiện tại.
     * 
     * Tính cả phiếu thu và phiếu chi.
     * 
     * @return int Số giao dịch
     */
    public function giaoDichThangNay(): int
    {
        $sql = "
            SELECT
                (SELECT COUNT(*) FROM PhieuThu 
                 WHERE MONTH(NgayThu) = MONTH(CURRENT_DATE()) 
                 AND YEAR(NgayThu) = YEAR(CURRENT_DATE()) 
                 AND TrangThai = 'Hoạt động')
                +
                (SELECT COUNT(*) FROM PhieuChi 
                 WHERE MONTH(NgayChi) = MONTH(CURRENT_DATE()) 
                 AND YEAR(NgayChi) = YEAR(CURRENT_DATE()) 
                 AND TrangThai = 'Đã chi trả')
        ";

        return (int) $this->csdl->layGiaTri($sql);
    }

    /**
     * Lấy danh sách giao dịch gần đây (10 giao dịch cuối).
     * 
     * Hợp nhất phiếu thu và phiếu chi, sắp xếp theo ngày giảm dần.
     * 
     * @return array<int, array<string, mixed>> Danh sách giao dịch
     */
    public function giaoDichGanDay(): array
    {
        $sql = "
            SELECT t.*, h.MaHD, k.HoTen AS ten_khach, k.SoDienThoai AS SDT
            FROM (
                SELECT p.MaPT AS MaGD, 'Thu' AS LoaiGD, p.SoTien, p.NgayThu AS NgayGD, p.MaHD, p.GhiChu
                FROM PhieuThu p WHERE p.TrangThai = 'Hoạt động'
                UNION ALL
                SELECT pc.MaPC AS MaGD, 'Chi' AS LoaiGD, pc.SoTien, pc.NgayChi AS NgayGD, yc.MaHD, pc.GhiChu
                FROM PhieuChi pc
                LEFT JOIN YeuCauBoiThuong yc ON pc.MaYC = yc.MaYC
                WHERE pc.TrangThai IS NOT NULL
            ) t
            LEFT JOIN hopdong h ON t.MaHD = h.MaHD
            LEFT JOIN khachhang k ON h.MaKH = k.MaKH
            ORDER BY t.NgayGD DESC, t.MaGD DESC
            LIMIT 10
        ";

        return $this->csdl->layTatCa($sql);
    }

    /**
     * Chi tiết thu chi theo ngày.
     * 
     * Dùng UNION để kết hợp phiếu thu và phiếu chi, sau đó GROUP BY ngày.
     * 
     * @param string $tuNgay  Ngày bắt đầu (Y-m-d)
     * @param string $denNgay Ngày kết thúc (Y-m-d)
     * 
     * @return array<int, array<string, mixed>> 
     *         Mỗi item: ngay, thu, chi, loi_nhuan_ngay
     */
    public function chiTietNgay(string $tuNgay, string $denNgay): array
    {
        $sql = "
            SELECT 
                DATE(ngay_gd) AS ngay,
                SUM(CASE WHEN loai = 'Thu' THEN so_tien ELSE 0 END) AS thu,
                SUM(CASE WHEN loai = 'Chi' THEN so_tien ELSE 0 END) AS chi,
                SUM(CASE WHEN loai = 'Thu' THEN so_tien ELSE -so_tien END) AS loi_nhuan_ngay
            FROM (
                SELECT NgayThu AS ngay_gd, 'Thu' AS loai, SoTien AS so_tien
                FROM PhieuThu
                WHERE NgayThu BETWEEN ? AND ? AND TrangThai = 'Hoạt động'
                UNION ALL
                SELECT NgayChi AS ngay_gd, 'Chi' AS loai, SoTien AS so_tien
                FROM PhieuChi
                WHERE NgayChi BETWEEN ? AND ? AND TrangThai = 'Đã chi trả'
            ) combined
            GROUP BY DATE(ngay_gd)
            ORDER BY ngay ASC
        ";

        return $this->csdl->layTatCa($sql, [$tuNgay, $denNgay, $tuNgay, $denNgay]);
    }

    /**
     * Top 5 khách hàng đóng phí nhiều nhất.
     * 
     * Thống kê dựa trên phiếu thu (vì phí bảo hiểm là thu).
     * 
     * @param string $tuNgay  Ngày bắt đầu (Y-m-d)
     * @param string $denNgay Ngày kết thúc (Y-m-d)
     * 
     * @return array<int, array<string, mixed>>
     *         Mỗi item: HoTen, SDT, tong_dong
     */
    public function topKhachHang(string $tuNgay, string $denNgay): array
    {
        $sql = "
            SELECT k.HoTen, k.SoDienThoai AS SDT, SUM(pt.SoTien) AS tong_dong
            FROM PhieuThu pt
            JOIN HopDong h ON pt.MaHD = h.MaHD
            JOIN KhachHang k ON h.MaKH = k.MaKH
            WHERE pt.NgayThu BETWEEN ? AND ? AND pt.TrangThai = 'Hoạt động'
            GROUP BY k.MaKH
            ORDER BY tong_dong DESC
            LIMIT 5
        ";

        return $this->csdl->layTatCa($sql, [$tuNgay, $denNgay]);
    }

    /**
     * Top 5 yêu cầu bồi thường chi nhiều tiền nhất.
     * 
     * @param string $tuNgay  Ngày bắt đầu (Y-m-d)
     * @param string $denNgay Ngày kết thúc (Y-m-d)
     * 
     * @return array<int, array<string, mixed>>
     *         Mỗi item: HoTen, MoTaSuCo, SoTien, NgayChi
     */
    public function topBoiThuong(string $tuNgay, string $denNgay): array
    {
        $sql = "
            SELECT k.HoTen, yc.MoTaSuCo, pc.SoTien, pc.NgayChi
            FROM PhieuChi pc
            JOIN YeuCauBoiThuong yc ON pc.MaYC = yc.MaYC
            JOIN HopDong h ON yc.MaHD = h.MaHD
            JOIN KhachHang k ON h.MaKH = k.MaKH
            WHERE pc.NgayChi BETWEEN ? AND ?
            ORDER BY pc.SoTien DESC
            LIMIT 5
        ";

        return $this->csdl->layTatCa($sql, [$tuNgay, $denNgay]);
    }
}
?>

