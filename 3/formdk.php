<?php
require 'ketnoi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hoten = trim($_POST['hoten'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password_raw = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');

    if (!$hoten || !$username || !$password_raw || !$email) {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    } else {
        $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (hoten, username, password, email) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssss", $hoten, $username, $password_hash, $email);
            if ($stmt->execute()) {
                header("Location: formdn.php");
                exit;
            } else {
                $error = "Lỗi khi đăng ký: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Không chuẩn bị được câu lệnh SQL.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Đăng ký</title>
    <link rel="stylesheet" href="GDdn.css">
</head>
<body>
     <div class="container">
        <h2>Đăng ký</h2>

        <?php if (!empty($error)) echo '<p style="color:red;">' . htmlspecialchars($error) . '</p>'; ?>

        <form method="POST" action="">
            <label for="hoten">Họ tên:</label>
            <input type="text" id="hoten" name="hoten" required />

            <label for="username">Tên đăng nhập:</label>
            <input type="text" id="username" name="username" required />

            <label for="password">Mật khẩu:</label>
            <input type="password" id="password" name="password" required />

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required />

            <input type="submit" value="Đăng ký" />
        </form>

        <p class="link">
            Đã có tài khoản? <a href="formdn.php">Đăng nhập</a>
        </p>
    </div>
</html>
