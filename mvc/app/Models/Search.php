<?php
namespace App\Models;

use Core\Model;
use Core\Database;

class Search extends \Core\Model
{
    public function __construct(Database $db)
    {
        // not tied to single table
        parent::__construct($db);
    }

    public function deep($q)
    {
        $pdo = $this->db;
        $res = [];
        $stmt = $pdo->prepare("SELECT * FROM qlbh_khachhang WHERE MaKH = :q OR CCCD = :q OR DienThoai = :q");
        $stmt->execute([':q' => $q]); $res['customers'] = $stmt->fetchAll();
        $stmt = $pdo->prepare("SELECT * FROM qlbh_xe WHERE MaXe = :q OR BienSoXe = :q OR SoKhung = :q OR SoMay = :q");
        $stmt->execute([':q' => $q]); $res['vehicles'] = $stmt->fetchAll();
        $stmt = $pdo->prepare("SELECT * FROM qlbh_hopdong WHERE MaHD = :q OR MaKH = :q OR MaXe = :q");
        $stmt->execute([':q' => $q]); $res['contracts'] = $stmt->fetchAll();
        $stmt = $pdo->prepare("SELECT * FROM qlbh_yeucau WHERE MaYC = :q OR MaHD = :q OR MaKH = :q OR MaXe = :q");
        $stmt->execute([':q' => $q]); $res['claims'] = $stmt->fetchAll();
        $stmt = $pdo->prepare("SELECT t.* FROM qlbh_thamdinh t JOIN qlbh_yeucau y ON t.MaYC = y.MaYC WHERE t.MaTD = :q OR y.MaYC = :q");
        $stmt->execute([':q' => $q]); $res['assessments'] = $stmt->fetchAll();
        $stmt = $pdo->prepare("SELECT p.* FROM qlbh_pheduyet p JOIN qlbh_thamdinh t ON p.MaTD = t.MaTD WHERE p.MaPD = :q OR t.MaTD = :q");
        $stmt->execute([':q' => $q]); $res['approvals'] = $stmt->fetchAll();
        $stmt = $pdo->prepare("SELECT * FROM qlbh_phieuchi WHERE MaPC = :q OR MaPD = :q");
        $stmt->execute([':q' => $q]); $res['payouts'] = $stmt->fetchAll();
        return $res;
    }
}
