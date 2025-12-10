<?php
session_start();
require_once 'ketnoi.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: formdn.php");
    exit;
}

$username = sanitizeInput($_POST['username_login']);
$password = $_POST['password_login'];

$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['hoten'] = $user['hoten'];

        if ((int)$user['sodu'] === 0) {
            $bonus = 1000000;
            $update = $conn->prepare("UPDATE users SET sodu = sodu + ? WHERE id = ?");
            $update->bind_param("ii", $bonus, $user['id']);
            $update->execute();
            $_SESSION['new_user'] = true;
        }

        header("Location: index.php");
        exit;
    } else {
        echo "Sai mật khẩu. <a href='formdn.php'>Thử lại</a>";
    }
} else {
    echo "Tên đăng nhập không tồn tại. <a href='formdn.php'>Thử lại</a>";
}
?>
