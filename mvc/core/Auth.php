<?php
namespace App\Core;

use PDO;

/**
 * Auth - Authentication & Authorization (Module 0C)
 * Complete OOP session handling with role-based access control
 * 
 * Features:
 * - Secure login with bcrypt password hashing
 * - Session management
 * - Role-based access control (RBAC) for 4 roles
 * - Auto-login on request
 */
class Auth
{
    private static $instance = null;
    private $pdo;
    private $isLoggedIn = false;
    private $user = null;

    // Define the 4 system roles (constants) - Match database ENUM values
    const ROLE_CUSTOMER = 'khach_hang';
    const ROLE_CLAIMS = 'boi_thuong';
    const ROLE_VEHICLE = 'phuong_tien';
    const ROLE_ACCOUNTING = 'ke_toan';

    private function __construct()
    {
        $this->pdo = Database::getInstance()->getPDO();
        $this->checkSessionLogin();
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
     * Check if user is logged in via session
     */
    private function checkSessionLogin()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!empty($_SESSION['MaTK']) && !empty($_SESSION['TenTK']) && !empty($_SESSION['VaiTro'])) {
            $this->isLoggedIn = true;
            $this->user = [
                'MaTK' => $_SESSION['MaTK'],
                'TenTK' => $_SESSION['TenTK'],
                'VaiTro' => $_SESSION['VaiTro'],
            ];
        }
    }

    /**
     * Authenticate user with username and password
     * 
     * @param string $username - Login username
     * @param string $password - Plain password (will be verified against hash)
     * @return bool - True if login successful, false otherwise
     */
    public function login($username, $password)
    {
        if (empty($username) || empty($password)) {
            return false;
        }

        // Query user from database
        $sql = "SELECT MaTK, TenTK, MatKhau, VaiTro, TrangThai 
                FROM qlbh_taikhoan 
                WHERE TenTK = :username AND TrangThai = 'HoatDong' 
                LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // User not found or disabled
        if (!$user) {
            return false;
        }

        // Verify password with bcrypt
        if (!password_verify($password, $user['MatKhau'])) {
            return false;
        }

        // Set session variables
        $_SESSION['MaTK'] = $user['MaTK'];
        $_SESSION['TenTK'] = $user['TenTK'];
        $_SESSION['VaiTro'] = $user['VaiTro'];
        $_SESSION['LoginTime'] = time();

        // Update user state
        $this->isLoggedIn = true;
        $this->user = [
            'MaTK' => $user['MaTK'],
            'TenTK' => $user['TenTK'],
            'VaiTro' => $user['VaiTro'],
        ];

        // Log successful login
        Logger::getInstance()->log(
            $user['MaTK'],
            'login',
            'qlbh_taikhoan',
            $user['MaTK'],
            null,
            ['vaitro' => $user['VaiTro']]
        );

        return true;
    }

    /**
     * Logout current user
     */
    public function logout()
    {
        if ($this->isLoggedIn && $this->user) {
            // Log logout action
            Logger::getInstance()->log(
                $this->user['MaTK'],
                'logout',
                'qlbh_taikhoan',
                $this->user['MaTK'],
                null,
                null
            );
        }

        // Destroy session
        $_SESSION = [];
        session_destroy();
        $this->isLoggedIn = false;
        $this->user = null;
    }

    /**
     * Check if user has required role
     * 
     * @param string $requiredRole - Role to check
     * @return bool - True if user has role, false otherwise
     */
    public function checkRole($requiredRole)
    {
        return $this->isLoggedIn && $this->user['VaiTro'] === $requiredRole;
    }

    /**
     * Check if user has any of the given roles
     * 
     * @param array $roles - Array of allowed roles
     * @return bool - True if user has any of the roles
     */
    public function checkRoles(array $roles)
    {
        if (!$this->isLoggedIn) {
            return false;
        }
        return in_array($this->user['VaiTro'], $roles, true);
    }

    /**
     * Require specific role - exit with 403 if not authorized
     * 
     * @param string $requiredRole - Role required
     */
    public function requireRole($requiredRole)
    {
        if (!$this->checkRole($requiredRole)) {
            http_response_code(403);
            die('Access Denied: Insufficient Permissions');
        }
    }

    /**
     * Require one of multiple roles - exit with 403 if not authorized
     * 
     * @param array $roles - Array of allowed roles
     */
    public function requireRoles(array $roles)
    {
        if (!$this->checkRoles($roles)) {
            http_response_code(403);
            die('Access Denied: Insufficient Permissions');
        }
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn()
    {
        return $this->isLoggedIn;
    }

    /**
     * Get current logged-in user ID
     */
    public function getUserID()
    {
        return $this->isLoggedIn ? $this->user['MaTK'] : null;
    }

    /**
     * Get current username
     */
    public function getUsername()
    {
        return $this->isLoggedIn ? $this->user['TenTK'] : null;
    }

    /**
     * Get current user role
     */
    public function getRole()
    {
        return $this->isLoggedIn ? $this->user['VaiTro'] : null;
    }

    /**
     * Get all user data
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Hash password using bcrypt
     * 
     * @param string $password - Plain password
     * @return string - Hashed password
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify password against hash
     * 
     * @param string $password - Plain password
     * @param string $hash - Hashed password from database
     * @return bool - True if matches
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
}

