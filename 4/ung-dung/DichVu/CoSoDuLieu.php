<?php

declare(strict_types=1);

namespace UngDung\DichVu;

use Exception;
use mysqli;
use mysqli_result;

/**
 * ====================================
 * DỊCH VỤ CƠ SỞ DỮ LIỆU - QUẢN LÝ KẾT NỐI
 * ====================================
 * 
 * Lớp Singleton quản lý kết nối MySQL.
 * 
 * Tính năng chính:
 *   - Bảo đảm chỉ một kết nối duy nhất (Singleton pattern).
 *   - Hỗ trợ prepared statements để ngăn SQL injection.
 *   - Cung cấp các phương thức helper: truyVan(), layDong(), layTatCa(), layGiaTri().
 *   - Xử lý lỗi kết nối một cách an toàn.
 * 
 * Sử dụng:
 *   $csdl = CoSoDuLieu::layInstance();
 *   $ketQua = $csdl->truyVan('SELECT * FROM bang WHERE id = ?', [1]);
 */
class CoSoDuLieu
{
    // Instance duy nhất (Singleton)
    private static ?self $instance = null;

    // Kết nối MySQL
    private mysqli $ketNoi;

    /**
     * Constructor private - không cho phép new trực tiếp.
     * Bắc nối tới MySQL server.
     */
    private function __construct()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->ketNoi = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->ketNoi->connect_error) {
            throw new Exception('Không thể kết nối CSDL: ' . $this->ketNoi->connect_error);
        }
        $this->ketNoi->set_charset(DB_CHARSET);
    }

    /**
     * Lấy instance duy nhất (Singleton).
     * Nếu instance chưa tồn tại, tạo mới một instance.
     */
    public static function layInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Trả về kết nối mysqli thô để dùng trực tiếp nếu cần.
     */
    public function ketNoi(): mysqli
    {
        return $this->ketNoi;
    }

    /**
     * =====================================
     * PHƯƠNG THỨC TRUYỀN VẤN (QUERY)
     * =====================================
     * 
     * Thực thi câu lệnh SQL với hoặc không có tham số.
     * Hỗ trợ prepared statements để bảo vệ khỏi SQL injection.
     * 
     * @param string $sql       Câu lệnh SQL (dùng ? cho tham số)
     * @param array  $thamSo   Mảng tham số để bind
     * @return mysqli_result|bool  Kết quả truy vấn hoặc true/false
     * 
     * Ví dụ:
     *   $ketQua = $csdl->truyVan('SELECT * FROM users WHERE id = ?', [5]);
     *   $thanhCong = $csdl->truyVan('INSERT INTO logs (tieu_de) VALUES (?)', ['Event 1']);
     */
    public function truyVan(string $sql, array $thamSo = [])
    {
        // Nếu không có tham số, thực thi trực tiếp
        if (empty($thamSo)) {
            return $this->ketNoi->query($sql);
        }

        // Prepared statement - bảo vệ SQL injection
        $stmt = $this->ketNoi->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Không thể chuẩn bị truy vấn: ' . $this->ketNoi->error);
        }

        // Xác định kiểu dữ liệu cho từng tham số (i=int, d=float, s=string)
        $types = '';
        $values = [];
        foreach ($thamSo as $thamSoItem) {
            if (is_int($thamSoItem)) {
                $types .= 'i';
            } elseif (is_float($thamSoItem)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $values[] = $thamSoItem;
        }

        // Bind tham số vào prepared statement
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        $ketQua = $stmt->get_result();

        // Nếu có kết quả (SELECT), trả về mysqli_result
        if ($ketQua instanceof mysqli_result) {
            $stmt->close();
            return $ketQua;
        }

        // Nếu không có kết quả (INSERT, UPDATE, DELETE), trả về true/false
        $thanhCong = $stmt->affected_rows >= 0;
        $stmt->close();

        return $thanhCong;
    }

    /**
     * Lấy một dòng kết quả dưới dạng mảng liên hợp.
     * 
     * @return array<string, mixed>|null  Một dòng dữ liệu hoặc null nếu không có kết quả
     * 
     * Ví dụ:
     *   $nguoiDung = $csdl->layDong('SELECT * FROM users WHERE id = ?', [1]);
     *   if ($nguoiDung) {
     *       echo $nguoiDung['ho_ten'];
     *   }
     */
    public function layDong(string $sql, array $thamSo = []): ?array
    {
        $ketQua = $this->truyVan($sql, $thamSo);
        if ($ketQua instanceof mysqli_result) {
            $row = $ketQua->fetch_assoc();
            $ketQua->free();
            return $row ?: null;
        }

        return null;
    }

    /**
     * Lấy tất cả kết quả dưới dạng mảng các mảng liên hợp.
     * 
     * @return array<int, array<string, mixed>>  Danh sách các dòng dữ liệu
     * 
     * Ví dụ:
     *   $cacNguoiDung = $csdl->layTatCa('SELECT * FROM users ORDER BY id DESC');
     *   foreach ($cacNguoiDung as $nd) {
     *       echo $nd['ho_ten'] . '<br>';
     *   }
     */
    public function layTatCa(string $sql, array $thamSo = []): array
    {
        $ketQua = $this->truyVan($sql, $thamSo);
        if ($ketQua instanceof mysqli_result) {
            $rows = $ketQua->fetch_all(MYSQLI_ASSOC);
            $ketQua->free();
            return $rows;
        }

        return [];
    }

    /**
     * Lấy một giá trị đơn (ví dụ: COUNT, SUM, MAX).
     * Trả về giá trị của cột đầu tiên của dòng đầu tiên.
     * 
     * @return mixed|null  Một giá trị hoặc null nếu không có kết quả
     * 
     * Ví dụ:
     *   $soLuongNguoiDung = $csdl->layGiaTri('SELECT COUNT(*) FROM users');
     *   $tongTien = $csdl->layGiaTri('SELECT SUM(tien) FROM hoa_don WHERE trang_thai = ?', ['Thanh toan']);
     */
    public function layGiaTri(string $sql, array $thamSo = [])
    {
        $dong = $this->layDong($sql, $thamSo);
        if (!$dong) {
            return null;
        }

        // Trả về giá trị của cột đầu tiên
        return array_values($dong)[0] ?? null;
    }
}
?>