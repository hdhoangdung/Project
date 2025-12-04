<?php

declare(strict_types=1);

namespace UngDung\HoTro;

class DinhDang
{
    /**
     * Định dạng tiền tệ VND.
     * Chấp nhận mọi kiểu số (int/float/string numeric) để tránh lỗi kiểu khi đọc từ DB.
     */
    public static function tien($soTien): string
    {
        return number_format((float) $soTien, 0, ',', '.') . ' đ';
    }

    public static function ngay(?string $ngay, string $dinhDang = 'd/m/Y'): string
    {
        if (!$ngay || $ngay === '0000-00-00') {
            return '';
        }

        return date($dinhDang, strtotime($ngay));
    }

    /**
     * Đọc số tiền thành chữ. Đầu vào được ép về float để an toàn.
     */
    public static function tienBangChu($amount): string
    {
        $amount = (float) $amount;
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

        if ($amount === 0.0) {
            return 'Không đồng';
        }

        $phanVi = ['', ' nghìn', ' triệu', ' tỷ', ' nghìn tỷ'];
        $so = (int) round($amount);
        $ketQua = '';
        $i = 0;

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
     * Đọc ba chữ số theo tiếng Việt.
     *
     * @param array<int, string> $phienAm
     */
    private static function docBaChuSo(int $number, array $phienAm): string
    {
        $tram = intdiv($number, 100);
        $chuc = intdiv($number % 100, 10);
        $donVi = $number % 10;

        $chuoi = '';
        if ($tram > 0) {
            $chuoi .= $phienAm[$tram] . ' trăm';
            if ($chuc === 0 && $donVi > 0) {
                $chuoi .= ' lẻ';
            }
        }

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

