<?php
namespace App\Models;

use Core\Model;
use Core\Database;

class PhieuThu extends \Core\Model
{
    protected $table = 'qlbh_phieuthu';
    protected $primaryKey = 'MaPT';

    public function __construct(Database $db)
    {
        parent::__construct($db);
    }

    public function sumByPeriod($type, $year, $month = null)
    {
        // type: month, quarter, year
        if ($type === 'year') {
            $sql = "SELECT SUM(SoTien) as total FROM {$this->table} WHERE YEAR(NgayThu) = :y AND TrangThai = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':y' => $year]);
            $r = $stmt->fetch(); return $r['total'] ?: 0.0;
        }
        if ($type === 'month' && $month) {
            $sql = "SELECT SUM(SoTien) as total FROM {$this->table} WHERE YEAR(NgayThu) = :y AND MONTH(NgayThu) = :m AND TrangThai = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':y' => $year, ':m' => $month]);
            $r = $stmt->fetch(); return $r['total'] ?: 0.0;
        }
        if ($type === 'quarter' && $month) {
            $q = intval(($month - 1) / 3) + 1;
            $sql = "SELECT SUM(SoTien) as total FROM {$this->table} WHERE YEAR(NgayThu) = :y AND QUARTER(NgayThu) = :qtr AND TrangThai = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':y' => $year, ':qtr' => $q]);
            $r = $stmt->fetch(); return $r['total'] ?: 0.0;
        }
        return 0.0;
    }
}
