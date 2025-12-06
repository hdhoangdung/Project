<?php
// Start session và kiểm tra đăng nhập đơn giản
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include 'config/database.php';

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        
        header('Location: customers.php?deleted=1');
        exit;
    } catch(PDOException $e) {
        header('Location: customers.php?error=1');
        exit;
    }
} else {
    header('Location: customers.php');
    exit;
}
?>