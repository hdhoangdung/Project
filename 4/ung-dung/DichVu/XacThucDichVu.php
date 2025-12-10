<?php

declare(strict_types=1);

namespace UngDung\DichVu;

/**
 * ====================================
 * DỊCH VỤ XÁC THỰC - QUẢN LÝ NGƯỜI DÙNG
 * ====================================
 * 
 * Quản lý đăng nhập, đăng xuất và kiểm tra quyền hạn.
 * 
 * Tính năng:
 *   - Kiểm tra đã đăng nhập hay chưa.
 *   - Lưu thông tin người dùng vào session.
 *   - Kiểm tra quyền hạn (role) của người dùng.
 *   - Chuyển hướng tự động nếu chưa đăng nhập hoặc không có quyền.
 * 
 * Sử dụng:
 *   $xacThuc = new XacThucDichVu();
 *   if (!$xacThuc->daDangNhap()) { header('Location: /dang-nhap.php'); exit; }
 *   $xacThuc->batBuocVaiTro('KeToan');  // Chỉ cho phép người dùng có role KeToan
 */
class XacThucDichVu
{
    // Khóa lưu trữ thông tin người dùng trong session
    private const KHOA_NGUOI_DUNG = 'NGUOI_DUNG';

    /**
     * Constructor - khởi tạo session khi tạo dịch vụ.
     */
    public function __construct()
    {
        PhienDichVu::batDau();
    }

    /**
     * Kiểm tra xem người dùng đã đăng nhập hay chưa.
     * 
     * @return bool  true nếu đã đăng nhập, false nếu chưa
     */
    public function daDangNhap(): bool
    {
        return (bool) PhienDichVu::lay(self::KHOA_NGUOI_DUNG);
    }

    /**
     * Lấy thông tin người dùng hiện tại từ session.
     * 
     * @return array<string, mixed>|null  Mảng thông tin người dùng hoặc null nếu chưa đăng nhập
     */
    public function nguoiDung(): ?array
    {
        /** @var array|null $user */
        $user = PhienDichVu::lay(self::KHOA_NGUOI_DUNG);
        return $user;
    }

    /**
     * Đăng nhập - lưu thông tin người dùng vào session.
     * 
     * @param array $thongTinNguoiDung  Mảng chứa các key: id, ma_nv, username, name, vai_tro, phong_ban, etc.
     * 
     * Ví dụ:
     *   $xacThuc->dangNhap([
     *       'id' => 1,
     *       'ma_nv' => 'NV001',
     *       'username' => 'ketoan',
     *       'name' => 'Nguyễn Văn A',
     *       'vai_tro' => 'KeToan'
     *   ]);
     */
    public function dangNhap(array $thongTinNguoiDung): void
    {
        PhienDichVu::dat(self::KHOA_NGUOI_DUNG, $thongTinNguoiDung);
    }

    /**
     * Đăng xuất - xóa thông tin người dùng khỏi session.
     * Hủy hoàn toàn session.
     */
    public function dangXuat(): void
    {
        PhienDichVu::xoa(self::KHOA_NGUOI_DUNG);  // Xóa thông tin người dùng
        session_destroy();                         // Hủy session
    }

    /**
     * ====================================
     * KIỂM TRA QUYỀN HẠN (ROLE-BASED)
     * ====================================
     * 
     * Bắt buộc người dùng phải:
     *   1. Đã đăng nhập
     *   2. Có role phù hợp
     * 
     * Nếu không đủ điều kiện, chuyển hướng tới trang login hoặc trang unauthorized.
     * 
     * @param string|string[] $vaiTro              Role được phép (chuỗi hoặc mảng)
     * @param string          $chuaDangNhap        URL chuyển hướng nếu chưa đăng nhập
     * @param string          $saiQuyen            URL chuyển hướng nếu sai quyền
     * 
     * Ví dụ:
     *   $xacThuc->batBuocVaiTro('KeToan');                 // Chỉ KeToan
     *   $xacThuc->batBuocVaiTro(['KeToan', 'QuanLy']);     // KeToan hoặc QuanLy
     */
    public function batBuocVaiTro($vaiTro, string $chuaDangNhap = '/dang-nhap.php', string $saiQuyen = '/'): void
    {
        // Kiểm tra đã đăng nhập chưa
        if (!$this->daDangNhap()) {
            header('Location: ' . $chuaDangNhap);
            exit;
        }

        // Lấy role hiện tại
        $nguoiDung = $this->nguoiDung();
        $role = $nguoiDung['vai_tro'] ?? '';
        
        // Kiểm tra xem role có phù hợp không
        $allowed = is_array($vaiTro) ? in_array($role, $vaiTro, true) : $role === $vaiTro;

        // Nếu không có quyền, chuyển hướng
        if (!$allowed) {
            header('Location: ' . $saiQuyen);
            exit;
        }
    }

    /**
     * Chuyển hướng người dùng tới trang phù hợp theo role.
     * (Hiện tại chuyển tới /ke-toan/index.php)
     */
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
?>

