<?php
namespace App\Models;

use Core\Model;
use Core\Database;

class Lichsu extends \Core\Model
{
    protected $table = 'qlbh_lichsu';
    protected $primaryKey = 'id';

    public function __construct(Database $db)
    {
        parent::__construct($db);
    }
}
