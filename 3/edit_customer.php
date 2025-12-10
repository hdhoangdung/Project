<?php include 'db.php'; ?>
<?php
$id = $_GET['id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = $_POST['code'];
    $name = $_POST['name'];
    $dob = $_POST['dob'];
    $id_card = $_POST['id_card'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $occupation = $_POST['occupation'];
    $insurance_type = $_POST['insurance_type'];
    $history = json_encode($_POST['history']);

    $sql = "UPDATE customers SET code='$code', name='$name', dob='$dob', id_card='$id_card', address='$address', phone='$phone', email='$email', occupation='$occupation', insurance_type='$insurance_type', history='$history' WHERE id=$id";
    if ($conn->query($sql)) {
        header("Location: index.php");
    } else {
        echo "Lỗi: " . $conn->error;
    }
}
$result = $conn->query("SELECT * FROM customers WHERE id=$id");
$row = $result->fetch_assoc();

// Decode history if it's JSON
$history_data = $row['history'];
if ($history_data && $history_data[0] == '[') {
    $history_array = json_decode($history_data, true);
    $history_display = is_array($history_array) ? implode("\n", $history_array) : $history_data;
} else {
    $history_display = $history_data;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa Khách Hàng - Bảo Hiểm Phương Tiện</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Additional Styles for Edit Form */
        .form-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 40px;
            background: rgba(10, 10, 20, 0.9);
            backdrop-filter: blur(15px);
            border: 2px solid var(--secondary);
            border-radius: 20px;
            box-shadow: 0 0 40px rgba(255, 106, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 106, 0, 0.05), transparent);
            transition: 0.5s;
        }

        .form-container:hover::before {
            left: 100%;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            background: linear-gradient(45deg, var(--secondary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2.5rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-shadow: 0 0 20px rgba(255, 106, 0, 0.5);
            margin-bottom: 10px;
        }

        .customer-info {
            background: rgba(255, 106, 0, 0.1);
            border: 1px solid var(--secondary);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 25px;
            text-align: center;
        }

        .customer-info p {
            margin: 5px 0;
            color: var(--text-secondary);
        }

        .customer-info strong {
            color: var(--secondary);
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--secondary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid rgba(255, 106, 0, 0.3);
            border-radius: 12px;
            background: rgba(15, 15, 30, 0.8);
            color: var(--text);
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 25px rgba(255, 106, 0, 0.4);
            background: rgba(20, 20, 40, 0.9);
            transform: translateY(-2px);
        }

        textarea.form-input {
            min-height: 100px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
        }

        .btn-update {
            background: linear-gradient(135deg, var(--secondary), var(--accent));
            color: #000;
            padding: 15px 40px;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.1rem;
            box-shadow: 0 0 25px rgba(255, 106, 0, 0.4);
        }

        .btn-update:hover {
            transform: translateY(-3px);
            box-shadow: 0 0 35px rgba(255, 106, 0, 0.6);
        }

        .btn-back {
            background: rgba(0, 234, 255, 0.1);
            color: var(--primary);
            padding: 15px 30px;
            border: 2px solid var(--primary);
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            text-align: center;
        }

        .btn-back:hover {
            background: rgba(0, 234, 255, 0.2);
            box-shadow: 0 0 25px rgba(0, 234, 255, 0.4);
            transform: translateY(-3px);
        }

        .btn-delete {
            background: rgba(255, 68, 68, 0.1);
            color: var(--danger);
            padding: 15px 30px;
            border: 2px solid var(--danger);
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            text-align: center;
        }

        .btn-delete:hover {
            background: rgba(255, 68, 68, 0.2);
            box-shadow: 0 0 25px rgba(255, 68, 68, 0.4);
            transform: translateY(-3px);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-full-width {
            grid-column: 1 / -1;
        }

        /* Animation */
        @keyframes editGlow {
            0%, 100% {
                box-shadow: 0 0 30px rgba(255, 106, 0, 0.3);
            }
            50% {
                box-shadow: 0 0 50px rgba(255, 106, 0, 0.5);
            }
        }

        .form-container {
            animation: editGlow 3s ease-in-out infinite alternate;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-container {
                margin: 20px;
                padding: 25px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-update, .btn-back, .btn-delete {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<!-- Top Bar -->
<div class="top-bar">
    <h1>Sửa Thông Tin Khách Hàng</h1>
    <div class="top-menu">
        <div class="user-menu">
            <div class="user-name">
                Hi, <strong><?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></strong> ⌄
            </div>
        </div>
        <a href="index.php" class="btn-back">← Quay Lại</a>
    </div>
</div>

<!-- Form Container -->
<div class="form-container">
    <div class="page-header">
        <h1>Cập Nhật Thông Tin</h1>
        <p style="color: var(--text-secondary);">Chỉnh sửa thông tin khách hàng <?= htmlspecialchars($row['name']); ?></p>
    </div>

    <div class="customer-info">
        <p><strong>Mã KH:</strong> <?= htmlspecialchars($row['code']); ?> | <strong>ID:</strong> <?= $id; ?></p>
        <p><strong>Lần cuối cập nhật:</strong> <?= date('d/m/Y H:i', strtotime($row['updated_at'] ?? 'now')); ?></p>
    </div>

    <form method="POST">
        <div class="form-grid">
            <div class="form-group">
                <label for="code">MÃ KHÁCH HÀNG *</label>
                <input type="text" id="code" name="code" class="form-input" required 
                       value="<?= htmlspecialchars($row['code']); ?>" placeholder="VD: KH001">
            </div>

            <div class="form-group">
                <label for="name">HỌ TÊN *</label>
                <input type="text" id="name" name="name" class="form-input" required 
                       value="<?= htmlspecialchars($row['name']); ?>" placeholder="Nhập họ tên đầy đủ">
            </div>

            <div class="form-group">
                <label for="dob">NGÀY SINH</label>
                <input type="date" id="dob" name="dob" class="form-input" 
                       value="<?= htmlspecialchars($row['dob']); ?>">
            </div>

            <div class="form-group">
                <label for="id_card">CMND/CCCD</label>
                <input type="text" id="id_card" name="id_card" class="form-input" 
                       value="<?= htmlspecialchars($row['id_card']); ?>" placeholder="Số chứng minh nhân dân">
            </div>

            <div class="form-group form-full-width">
                <label for="address">ĐỊA CHỈ</label>
                <textarea id="address" name="address" class="form-input" 
                          placeholder="Địa chỉ liên hệ"><?= htmlspecialchars($row['address']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="phone">SỐ ĐIỆN THOẠI</label>
                <input type="text" id="phone" name="phone" class="form-input" 
                       value="<?= htmlspecialchars($row['phone']); ?>" placeholder="VD: 0123456789">
            </div>

            <div class="form-group">
                <label for="email">EMAIL</label>
                <input type="email" id="email" name="email" class="form-input" 
                       value="<?= htmlspecialchars($row['email']); ?>" placeholder="email@example.com">
            </div>

            <div class="form-group">
                <label for="occupation">NGHỀ NGHIỆP</label>
                <input type="text" id="occupation" name="occupation" class="form-input" 
                       value="<?= htmlspecialchars($row['occupation']); ?>" placeholder="Công việc hiện tại">
            </div>

            <div class="form-group">
                <label for="insurance_type">LOẠI BẢO HIỂM</label>
                <input type="text" id="insurance_type" name="insurance_type" class="form-input" 
                       value="<?= htmlspecialchars($row['insurance_type']); ?>" placeholder="VD: Xe máy, Ô tô...">
            </div>

            <div class="form-group form-full-width">
                <label for="history">LỊCH SỬ GIAO DỊCH</label>
                <textarea id="history" name="history" class="form-input" 
                          placeholder="Ghi chú về lịch sử giao dịch..."><?= htmlspecialchars($history_display); ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-update">
                ⚡ CẬP NHẬT
            </button>
            <a href="index.php" class="btn-back">
                ↩ HUỶ
            </a>
            <a href="delete_customer.php?id=<?= $id; ?>" class="btn-delete" 
               onclick="return confirm('Bạn có chắc muốn xóa khách hàng này?')">
                🗑️ XÓA
            </a>
        </div>
    </form>
</div>

<!-- Footer -->
<footer class="site-footer">
    <div class="container">
        <p>&copy; <?= date("Y"); ?> Bảo Hiểm Phương Tiện. All Rights Reserved.</p>
        <p>Địa chỉ: 123 Đường ABC, Quận 1, TP.HCM | Email: info@baohiempt.com | Hotline: 1900 1234</p>
    </div>
</footer>

</body>
</html>