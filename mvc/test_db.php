<?php
require_once 'C:\xampp\htdocs\Projects_\mvc\config\config.php';
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASS
    );
    echo 'Database connected successfully!' . PHP_EOL;
    
    // Test if qlbh_khachhang table exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM qlbh_khachhang");
    $count = $stmt->fetchColumn();
    echo "Customers in database: " . $count . PHP_EOL;
    
    // Check if taikhoan table exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM qlbh_taikhoan");
    $count = $stmt->fetchColumn();
    echo "Users in database: " . $count . PHP_EOL;
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
?>
