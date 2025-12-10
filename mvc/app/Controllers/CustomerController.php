<?php
namespace App\Controllers;

use App\Models\Customer;
use App\Core\Auth;
use App\Core\Logger;
use Exception;

/**
 * Customer Controller - Vehicle Insurance Management System (Module 1B)
 * Complete OOP controller with RBAC, validation, and model integration
 * 
 * Methods:
 * - list() → Display paginated customer list
 * - create() → Handle new customer creation (GET/POST)
 * - edit($maKH) → Handle customer edit (GET/POST)
 * - delete($maKH) → Handle customer soft delete
 * - detail($maKH) → Display customer details with relations
 * - search() → Search customers by CCCD or phone
 * 
 * RBAC: All methods require 'khach_hang' role
 * Routing: index.php?c=Customer&m=method[&maKH=id]
 */
class CustomerController
{
    private $customerModel;
    private $auth;
    private $logger;

    public function __construct()
    {
        $this->customerModel = new Customer();
        $this->auth = Auth::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
     * Display paginated list of customers
     * RBAC: khach_hang role only
     */
    public function list()
    {
        $this->enforceRole(Auth::ROLE_CUSTOMER);
        
        $page = $_GET['page'] ?? 1;
        $pageSize = 20;
        
        $customers = $this->customerModel->list($page, $pageSize);
        $total = $this->customerModel->count();
        $totalPages = ceil($total / $pageSize);
        
        $currentPage = (int)$page;
        
        ob_start();
        require APP_ROOT . '/app/Views/layout/header.php';
        require APP_ROOT . '/app/Views/Customer/list.php';
        require APP_ROOT . '/app/Views/layout/footer.php';
        echo ob_get_clean();
    }

    /**
     * Handle new customer creation (GET shows form, POST processes)
     * RBAC: khach_hang role only
     */
    public function create()
    {
        $this->enforceRole(Auth::ROLE_CUSTOMER);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->handleCreatePost();
        }
        
        // GET - Show form
        $formError = null;
        
        ob_start();
        require APP_ROOT . '/app/Views/layout/header.php';
        require APP_ROOT . '/app/Views/Customer/create.php';
        require APP_ROOT . '/app/Views/layout/footer.php';
        echo ob_get_clean();
    }

    /**
     * Handle customer edit (GET shows form, POST processes)
     * RBAC: khach_hang role only
     */
    public function edit()
    {
        $this->enforceRole(Auth::ROLE_CUSTOMER);
        
        $maKH = $_GET['maKH'] ?? null;
        if (!$maKH) {
            $_SESSION['error'] = 'Không tìm thấy khách hàng!';
            header('Location: ' . BASE_URL . '?c=Customer&m=list');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->handleEditPost($maKH);
        }
        
        // GET - Show form with customer data
        $customer = $this->customerModel->getById($maKH);
        if (!$customer) {
            $_SESSION['error'] = 'Khách hàng không tồn tại!';
            header('Location: ' . BASE_URL . '?c=Customer&m=list');
            exit;
        }
        
        $formError = null;
        
        ob_start();
        require APP_ROOT . '/app/Views/layout/header.php';
        require APP_ROOT . '/app/Views/Customer/edit.php';
        require APP_ROOT . '/app/Views/layout/footer.php';
        echo ob_get_clean();
    }

