<?php

declare(strict_types=1);

namespace UngDung\HoTro;

use UngDung\DichVu\CoSoDuLieu;

/**
 * ====================================
 * CÔNG CỤ DỮ LIỆU - HELPER LÀM SẠCH DỮ LIỆU
 * ====================================
 * 
 * Cung cấp các hàm helper để xử lý dữ liệu:
 *   - Làm sạch chuỗi (escape, trim).
 *   - Chuyển đổi kiểu dữ liệu (int, float).
 *   - Giá trị mặc định nếu dữ liệu không hợp lệ.
 * 
 * Sử dụng:
 *   DuLieu::lamSach($_POST['ten']);        // Escape chuỗi từ form
 *   DuLieu::so($_GET['trang'], 1);         // Convert sang int (mặc định 1)
 *   DuLieu::soThuc($_POST['gia'], 0.0);    // Convert sang float (mặc định 0.0)
 */
class DuLieu
{
    /**
     * Làm sạch chuỗi - loại bỏ khoảng trắng và escape ký tự đặc biệt.
     * 
     * Điều này ngăn chặn SQL injection nhưng không thay thế prepared statements.
     * Luôn dùng prepared statements cho câu lệnh SQL!
     * 
     * @param mixed $giaTri  Giá trị cần làm sạch (bất kỳ kiểu nào)
     * @return string  Chuỗi đã làm sạch
     * 
     * Ví dụ:
     *   DuLieu::lamSach("  Nguyễn O'Hara  ")  → "Nguyễn O\'Hara"
     */
    public static function lamSach($giaTri): string
    {
        $text = trim((string) $giaTri);
        return CoSoDuLieu::layInstance()->ketNoi()->real_escape_string($text);
    }

    /**
     * Chuyển đổi giá trị sang kiểu int.
     * 
     * @param mixed $giaTri      Giá trị (bất kỳ kiểu nào)
     * @param int   $macDinh     Giá trị mặc định nếu không thể convert
     * @return int  Giá trị đã convert hoặc giá trị mặc định
     * 
     * Ví dụ:
     *   DuLieu::so('123')      → 123
     *   DuLieu::so('abc', 0)   → 0  (không thể convert, dùng mặc định)
     *   DuLieu::so('12.5', 10) → 10 (không phải int thuần, dùng mặc định)
     */
    public static function so($giaTri, int $macDinh = 0): int
    {
        return is_numeric($giaTri) ? (int) $giaTri : $macDinh;
    }

    /**
     * Chuyển đổi giá trị sang kiểu float.
     * 
     * @param mixed $giaTri      Giá trị (bất kỳ kiểu nào)
     * @param float $macDinh     Giá trị mặc định nếu không thể convert
     * @return float  Giá trị đã convert hoặc giá trị mặc định
     * 
     * Ví dụ:
     *   DuLieu::soThuc('123.45')     → 123.45
     *   DuLieu::soThuc('abc', 0.0)   → 0.0  (không thể convert)
     *   DuLieu::soThuc('12,500')     → 0.0  (dấu phẩy không hợp lệ cho float PHP)
     */
    public static function soThuc($giaTri, float $macDinh = 0.0): float
    {
        return is_numeric($giaTri) ? (float) $giaTri : $macDinh;
    }
}
?>

