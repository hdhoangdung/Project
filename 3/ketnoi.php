<?php
// ketnoi.php - Improved version
//khai báo các thông tin cụ thể dành cho việc kết nối
$host="localhost";
$user="root";
$password="";
$database="insurance_db"; // Chỉ sử dụng một database

// Cấu hình báo lỗi
error_reporting(E_ALL);
ini_set('display_errors', 0); // Tắt hiển thị lỗi trên production

// thực hiện kết nối với try-catch để xử lý lỗi tốt hơn
try {
    $conn = new mysqli($host, $user, $password, $database);
    
    // Thiết lập charset UTF-8
    $conn->set_charset("utf8mb4");
    
    // kiểm tra kết nối
    if ($conn->connect_error) {
        // Log lỗi thay vì hiển thị trên màn hình
        error_log("Kết nối thất bại: " . $conn->connect_error);
        throw new Exception("Kết nối cơ sở dữ liệu thất bại");
    }
} catch (Exception $e) {
    // Log lỗi và hiển thị thông báo chung
    error_log($e->getMessage());
    die("Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.");
}

// Thiết lập functions bảo mật hơn
// Hàm kiểm tra người dùng đã đăng nhập hay chưa
function kiemTraDangNhap() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header("Location: formdk.php");
        exit;
    }
    return $_SESSION['user_id'];
}

// Hàm lấy thông tin người dùng
function layThongTinUser($conn, $user_id) {
    // Chuyển đổi kiểu để đảm bảo đúng kiểu dữ liệu
    $user_id = (int)$user_id;
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Hàm kiểm tra quyền admin (nếu cần)
function kiemTraAdmin($conn, $user_id) {
    $user_info = layThongTinUser($conn, $user_id);
    return isset($user_info['is_admin']) && $user_info['is_admin'] == 1;
}

// Hàm mã hóa mật khẩu
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Hàm kiểm tra mật khẩu hợp lệ
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Hàm lọc dữ liệu đầu vào
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>