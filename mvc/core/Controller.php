<?php
namespace App\Core;

/**
 * Controller - Base Controller Class (Module 0A)
 * Foundation only - RBAC & business logic added in 0C+
 */
abstract class Controller
{
    protected $db;

    /**
     * Constructor - initialize database connection
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getPDO();
    }

    /**
     * Load and render a view file
     * @param string $path - Full path to view file
     * @param array $data - Variables to extract in view scope
     */
    protected function view($path, $data = [])
    {
        if (!file_exists($path)) {
            die('View not found: ' . htmlspecialchars($path));
        }
        extract($data);
        include $path;
    }

    /**
     * Redirect to another URL
     * @param string $url - Target URL
     */
    protected function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }
}

