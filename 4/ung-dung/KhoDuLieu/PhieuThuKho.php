<?php

declare(strict_types=1);

namespace UngDung\KhoDuLieu;

use UngDung\DichVu\CoSoDuLieu;
use UngDung\HoTro\DuLieu;

class PhieuThuKho
{
    private CoSoDuLieu $csdl;

    public function __construct(?CoSoDuLieu $csdl = null)
    {
        $this->csdl = $csdl ?? CoSoDuLieu::layInstance();
    }

    public function tao(array $duLieu, array $nguoiDung): array
    {
        $maNv = $nguoiDung['ma_nv'] ?? $nguoiDung['id'] ?? null;
        if (!$maNv) {
            return ['ok' => false, 'thong_diep' => 'Không xác định nhân viên thực hiện.'];
        }

        $kiemTra = $this->csdl->layGiaTri('SELECT COUNT(*) FROM phieuthu WHERE MaHD = ? AND TrangThai <> \'Đã hủy\'', [$duLieu['ma_hd']]);
        if ((int) $kiemTra > 0) {
            return ['ok' => false, 'thong_diep' => 'Hợp đồng này đã có phiếu thu trước đó!'];
        }

        $sql = 'INSERT INTO phieuthu (MaHD, NgayThu, SoTien, GhiChu, MaNV, TrangThai)
                VALUES (?, ?, ?, ?, ?, \'Hoạt động\')';
        $thanhCong = $this->csdl->truyVan($sql, [
            $duLieu['ma_hd'],
            $duLieu['ngay_thu'],
            $duLieu['so_tien'],
            $duLieu['ghi_chu'],
            $maNv,
        ]);

        $maPhieu = (string) $this->csdl->ketNoi()->insert_id;

        if ($thanhCong) {
            $this->csdl->truyVan('UPDATE hopdong SET TrangThai = \'Đã thanh toán\' WHERE MaHD = ?', [$duLieu['ma_hd']]);
        }

        return [
            'ok' => (bool) $thanhCong,
            'thong_diep' => $thanhCong ? 'Thêm phiếu thu thành công!' : 'Không thể thêm phiếu thu.',
            'ma_pt' => $maPhieu,
        ];
    }

    public function capNhat(array $duLieu): array
    {
        $sql = 'UPDATE phieuthu SET SoTien = ?, NgayThu = ?, GhiChu = ? WHERE MaPT = ?';
        $thanhCong = $this->csdl->truyVan($sql, [
            $duLieu['so_tien'],
            $duLieu['ngay_thu'],
            $duLieu['ghi_chu'],
            $duLieu['ma_pt'],
        ]);
        return [
            'ok' => (bool) $thanhCong,
            'thong_diep' => $thanhCong ? 'Cập nhật phiếu thu thành công!' : 'Không thể cập nhật phiếu thu.',
            'ma_pt' => $duLieu['ma_pt'],
        ];
    }

    public function xoa(string $maPt): array
    {
        $phieu = $this->csdl->layDong('SELECT * FROM phieuthu WHERE MaPT = ?', [$maPt]);
        if (!$phieu) {
            return ['ok' => false, 'thong_diep' => 'Không tìm thấy phiếu thu!'];
        }

        $thanhCong = $this->csdl->truyVan('UPDATE phieuthu SET TrangThai = \'Đã hủy\' WHERE MaPT = ?', [$maPt]);
        if ($thanhCong) {
            $this->csdl->truyVan('UPDATE hopdong SET TrangThai = \'Hiệu lực\' WHERE MaHD = ?', [$phieu['MaHD']]);
        }

        return [
            'ok' => (bool) $thanhCong,
            'thong_diep' => $thanhCong ? 'Xóa phiếu thu thành công!' : 'Không thể xóa phiếu thu.',
            'ma_pt' => $maPt,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function danhSach(array $boLoc = []): array
    {
        $dieuKien = ["g.TrangThai = 'Hoạt động'"];
        if (!empty($boLoc['tu_ngay'])) {
            $dieuKien[] = "g.NgayThu >= '" . DuLieu::lamSach($boLoc['tu_ngay']) . "'";
        }
        if (!empty($boLoc['den_ngay'])) {
            $dieuKien[] = "g.NgayThu <= '" . DuLieu::lamSach($boLoc['den_ngay']) . "'";
        }
        if (!empty($boLoc['tu_khoa'])) {
            $tuKhoa = DuLieu::lamSach($boLoc['tu_khoa']);
            $dieuKien[] = "(k.HoTen LIKE '%{$tuKhoa}%' OR x.BienSo LIKE '%{$tuKhoa}%' OR g.MaPT LIKE '%{$tuKhoa}%')";
        }

        $sql = "
            SELECT g.*, h.MaHD, k.HoTen, k.SoDienThoai AS SDT, k.Email,
                   x.BienSo
            FROM phieuthu g
            JOIN hopdong h ON g.MaHD = h.MaHD
            JOIN khachhang k ON h.MaKH = k.MaKH
            JOIN xeoto x ON h.MaXe = x.MaXe
            WHERE " . implode(' AND ', $dieuKien) . '
            ORDER BY g.NgayThu DESC, g.MaPT DESC
        ';

        return $this->csdl->layTatCa($sql);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function danhSachHopDong(): array
    {
        $sql = "
            SELECT h.MaHD, h.PhiBaoHiem, h.NgayLap,
                   k.HoTen, k.SoDienThoai,
                   x.BienSo, x.HangXe
            FROM hopdong h
            JOIN khachhang k ON h.MaKH = k.MaKH
            JOIN xeoto x ON h.MaXe = x.MaXe
            WHERE h.TrangThai = 'Hiệu lực'
            ORDER BY h.NgayLap DESC
        ";

        return $this->csdl->layTatCa($sql);
    }

    public function tongThu(array $boLoc = []): float
    {
        $dieuKien = ["TrangThai = 'Hoạt động'"];
        if (!empty($boLoc['tu_ngay'])) {
            $dieuKien[] = "NgayThu >= '" . DuLieu::lamSach($boLoc['tu_ngay']) . "'";
        }
        if (!empty($boLoc['den_ngay'])) {
            $dieuKien[] = "NgayThu <= '" . DuLieu::lamSach($boLoc['den_ngay']) . "'";
        }
        if (!empty($boLoc['tu_khoa'])) {
            $tuKhoa = DuLieu::lamSach($boLoc['tu_khoa']);
            $dieuKien[] = "(MaPT LIKE '%{$tuKhoa}%' OR GhiChu LIKE '%{$tuKhoa}%')";
        }

        $sql = 'SELECT COALESCE(SUM(SoTien),0) AS Tong FROM phieuthu WHERE ' . implode(' AND ', $dieuKien);
        return (float) $this->csdl->layGiaTri($sql);
    }

    public function thongTinIn(string $maPt): ?array
    {
        $sql = "
            SELECT pt.*, h.MaHD, h.NgayLap, h.NgayHetHan, h.PhiBaoHiem,
                   k.MaKH, k.HoTen, k.DiaChi, k.SoDienThoai, k.Email, k.CCCD,
                   x.BienSo, x.HangXe, x.DongXe, x.NamSanXuat, x.MauSac,
                   g.TenGoi, g.MoTa AS MoTaGoi
            FROM phieuthu pt
            JOIN hopdong h ON pt.MaHD = h.MaHD
            JOIN khachhang k ON h.MaKH = k.MaKH
            JOIN xeoto x ON h.MaXe = x.MaXe
            JOIN goibaohiem g ON h.MaGoi = g.MaGoi
            WHERE pt.MaPT = ?
        ";

        return $this->csdl->layDong($sql, [$maPt]);
    }
}

