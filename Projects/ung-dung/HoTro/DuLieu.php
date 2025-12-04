<?php

declare(strict_types=1);

namespace UngDung\HoTro;

use UngDung\DichVu\CoSoDuLieu;

class DuLieu
{
    public static function lamSach($giaTri): string
    {
        $text = trim((string) $giaTri);
        return CoSoDuLieu::layInstance()->ketNoi()->real_escape_string($text);
    }

    public static function so($giaTri, int $macDinh = 0): int
    {
        return is_numeric($giaTri) ? (int) $giaTri : $macDinh;
    }

    public static function soThuc($giaTri, float $macDinh = 0.0): float
    {
        return is_numeric($giaTri) ? (float) $giaTri : $macDinh;
    }
}

