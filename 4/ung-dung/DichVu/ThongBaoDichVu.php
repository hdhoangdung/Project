<?php

declare(strict_types=1);

namespace UngDung\DichVu;

/**
 * ====================================
 * DỊCH VỤ THÔNG BÁO - FLASH MESSAGE
 * ====================================
 *
 * Quản lý các thông báo tạm thời (flash message).
 * Thông báo được hiển thị một lần duy nhất rồi tự hủy.
 *
 * Sử dụng:
 *   $thongBao = new ThongBaoDichVu();
 *   $thongBao->dat('Thao tác thành công!', 'success');
 *   $alert = $thongBao->lay();  // Trả về array hoặc null nếu hết hạn
 */
class ThongBaoDichVu
{
    // Khóa lưu trữ thông báo trong $_SESSION
    private const KHOA_THONG_BAO = 'THONG_BAO_FLASH';

    /**
     * Constructor - Khởi tạo phiên.
     */
    public function __construct()
    {
        PhienDichVu::batDau();
    }

    /**
     * Lưu thông báo vào session.
     *
     * @param string $noiDung Nội dung thông báo
     * @param string $loai    Loại thông báo: 'success', 'error', 'warning', 'info'
     */
    public function dat(string $noiDung, string $loai = 'success'): void
    {
        PhienDichVu::dat(self::KHOA_THONG_BAO, [
            'noi_dung' => $noiDung,
            'loai' => $loai,
        ]);
    }

    /**
     * Lấy và xóa thông báo từ session (Flash pattern).
     *
     * @return array<string, string>|null Mảng ['noi_dung'=>'...', 'loai'=>'...'] hoặc null
     */
    public function lay(): ?array
    {
        /** @var array|null $alert */
        $alert = PhienDichVu::lay(self::KHOA_THONG_BAO);
        if ($alert) {
            PhienDichVu::xoa(self::KHOA_THONG_BAO);
        }
        return $alert;
    }
}
?>

