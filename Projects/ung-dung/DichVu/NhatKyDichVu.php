<?php

declare(strict_types=1);

namespace UngDung\DichVu;

class NhatKyDichVu
{
    private CoSoDuLieu $csdl;

    public function __construct(?CoSoDuLieu $csdl = null)
    {
        $this->csdl = $csdl ?? CoSoDuLieu::layInstance();
    }

    public function ghi(string $bang, string $maBanGhi, string $hanhDong, ?array $duLieuCu = null, ?array $duLieuMoi = null, ?string $maNv = null): void
    {
        $duLieuCuJson = $duLieuCu ? json_encode($duLieuCu, JSON_UNESCAPED_UNICODE) : null;
        $duLieuMoiJson = $duLieuMoi ? json_encode($duLieuMoi, JSON_UNESCAPED_UNICODE) : null;
        $sql = 'INSERT INTO lichsuthaydoi (BangDuLieu, MaBanGhi, HanhDong, DuLieuCu, DuLieuMoi, MaNV) VALUES (?,?,?,?,?,?)';
        $this->csdl->truyVan($sql, [$bang, $maBanGhi, $hanhDong, $duLieuCuJson, $duLieuMoiJson, $maNv]);
    }
}

