<?php
// Start session ở đầu file
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Nếu đã đăng nhập, chuyển hướng đến new customer list (central app)
if (isset($_SESSION['user_id'])) {
    header('Location: /?c=Customer&m=list');
    exit;
}

include 'config/database.php';

$error = '';

if ($_POST) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Include auth.php
    include 'config/auth.php';
    
    if (loginUser($username, $password, $pdo)) {
        header('Location: /?c=Customer&m=list');
        exit;
    } else {
        $error = "Tên đăng nhập hoặc mật khẩu không đúng!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Quản Lý Bảo Hiểm Xe</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary), var(--dark));
            padding: 20px;
        }
        
        .login-card {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-logo i {
            font-size: 3rem;
            color: var(--secondary);
            margin-bottom: 1rem;
        }
        
        .login-logo h1 {
            color: var(--primary);
            margin: 0;
            font-size: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--secondary);
        }
        
        .login-btn {
            width: 100%;
            padding: 12px;
            background: var(--secondary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .login-btn:hover {
            background: #2980b9;
        }
        
        .demo-accounts {
            margin-top: 2rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid var(--info);
        }
        
        .demo-accounts h4 {
            margin-top: 0;
            color: var(--dark);
            font-size: 0.9rem;
        }
        
        .demo-account {
            margin-bottom: 0.5rem;
            font-size: 0.8rem;
        }
        
        .demo-account:last-child {
            margin-bottom: 0;
        }
        
        .demo-account strong {
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <i class="fas fa-car-crash"></i>
                <h1>Quản Lý Bảo Hiểm Xe</h1>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i> Tên đăng nhập
                    </label>
                    <input type="text" id="username" name="username" required 
                           placeholder="Nhập tên đăng nhập">
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Mật khẩu
                    </label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Nhập mật khẩu">
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Đăng Nhập
                </button>
            </form>
            
            <div class="demo-accounts">
                <h4><i class="fas fa-info-circle"></i> Tài khoản demo:</h4>
                <div class="demo-account">
                    <strong>Admin:</strong> admin / password
                </div>
                <div class="demo-account">
                    <strong>Nhân viên:</strong> nhanvien / password
                </div>
            </div>
        </div>
    </div>
</body>
</html>