    /**
     * Handle customer soft delete
     * RBAC: khach_hang role only
     */
    public function delete()
    {
        $this->enforceRole(Auth::ROLE_CUSTOMER);
        
        $maKH = $_GET['maKH'] ?? null;
        if (!$maKH) {
            $_SESSION['error'] = 'Không tìm thấy khách hàng!';
            header('Location: ' . BASE_URL . '?c=Customer&m=list');
            exit;
        }
        
        try {
            $result = $this->customerModel->softDelete($maKH, $_SESSION['MaTK']);
            
            if ($result) {
                $_SESSION['success'] = 'Khách hàng đã được xóa!';
            } else {
                $_SESSION['error'] = 'Không thể xóa khách hàng!';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '?c=Customer&m=list');
        exit;
    }

    /**
     * Display customer detail with vehicles, contracts, claims
     * RBAC: khach_hang role only
     */
    public function detail()
    {
        $this->enforceRole(Auth::ROLE_CUSTOMER);
        
        $maKH = $_GET['maKH'] ?? null;
        if (!$maKH) {
            $_SESSION['error'] = 'Không tìm thấy khách hàng!';
            header('Location: ' . BASE_URL . '?c=Customer&m=list');
            exit;
        }
        
        $customer = $this->customerModel->getById($maKH);
        if (!$customer) {
            $_SESSION['error'] = 'Khách hàng không tồn tại!';
            header('Location: ' . BASE_URL . '?c=Customer&m=list');
            exit;
        }
        
        $vehicles = $this->customerModel->getVehicles($maKH);
        $contracts = $this->customerModel->getContracts($maKH);
        $claims = $this->customerModel->getClaimsByCustomer($maKH);
        $statistics = $this->customerModel->getStatistics($maKH);
        
        ob_start();
        require APP_ROOT . '/app/Views/layout/header.php';
        require APP_ROOT . '/app/Views/Customer/detail.php';
        require APP_ROOT . '/app/Views/layout/footer.php';
        echo ob_get_clean();
    }

    /**
     * Search customers by CCCD or phone
     * RBAC: khach_hang role only
     */
    public function search()
    {
        $this->enforceRole(Auth::ROLE_CUSTOMER);
        
        $cccd = $_POST['cccd'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $searchPerformed = false;
        $results = [];
        
        if (!empty($cccd) || !empty($phone)) {
            $searchPerformed = true;
            $results = $this->customerModel->search($cccd, $phone);
        }
        
        ob_start();
        require APP_ROOT . '/app/Views/layout/header.php';
        require APP_ROOT . '/app/Views/Customer/search.php';
        require APP_ROOT . '/app/Views/layout/footer.php';
        echo ob_get_clean();
    }

    // ========== Helper Methods ==========

    /**
     * Enforce role-based access control
     * Redirect to login if not authenticated or wrong role
     */
    private function enforceRole($requiredRole)
    {
        if (!$this->auth->isLoggedIn()) {
            $_SESSION['error'] = 'Vui lòng đăng nhập!';
            header('Location: ' . BASE_URL . '?c=Auth&m=login');
            exit;
        }
        
        if (!$this->auth->checkRole($requiredRole)) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập!';
            header('Location: ' . BASE_URL . '?c=Customer&m=list');
            exit;
        }
    }

    /**
     * Handle POST for create form
     */
    private function handleCreatePost()
    {
        $data = [
            'HoTen' => $_POST['HoTen'] ?? '',
            'NgaySinh' => $_POST['NgaySinh'] ?? null,
            'CCCD' => $_POST['CCCD'] ?? '',
            'DiaChi' => $_POST['DiaChi'] ?? '',
            'SDT' => $_POST['SDT'] ?? '',
            'Email' => $_POST['Email'] ?? '',
        ];
        
        try {
            // Validate
            if (empty($data['HoTen'])) {
                throw new Exception('Tên khách hàng không được để trống!');
            }
            if (!preg_match('/^\d{12}$/', $data['CCCD'])) {
                throw new Exception('CCCD phải đúng 12 chữ số!');
            }
            if (!empty($data['SDT']) && !preg_match('/^[+]?\d{9,14}$/', $data['SDT'])) {
                throw new Exception('Số điện thoại không hợp lệ!');
            }
            if (!empty($data['Email']) && !filter_var($data['Email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email không hợp lệ!');
            }
            
            // Generate ID
            $maKH = 'KH' . date('YmdHis');
            
            // Create
            $result = $this->customerModel->create(array_merge(['MaKH' => $maKH], $data), $_SESSION['MaTK']);
            
            if ($result) {
                $_SESSION['success'] = 'Khách hàng mới đã được thêm!';
                header('Location: ' . BASE_URL . '?c=Customer&m=list');
                exit;
            }
        } catch (Exception $e) {
            $formError = $e->getMessage();
        }
        
        // Re-display form with error
        ob_start();
        require APP_ROOT . '/app/Views/layout/header.php';
        require APP_ROOT . '/app/Views/Customer/create.php';
        require APP_ROOT . '/app/Views/layout/footer.php';
        echo ob_get_clean();
    }

    /**
     * Handle POST for edit form
     */
    private function handleEditPost($maKH)
    {
        $data = [
            'HoTen' => $_POST['HoTen'] ?? '',
            'NgaySinh' => $_POST['NgaySinh'] ?? null,
            'CCCD' => $_POST['CCCD'] ?? '',
            'DiaChi' => $_POST['DiaChi'] ?? '',
            'SDT' => $_POST['SDT'] ?? '',
            'Email' => $_POST['Email'] ?? '',
        ];
        
        try {
            // Validate
            if (empty($data['HoTen'])) {
                throw new Exception('Tên khách hàng không được để trống!');
            }
            if (!preg_match('/^\d{12}$/', $data['CCCD'])) {
                throw new Exception('CCCD phải đúng 12 chữ số!');
            }
            if (!empty($data['SDT']) && !preg_match('/^[+]?\d{9,14}$/', $data['SDT'])) {
                throw new Exception('Số điện thoại không hợp lệ!');
            }
            if (!empty($data['Email']) && !filter_var($data['Email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email không hợp lệ!');
            }
            
            // Update
            $result = $this->customerModel->update($maKH, $data, $_SESSION['MaTK']);
            
            if ($result) {
                $_SESSION['success'] = 'Khách hàng đã được cập nhật!';
                header('Location: ' . BASE_URL . '?c=Customer&m=detail&maKH=' . $maKH);
                exit;
            }
        } catch (Exception $e) {
            $formError = $e->getMessage();
        }
        
        // Re-display form with error
        $customer = $this->customerModel->getById($maKH);
        
        ob_start();
        require APP_ROOT . '/app/Views/layout/header.php';
        require APP_ROOT . '/app/Views/Customer/edit.php';
        require APP_ROOT . '/app/Views/layout/footer.php';
        echo ob_get_clean();
    }
}
