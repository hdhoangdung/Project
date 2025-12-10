<?php
namespace App\Core;

use PDO;
use PDOException;

/**
 * Database - PDO Singleton (Module 0A)
 * Basic connection only - logging added in 0C
 */
class Database
{
    private static $instance = null;
    private $pdo;

    /**
     * Private constructor - enforce singleton
     */
    private function __construct()
    {
        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s;charset=%s',
            DB_DRIVER,
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                $options
            );
        } catch (PDOException $e) {
            $msg = DEBUG ? 'Database Connection Error: ' . $e->getMessage() : 'Database Connection Error';
            die($msg);
        }
    }

    /**
     * Get singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get PDO instance for queries
     */
    public function getPDO()
    {
        return $this->pdo;
    }

    /**
     * Close connection
     */
    public function close()
    {
        $this->pdo = null;
        self::$instance = null;
    }
}

