<?php
namespace App\Models;

use Core\Model;
use Core\Database;

class PheDuyet extends \Core\Model
{
    protected $table = 'qlbh_pheduyet';
    protected $primaryKey = 'MaPD';

    public function __construct(Database $db)
    {
        parent::__construct($db);
    }
}
