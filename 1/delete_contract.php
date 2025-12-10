<?php
include 'config/database.php';

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM contracts WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        
        header('Location: contracts.php?deleted=1');
        exit;
    } catch(PDOException $e) {
        header('Location: contracts.php?error=1');
        exit;
    }
} else {
    header('Location: contracts.php');
    exit;
}
?>