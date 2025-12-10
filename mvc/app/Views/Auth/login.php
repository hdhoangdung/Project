<?php
/**
 * Login View (Module 0C)
 * Authentic login form with Bootstrap styling
 */

// If already logged in, redirect to customer list
if (isset($auth) && $auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . '?c=Customer&m=list');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Hệ Thống Quản Lý Bảo Hiểm Xe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: #667eea;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }
        .form-control {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px 15px;
            font-size: 14px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .alert {
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .account-info {
            background: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-top: 25px;
            font-size: 13px;
        }
        .account-info h6 {
            color: #667eea;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .account-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .account-row:last-child {
            border-bottom: none;
        }
        .account-username {
            color: #333;
            font-weight: 500;
        }
        .account-role {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Header -->
        <div class="login-header">
            <h1>🔐 Đăng Nhập</h1>
            <p>Hệ Thống Quản Lý Bảo Hiểm Xe</p>
        </div>

        <!-- Error Messages -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Lỗi:</strong> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="<?php echo BASE_URL; ?>?c=Auth&m=authenticate">
            <div class="form-group">
                <label for="username" class="form-label">Tên Đăng Nhập</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="username" 
                    name="username" 
                    placeholder="Nhập tên đăng nhập"
                    required
                    autofocus>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Mật Khẩu</label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="password" 
                    name="password" 
                    placeholder="Nhập mật khẩu"
                    required>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Đăng Nhập
            </button>
        </form>

        <!-- Account Info -->
        <div class="account-info">
            <h6>📋 Tài Khoản Test</h6>
            <div class="account-row">
                <span class="account-username">khach_hang</span>
                <span class="account-role">Quản Lý KH</span>
            </div>
            <div class="account-row">
                <span class="account-username">boi_thuong</span>
                <span class="account-role">Bồi Thường</span>
            </div>
            <div class="account-row">
                <span class="account-username">phuong_tien</span>
                <span class="account-role">Phương Tiện</span>
            </div>
            <div class="account-row">
                <span class="account-username">ke_toan</span>
                <span class="account-role">Kế Toán</span>
            </div>
            <p style="margin-top: 10px; color: #666; font-size: 12px;">
                <strong>Mật khẩu:</strong> 123456 (cho tất cả)
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
