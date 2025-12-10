<?php
// Start session nếu chưa start
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập
$current_page = basename($_SERVER['PHP_SELF']);
$is_login_page = ($current_page == 'login.php');

if (!isset($_SESSION['user_id']) && !$is_login_page) {
    header('Location: login.php');
    exit;
}

if (isset($_SESSION['user_id']) && $is_login_page) {
    // Redirect legacy login page to the central app customer list
    header('Location: /?c=Customer&m=list');
    exit;
}

// Lấy thông tin user từ session
$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Bảo Hiểm Xe</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
    <header class="header">
        <div class="container">
            <div class="logo">
                <i class="fas fa-car-crash"></i>
                <h1>Quản Lý Bảo Hiểm Xe</h1>
            </div>
            
            <nav class="nav">
                <a href="dashboard.php" class="nav-link"><i class="fas fa-home"></i> Trang Chủ</a>
                <a href="customers.php" class="nav-link"><i class="fas fa-users"></i> Khách Hàng</a>
                <a href="contracts.php" class="nav-link"><i class="fas fa-file-contract"></i> Hợp Đồng</a>
                
                <!-- User Menu -->
                <?php if ($user): ?>
                <div class="user-menu">
                    <button class="user-toggle">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="user-dropdown">
                        <div class="user-info">
                            <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                            <span class="user-role"><?php echo $user['role'] === 'admin' ? 'Quản trị viên' : 'Nhân viên'; ?></span>
                        </div>
                        <a href="logout.php" class="logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <?php endif; ?>
    
    <main class="main">
        <div class="container">