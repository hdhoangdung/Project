<?php
namespace App\Models;

use Core\Model;
use Core\Database;

class YeuCau extends \Core\Model
{
    protected $table = 'qlbh_yeucau';
    protected $primaryKey = 'MaYC';

    public function __construct(Database $db)
    {
        parent::__construct($db);
    }
}
