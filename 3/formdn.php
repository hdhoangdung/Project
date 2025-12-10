<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="GDdn.css">
</head>
<body>
    <div class="container">
        <h2>Đăng nhập</h2>
        <form method="POST" action="xulydangnhap.php">
            <label for="username_login">Tên đăng nhập:</label>
            <input type="text" name="username_login" id="username_login" required>

            <label for="password_login">Mật khẩu:</label>
            <input type="password" name="password_login" id="password_login" required>

            <input type="submit" value="Đăng nhập">

            <p class="link">Chưa có tài khoản? <a href="formdk.php">Đăng ký ngay</a></p>
        </form>
    </div>
</body>
</html>
