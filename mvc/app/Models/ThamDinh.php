<?php
namespace App\Models;

use Core\Model;
use Core\Database;

class ThamDinh extends \Core\Model
{
    protected $table = 'qlbh_thamdinh';
    protected $primaryKey = 'MaTD';

    public function __construct(Database $db)
    {
        parent::__construct($db);
    }
}
