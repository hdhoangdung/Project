<?php

declare(strict_types=1);

namespace UngDung\DichVu;

class ThongBaoDichVu
{
    private const KHOA_THONG_BAO = 'THONG_BAO_FLASH';

    public function __construct()
    {
        PhienDichVu::batDau();
    }

    public function dat(string $noiDung, string $loai = 'success'): void
    {
        PhienDichVu::dat(self::KHOA_THONG_BAO, [
            'noi_dung' => $noiDung,
            'loai' => $loai,
        ]);
    }

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

