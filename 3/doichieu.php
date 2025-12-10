<?php
require 'ketnoi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: formdn.php");
    exit;
}

$username = sanitizeInput($_POST['username']);
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        echo "<h3>Thông tin đăng nhập chính xác.</h3><a href='formdn.php'>Quay lại đăng nhập</a>";
    } else {
        echo "<h3>Mật khẩu không đúng.</h3>";
    }
} else {
    echo "<h3>Không tìm thấy tài khoản.</h3>";
}
?>
