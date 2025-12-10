<?php

declare(strict_types=1);

namespace UngDung\HoTro;

/**
 * ====================================
 * CÔNG CỤ ĐỊNH DẠNG - FORMAT HỖ TRỢ
 * ====================================
 * 
 * Cung cấp các hàm định dạng dữ liệu:
 *   - Tiền tệ VND (số và chữ).
 *   - Ngày tháng theo múi giờ Việt Nam.
 * 
 * Sử dụng:
 *   DinhDang::tien(50000);                    // Trả về "50.000 đ"
 *   DinhDang::tienBangChu(50000);             // Trả về "Năm mươi nghìn đồng"
 *   DinhDang::ngay('2025-01-15', 'd/m/Y');    // Trả về "15/01/2025"
 */
class DinhDang
{
    /**
     * Định dạng tiền tệ VND.
     * 
     * Chấp nhận mọi kiểu số (int, float, string) để tránh lỗi khi đọc từ DB.
     * 
     * @param mixed $soTien  Số tiền (bất kỳ kiểu nào có thể convert thành float)
     * @return string  Chuỗi tiền với dấu phẩy phân cách hàng nghìn + " đ"
     * 
     * Ví dụ:
     *   DinhDang::tien(1500000)  → "1.500.000 đ"
     *   DinhDang::tien(1500)     → "1.500 đ"
     *   DinhDang::tien(123.45)   → "123 đ"  (làm tròn)
     */
    public static function tien($soTien): string
    {
        return number_format((float) $soTien, 0, ',', '.') . ' đ';
    }

    /**
     * Định dạng ngày tháng.
     * 
     * @param string|null $ngay        Chuỗi ngày (format: YYYY-MM-DD từ DB)
     * @param string      $dinhDang    Format ngày đầu ra (mặc định: d/m/Y)
     * @return string  Ngày đã định dạng hoặc chuỗi rỗng nếu ngày không hợp lệ
     * 
     * Ví dụ:
     *   DinhDang::ngay('2025-01-15')              → "15/01/2025"
     *   DinhDang::ngay('2025-01-15', 'd-m-Y')    → "15-01-2025"
     *   DinhDang::ngay(null)                      → ""
     *   DinhDang::ngay('0000-00-00')              → ""
     */
    public static function ngay(?string $ngay, string $dinhDang = 'd/m/Y'): string
    {
        // Nếu ngày trống hoặc là 0000-00-00, trả về chuỗi rỗng
        if (!$ngay || $ngay === '0000-00-00') {
            return '';
        }

        return date($dinhDang, strtotime($ngay));
    }

    /**
     * ====================================
     * ĐỌC SỐ TIỀN THÀNH CHỮ TIẾNG VIỆT
     * ====================================
     * 
     * Chuyển đổi một số tiền thành chữ Việt.
     * Ví dụ: 123.456 → "Một trăm hai mươi ba nghìn bốn trăm năm mươi sáu đồng"
     * 
     * @param mixed $amount  Số tiền (bất kỳ kiểu nào)
     * @return string  Đọc chữ của số tiền
     */
    public static function tienBangChu($amount): string
    {
        $amount = (float) $amount;
        
        // Bảng chữ số
        $phienAm = [
            0 => 'không',
            1 => 'một',
            2 => 'hai',
            3 => 'ba',
            4 => 'bốn',
            5 => 'năm',
            6 => 'sáu',
            7 => 'bảy',
            8 => 'tám',
            9 => 'chín',
        ];

        // Nếu số = 0
        if ($amount === 0.0) {
            return 'Không đồng';
        }

        // Các phần vị (hàng đơn vị)
        $phanVi = ['', ' nghìn', ' triệu', ' tỷ', ' nghìn tỷ'];
        $so = (int) round($amount);
        $ketQua = '';
        $i = 0;

        // Chia số thành từng nhóm 3 chữ số
        while ($so > 0 && $i < count($phanVi)) {
            $baChuSo = $so % 1000;
            if ($baChuSo !== 0) {
                $ketQua = self::docBaChuSo($baChuSo, $phienAm) . $phanVi[$i] . ($ketQua ? ' ' . $ketQua : '');
            }
            $so = intdiv($so, 1000);
            $i++;
        }

        return ucfirst(trim($ketQua)) . ' đồng';
    }

    /**
     * Đọc ba chữ số (0-999) theo tiếng Việt.
     * Hàm helper nội bộ cho tienBangChu().
     * 
     * @param int                $number   Ba chữ số (0-999)
     * @param array<int, string> $phienAm  Bảng chữ số
     * @return string  Chuỗi đọc ba chữ số
     */
    private static function docBaChuSo(int $number, array $phienAm): string
    {
        $tram = intdiv($number, 100);      // Chữ số hàng trăm
        $chuc = intdiv($number % 100, 10); // Chữ số hàng chục
        $donVi = $number % 10;              // Chữ số hàng đơn vị

        $chuoi = '';
        
        // Xử lý hàng trăm
        if ($tram > 0) {
            $chuoi .= $phienAm[$tram] . ' trăm';
            if ($chuc === 0 && $donVi > 0) {
                $chuoi .= ' lẻ';
            }
        }

        // Xử lý hàng chục
        if ($chuc > 1) {
            $chuoi .= ($chuoi ? ' ' : '') . $phienAm[$chuc] . ' mươi';
            if ($donVi === 1) {
                $chuoi .= ' mốt';
            } elseif ($donVi === 5) {
                $chuoi .= ' lăm';
            } elseif ($donVi > 0) {
                $chuoi .= ' ' . $phienAm[$donVi];
            }
        } elseif ($chuc === 1) {
            $chuoi .= ($chuoi ? ' ' : '') . 'mười';
            if ($donVi === 5) {
                $chuoi .= ' lăm';
            } elseif ($donVi > 0) {
                $chuoi .= ' ' . $phienAm[$donVi];
            }
        } elseif ($chuc === 0 && $donVi > 0 && $tram === 0) {
            $chuoi .= $phienAm[$donVi];
        } elseif ($chuc === 0 && $donVi > 0 && $tram > 0) {
            $chuoi .= ' ' . $phienAm[$donVi];
        }

        return trim($chuoi);
    }
}
?>

