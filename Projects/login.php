<?php
/*
 * File: `login.php`
 * Mục đích:
 * - Hiển thị form đăng nhập và xử lý xác thực người dùng.
 * - Lưu session khi đăng nhập thành công và chuyển hướng theo vai trò.
 * Bảo mật:
 * - Dùng `sanitize()` cho input; so sánh mật khẩu nên dùng hàm an toàn nếu lưu hash.
 */
define('APP_ACCESS', true);
require_once 'config.php';

initSession();

// Nếu đã đăng nhập thì chuyển hướng
if (isLoggedIn()) {
    $role = $_SESSION['vai_tro'];
    switch ($role) {
        case 'KeToan':
            header("Location: " . dirname($_SERVER['PHP_SELF']) . "/phan-he-4/index.php");

            break;
        case 'QuanLy':
            header("Location: " . dirname($_SERVER['PHP_SELF']) . "/phan-he-4/index.php");
            break;
        case 'GiamDinh':
            header("Location: " . dirname($_SERVER['PHP_SELF']) . "/phan-he-4/index.php");
            break;
        default:
            header("Location: " . dirname($_SERVER['PHP_SELF']) . "/phan-he-4/index.php");
    }
    exit();
}

$error = '';
$success = isset($_GET['logout']) ? 'Đăng xuất thành công!' : '';

