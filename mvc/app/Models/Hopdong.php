<?php
namespace App\Models;

use Core\Model;
use Core\Database;

class Hopdong extends \Core\Model
{
    protected $table = 'qlbh_hopdong';
    protected $primaryKey = 'MaHD';

    public function __construct(Database $db)
    {
        parent::__construct($db);
    }

    public function isExpired($mahd)
    {
        $sql = "SELECT NgayKT FROM `{$this->table}` WHERE MaHD = :m LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':m' => $mahd]);
        $row = $stmt->fetch();
        if (!$row) return true;
        $ngaykt = $row['NgayKT'];
        return (strtotime($ngaykt) < strtotime(date('Y-m-d')));
    }

    public function totalPaid($mahd)
    {
        $sql = "SELECT SUM(SoTien) as paid FROM qlbh_phieuthu WHERE MaHD = :m AND TrangThai = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':m' => $mahd]);
        $row = $stmt->fetch();
        return $row['paid'] ? (float)$row['paid'] : 0.0;
    }
}
