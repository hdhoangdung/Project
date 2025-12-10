<?php
// config.php - cấu hình nâng cao dự án PHAN-HE-2

// ======================
// DEBUG MODE
// ======================
define('DEBUG_MODE', true); // true: hiển thị lỗi, false: tắt lỗi

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// ======================
// CẤU HÌNH DATABASE
// ======================
$db_host = "localhost";
$db_name = "qlbh_xe";
$db_user = "root";
$db_pass = "";

// ======================
// KẾT NỐI DATABASE PDO
// ======================
try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8",
        $db_user,
        $db_pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối database thất bại: " . $e->getMessage());
}

// ======================
// SESSION (chuẩn, tránh lỗi duplicate)
// ======================
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// ======================
// HÀM KIỂM TRA QUYỀN TRUY CẬP
// ======================
function requireRole($roles)
{
    // Nếu chưa login thì dừng ngay
    if (empty($_SESSION['NGUOI_DUNG']) || empty($_SESSION['NGUOI_DUNG']['vai_tro'])) {
        die("Bạn chưa đăng nhập.");
    }

    // Lấy vai trò hiện tại
    $currentRole = $_SESSION['NGUOI_DUNG']['vai_tro'];

    // Kiểm tra dạng mảng
    if (is_array($roles)) {
        if (!in_array($currentRole, $roles, true)) {
            die("Bạn không có quyền truy cập trang này.");
        }
    } else {
        // Kiểm tra dạng 1 role
        if ($currentRole !== $roles) {
            die("Bạn không có quyền truy cập trang này.");
        }
    }
}

// ======================
// CẤU HÌNH CHUNG
// ======================
$base_url = "http://localhost/Projects/phan-he-2";
$site_name = "Dự án PHAN-HE-2";

// ======================
// HÀM HỖ TRỢ TIỆN LỢI
// ======================

// Redirect nhanh
function redirect($url)
{
    header("Location: $url");
    exit;
}

// Truy vấn SELECT tiện lợi
function db_select($sql, $params = [])
{
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Thực hiện INSERT/UPDATE/DELETE
function db_execute($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

// Lấy một giá trị (dùng cho COUNT, SUM, 1 cột)
function dbGetValue($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

// Truy vấn dạng SELECT trả về PDOStatement
function dbQuery($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}



// ======================
// LẤY THÔNG TIN NGƯỜI DÙNG HIỆN TẠI
// ======================
function getCurrentUser() {
    return $_SESSION['NGUOI_DUNG'] ?? null;
}


// Debug nhanh biến
function debug($var)
{
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

?>
