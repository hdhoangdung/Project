<?php

declare(strict_types=1);

namespace UngDung\DichVu;

/**
 * ====================================
 * DỊCH VỤ NHẬT KÝ - AUDIT LOG
 * ====================================
 * 
 * Ghi lại tất cả hoạt động thay đổi dữ liệu.
 * Hỗ trợ audit trail để theo dõi và kiểm toán.
 * 
 * Sử dụng:
 *   $nhatKy = new NhatKyDichVu();
 *   $nhatKy->ghi('phieuthu', '123', 'CREATE', null, $duLieuMoi, 'NV001');
 *   $nhatKy->ghi('phieuthu', '123', 'UPDATE', $duLieuCu, $duLieuMoi, 'NV001');
 *   $nhatKy->ghi('phieuthu', '123', 'DELETE', $duLieuCu, null, 'NV001');
 */
class NhatKyDichVu
{
    /** Kết nối CSDL */
    private CoSoDuLieu $csdl;

    /**
     * Constructor - Khởi tạo với kết nối CSDL.
     * 
     * @param CoSoDuLieu|null $csdl Kết nối tùy chỉnh hoặc Singleton mặc định
     */
    public function __construct(?CoSoDuLieu $csdl = null)
    {
        $this->csdl = $csdl ?? CoSoDuLieu::layInstance();
    }

    /**
     * Ghi nhật ký thay đổi dữ liệu.
     * 
     * @param string       $bang        Tên bảng (VD: 'phieuthu', 'phieuchi')
     * @param string       $maBanGhi    ID của bản ghi bị thay đổi
     * @param string       $hanhDong    Loại hành động: 'CREATE', 'UPDATE', 'DELETE', 'LOGIN', 'LOGIN_FAILED', 'LOGOUT'
     * @param array|null   $duLieuCu   Dữ liệu trước thay đổi (NULL nếu CREATE)
     * @param array|null   $duLieuMoi  Dữ liệu sau thay đổi (NULL nếu DELETE)
     * @param string|null  $maNv       Mã nhân viên thực hiện hành động
     * 
     * Ví dụ:
     *   // Tạo mới
     *   $nhatKy->ghi('phieuthu', 'PT001', 'CREATE', null, ['so_tien'=>100000], 'NV001');
     *   
     *   // Cập nhật
     *   $nhatKy->ghi('phieuthu', 'PT001', 'UPDATE', 
     *       ['so_tien'=>100000], 
     *       ['so_tien'=>120000], 
     *       'NV001');
     *   
     *   // Xóa
     *   $nhatKy->ghi('phieuthu', 'PT001', 'DELETE', ['so_tien'=>100000], null, 'NV001');
     */
    public function ghi(
        string $bang,
        string $maBanGhi,
        string $hanhDong,
        ?array $duLieuCu = null,
        ?array $duLieuMoi = null,
        ?string $maNv = null
    ): void {
        // Chuyển dữ liệu sang JSON để lưu vào DB
        $duLieuCuJson = $duLieuCu ? json_encode($duLieuCu, JSON_UNESCAPED_UNICODE) : null;
        $duLieuMoiJson = $duLieuMoi ? json_encode($duLieuMoi, JSON_UNESCAPED_UNICODE) : null;

        // INSERT vào bảng lichsuthaydoi
        $sql = 'INSERT INTO lichsuthaydoi (BangDuLieu, MaBanGhi, HanhDong, DuLieuCu, DuLieuMoi, MaNV) 
                VALUES (?,?,?,?,?,?)';
        
        $this->csdl->truyVan($sql, [
            $bang,
            $maBanGhi,
            $hanhDong,
            $duLieuCuJson,
            $duLieuMoiJson,
            $maNv
        ]);
    }
}
?>

