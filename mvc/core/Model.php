<?php
namespace App\Core;

/**
 * Model - Base Model Class (Module 0A)
 * Skeleton only - CRUD & queries added in 0B
 */
abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    /**
     * Constructor - get PDO instance
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getPDO();
    }

    /**
     * Get database instance
     */
    public function getDB()
    {
        return $this->db;
    }

    /**
     * Get table name
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Get primary key column name
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    // CRUD methods to be implemented in Module 0B
    // - all()
    // - find()
    // - create()
    // - update()
    // - softDelete()
}

