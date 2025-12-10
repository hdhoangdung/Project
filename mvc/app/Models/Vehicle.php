<?php
namespace App\Models;

use Core\Model;
use Core\Database;

class Vehicle extends \Core\Model
{
    protected $table = 'qlbh_xe';
    protected $primaryKey = 'MaXe';

    public function __construct(Database $db)
    {
        parent::__construct($db);
    }

    public function hasContract($maXe)
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) as c FROM qlbh_hopdong WHERE MaXe = :mx AND TrangThai = 1');
        $stmt->execute([':mx' => $maXe]);
        $r = $stmt->fetch();
        return ($r && $r['c'] > 0);
    }
}
