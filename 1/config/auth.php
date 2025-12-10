<?php
// File này CHỈ dùng cho login.php, không dùng cho các file khác

/**
 * Đăng nhập
 */
function loginUser($username, $password, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Start session nếu chưa start
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            // Cập nhật thời gian đăng nhập cuối
            $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            // Lưu thông tin người dùng vào session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
            
            return true;
        }
        return false;
    } catch(PDOException $e) {
        return false;
    }
}

/**
 * Đăng xuất
 */
function logoutUser() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = array();
    session_destroy();
    header('Location: login.php');
    exit;
}
?>