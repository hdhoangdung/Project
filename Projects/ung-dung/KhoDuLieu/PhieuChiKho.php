<?php

declare(strict_types=1);

namespace UngDung\KhoDuLieu;

use UngDung\DichVu\CoSoDuLieu;
use UngDung\HoTro\DuLieu;

/**
 * Repository làm việc với bảng `phieuchi` và các bảng liên quan.
 *
 * Chịu trách nhiệm:
 * - Tạo / cập nhật / xóa mềm phiếu chi.
 * - Lọc danh sách phiếu chi theo ngày, loại chi, từ khóa.
 * - Lấy danh sách yêu cầu bồi thường đã duyệt nhưng chưa lập phiếu chi.
 * - Chuẩn bị dữ liệu cho màn hình in phiếu chi.
 */
class PhieuChiKho
{
    /** Kết nối CSDL được bọc trong lớp dịch vụ CoSoDuLieu */
    private CoSoDuLieu $csdl;

    /**
     * @param CoSoDuLieu|null $csdl Cho phép inject kết nối khác (phục vụ test),
     *                              nếu bỏ trống sẽ dùng Singleton mặc định.
     */
    public function __construct(?CoSoDuLieu $csdl = null)
    {
        $this->csdl = $csdl ?? CoSoDuLieu::layInstance();
    }

    /**
     * Tạo mới một phiếu chi.
     *
     * @param array $duLieu    Dữ liệu từ form (ma_yc, ngay_chi, so_tien, ghi_chu, noi_dung).
     * @param array $nguoiDung Thông tin người dùng hiện tại (lấy từ session).
     *
     * @return array{ok:bool,thong_diep:string,ma_pc:string} Kết quả xử lý.
     */
    public function tao(array $duLieu, array $nguoiDung): array
    {
        $maNv = $nguoiDung['ma_nv'] ?? null;
        if (!$maNv) {
            // Không có MaNV thì không thể lưu để đảm bảo toàn vẹn dữ liệu
            return ['ok' => false, 'thong_diep' => 'Không xác định nhân viên thực hiện. Vui lòng đăng nhập lại.'];
        }

        // Bảng `phieuchi` KHÔNG có cột LoaiChi → chỉ chèn đúng các cột đang tồn tại
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

        // Lấy ID tự tăng cuối cùng (MaPC được sinh bởi trigger nhưng vẫn dùng insert_id để log)
        $maPhieu = (string) $this->csdl->ketNoi()->insert_id;

        if ($thanhCong && $maYc) {
            $this->csdl->truyVan('UPDATE yeucauboithuong SET TrangThai = \'Đã chi trả\' WHERE MaYC = ?', [$maYc]);
        }

        return [
            'ok' => (bool) $thanhCong,
            'thong_diep' => $thanhCong ? 'Thêm phiếu chi thành công!' : 'Không thể tạo phiếu chi.',
            'ma_pc' => $maPhieu,
        ];
    }

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

    public function xoa(string $maPc): array
    {
        $thanhCong = $this->csdl->truyVan('UPDATE phieuchi SET TrangThai = \'Đã hủy\' WHERE MaPC = ?', [$maPc]);
        return [
            'ok' => (bool) $thanhCong,
            'thong_diep' => $thanhCong ? 'Xóa phiếu chi thành công!' : 'Không thể xóa phiếu chi.',
            'ma_pc' => $maPc,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
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
     * Yêu cầu bồi thường đã duyệt chưa lập phiếu chi.
     *
     * @return array<int, array<string, mixed>>
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