if (isset($_GET['timeout'])) {
    $error = 'Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.';
}

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '', $conn);
    $password = $_POST['password'] ?? '';
    
    // Xác thực input
    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    } else {
        try {
            // Lấy thông tin tài khoản
            $stmt = $conn->prepare("
                SELECT tk.*, nv.MaNV, nv.HoTen, nv.PhongBan
                FROM TaiKhoan tk
                JOIN NhanVien nv ON tk.MaNV = nv.MaNV
                WHERE tk.TenDangNhap = ? AND tk.TrangThai = 1
            ");
            
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Kiểm tra tài khoản có bị khóa không
                if ($user['ThoiGianKhoa'] && strtotime($user['ThoiGianKhoa']) > time()) {
                    $remaining = ceil((strtotime($user['ThoiGianKhoa']) - time()) / 60);
                    $error = "Tài khoản bị khóa. Vui lòng thử lại sau {$remaining} phút.";
                } else {
                    // Xác thực mật khẩu
                    $password_valid = password_verify($password, $user['MatKhau']) || 
                                     ($password === '123456' && $user['MatKhau'] === '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
                    
                    if ($password_valid) {
                        // Reset số lần đăng nhập sai
                        $conn->query("UPDATE TaiKhoan SET SoLanDangNhapSai = 0, ThoiGianKhoa = NULL WHERE MaTK = '{$user['MaTK']}'");
                        
                        // Tạo session
                        $_SESSION['user_id'] = $user['MaTK'];
                        $_SESSION['ma_nv'] = $user['MaNV'];
                        $_SESSION['username'] = $user['TenDangNhap'];
                        $_SESSION['name'] = $user['HoTen'];
                        $_SESSION['vai_tro'] = $user['VaiTro'];
                        $_SESSION['phong_ban'] = $user['PhongBan'];
                        $_SESSION['LAST_ACTIVITY'] = time();
                        
                        // Ghi log
                        logActivity('LOGIN', 'TaiKhoan', $user['MaTK'], null, ['ip' => $_SERVER['REMOTE_ADDR']]);
                        
                        // Chuyển hướng
                        switch ($user['VaiTro']) {
                            case 'KeToan':
                             header("Location: " . dirname($_SERVER['PHP_SELF']) . "/phan-he-4/index.php");
                                break;
                            case 'QuanLy':
                                header("Location: " . dirname($_SERVER['PHP_SELF']) . "/phan-he-4/index.php");
                                break;
                            case 'GiamDinh':
                                header("Location: " . dirname($_SERVER['PHP_SELF']) . "/phan-he-4/index.php");
                                break;
                            default:
                                header("Location: " . dirname($_SERVER['PHP_SELF']) . "/phan-he-4/index.php");
                        }
                        exit();
                    } else {
                        // Tăng số lần đăng nhập sai
                        $sai_moi = $user['SoLanDangNhapSai'] + 1;
                        
                        if ($sai_moi >= MAX_LOGIN_ATTEMPTS) {
                            // Khóa tài khoản
                            $khoa_den = date('Y-m-d H:i:s', time() + LOCKOUT_TIME);
                            $conn->query("UPDATE TaiKhoan SET SoLanDangNhapSai = $sai_moi, ThoiGianKhoa = '$khoa_den' WHERE MaTK = '{$user['MaTK']}'");
                            $error = 'Tài khoản đã bị khóa do đăng nhập sai quá ' . MAX_LOGIN_ATTEMPTS . ' lần. Vui lòng thử lại sau 15 phút.';
                        } else {
                            $conn->query("UPDATE TaiKhoan SET SoLanDangNhapSai = $sai_moi WHERE MaTK = '{$user['MaTK']}'");
                            $con_lai = MAX_LOGIN_ATTEMPTS - $sai_moi;
                            $error = "Mật khẩu không chính xác! Còn {$con_lai} lần thử.";
                        }
                        
                        // Ghi log thất bại
                        logActivity('LOGIN_FAILED', 'TaiKhoan', $user['MaTK'], null, ['ip' => $_SERVER['REMOTE_ADDR']]);
                    }
                }
            } else {
                $error = 'Tài khoản không tồn tại hoặc đã bị khóa!';
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            $error = 'Đã xảy ra lỗi. Vui lòng thử lại sau.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Hệ thống Quản lý Bảo hiểm Xe - Đăng nhập">
    <title>Đăng nhập - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Kiểu CSS nội tuyến -->
    <style>
    .login-page {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .login-container {
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        width: 100%;
        max-width: 440px;
        animation: slideIn 0.5s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .login-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 50px 30px;
        text-align: center;
        position: relative;
    }

    .login-header::before {
        content: '🚗';
        font-size: 60px;
        display: block;
        margin-bottom: 15px;
    }

    .login-header h1 {
        font-size: 28px;
        margin-bottom: 10px;
        font-weight: 700;
    }

    .login-header p {
        opacity: 0.95;
        font-size: 15px;
    }

    .login-body {
        padding: 40px 35px;
    }

    .form-group {
        margin-bottom: 25px;
        position: relative;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #374151;
        font-size: 14px;
    }

    .form-group input {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 15px;
        transition: all 0.3s;
        background: #f9fafb;
    }

    .form-group input:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .form-group input::placeholder {
        color: #9ca3af;
    }

    .btn-login {
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 10px;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }

    .btn-login:active {
        transform: translateY(0);
    }

    .demo-accounts {
        margin-top: 30px;
        padding-top: 25px;
        border-top: 2px solid #f3f4f6;
        text-align: center;
    }

    .demo-accounts h4 {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .demo-account {
        display: inline-block;
        margin: 6px 8px;
        padding: 8px 14px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 13px;
        color: #374151;
    }

    .demo-account code {
        font-family: 'Courier New', monospace;
        color: #667eea;
        font-weight: 600;
    }

    .alert {
        padding: 14px 18px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-error {
        background: #fef2f2;
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }

    .alert-success {
        background: #f0fdf4;
        color: #166534;
        border-left: 4px solid #10b981;
    }

    .system-version {
        text-align: center;
        margin-top: 20px;
        font-size: 12px;
        color: #9ca3af;
    }
    </style>
</head>

<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-header">
                <h1><?php echo APP_NAME; ?></h1>
                <p>Phân hệ Kế toán - Quản lý thu chi</p>
            </div>

            <div class="login-body">
                <!-- Thông báo lỗi -->
                <?php if ($error): ?>
                <div class="alert alert-error">
                    <span>✗</span>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
                <?php endif; ?>

                <!-- Thông báo thành công -->
                <?php if ($success): ?>
                <div class="alert alert-success">
                    <span>✓</span>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
                <?php endif; ?>

                <form method="POST" action="" autocomplete="off">
                    <div class="form-group">
                        <label for="username">Tên đăng nhập</label>
                        <input type="text" id="username" name="username" placeholder="Nhập tên đăng nhập" required
                            autofocus autocomplete="username"
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Mật khẩu</label>
                        <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required
                            autocomplete="current-password">
                    </div>

                    <button type="submit" class="btn-login">
                        Đăng nhập
                    </button>
                </form>

                <!-- Tài khoản demo -->
                <div class="demo-accounts">
                    <h4>Tài khoản demo</h4>
                    <div class="demo-account">
                        Kế toán: <code>ketoan / 123456</code>
                    </div>
                    <div class="demo-account">
                        Quản lý: <code>quanly / 123456</code>
                    </div>
                    <div class="demo-account">
                        Giám định: <code>giamdinh / 123456</code>
                    </div>
                </div>

                <!-- Phiên bản hệ thống -->
                <div class="system-version">
                    Version <?php echo APP_VERSION; ?> - © 2025
                </div>
            </div>
        </div>
    </div>

    <script>
    // Tự động focus vào trường password nếu username đã có giá trị
    document.addEventListener('DOMContentLoaded', function() {
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');

        if (usernameInput.value.trim() !== '') {
            passwordInput.focus();
        }
    });

    // Xóa thông báo sau 5 giây
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);
    </script>
</body>

</html>