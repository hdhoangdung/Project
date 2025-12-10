<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;

/**
 * AuthController - Authentication Handler (Module 0C)
 * Complete login/logout implementation with session management
 */
class AuthController extends Controller
{
    /**
     * Display login form
     */
    public function login()
    {
        // Render login view without layout
        $error = isset($_SESSION['error']) ? $_SESSION['error'] : null;
        unset($_SESSION['error']);
        
        require APP_ROOT . '/app/Views/Auth/login.php';
    }

    /**
     * Handle login submission (POST)
     */
    public function authenticate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?c=Auth&m=login');
            exit;
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $auth = Auth::getInstance();
        
        if ($auth->login($username, $password)) {
            // Login successful - redirect to customer list
            header('Location: ' . BASE_URL . '?c=Customer&m=list');
            exit;
        } else {
            // Login failed
            $_SESSION['error'] = 'Tên đăng nhập hoặc mật khẩu không đúng!';
            header('Location: ' . BASE_URL . '?c=Auth&m=login');
            exit;
        }
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        $auth = Auth::getInstance();
        $auth->logout();
        
        $_SESSION['success'] = 'Đã đăng xuất thành công!';
        header('Location: ' . BASE_URL . '?c=Auth&m=login');
        exit;
    }
}
