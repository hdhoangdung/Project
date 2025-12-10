<?php
namespace App\Models;

use Core\Model;
use Core\Database;

class Taikhoan extends \Core\Model
{
    protected $table = 'qlbh_taikhoan';
    protected $primaryKey = 'MaTK';

    public function __construct(Database $db)
    {
        parent::__construct($db);
    }

    public function findByUsername($username)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `TenTK` = :u LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':u' => $username]);
        return $stmt->fetch();
    }
}
