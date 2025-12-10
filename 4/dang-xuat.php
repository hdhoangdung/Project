<?php

declare(strict_types=1);

use UngDung\DichVu\NhatKyDichVu;
use UngDung\DichVu\XacThucDichVu;

require_once __DIR__ . '/cau-hinh.php';

$xacThuc = new XacThucDichVu();
$nhatKy = new NhatKyDichVu();
$nguoiDung = $xacThuc->nguoiDung();

if ($nguoiDung) {
    $nhatKy->ghi('TaiKhoan', (string) ($nguoiDung['id'] ?? $nguoiDung['ma_nv']), 'LOGOUT', null, [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'time' => date('Y-m-d H:i:s'),
    ]);
}

$xacThuc->dangXuat();

header('Location: /dang-nhap.php?logout=1');
exit;