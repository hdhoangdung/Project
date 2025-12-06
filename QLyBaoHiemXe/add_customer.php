<?php include 'includes/header.php'; ?>
<?php include 'config/database.php'; ?>
<?php
if ($_POST) {
    try {
        $sql = "INSERT INTO customers (customer_code, full_name, date_of_birth, id_card, address, phone, email, occupation, insurance_type) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['customer_code'],
            $_POST['full_name'],
            $_POST['date_of_birth'],
            $_POST['id_card'],
            $_POST['address'],
            $_POST['phone'],
            $_POST['email'],
            $_POST['occupation'],
            $_POST['insurance_type']
        ]);

        header('Location: customers.php?success=1');
        exit;
    } catch(PDOException $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}
?>

<div class="page-header">
    <h1><i class="fas fa-user-plus"></i> Thêm Khách Hàng Mới</h1>
    <a href="customers.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay Lại
    </a>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<div class="form-section">
    <form method="POST" class="customer-form">
        <div class="form-grid">
            <div class="form-group">
                <label for="customer_code">Mã Khách Hàng *</label>
                <input type="text" id="customer_code" name="customer_code" required>
            </div>

            <div class="form-group">
                <label for="full_name">Họ và Tên *</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>

            <div class="form-group">
                <label for="date_of_birth">Ngày Sinh *</label>
                <input type="date" id="date_of_birth" name="date_of_birth" required>
            </div>

            <div class="form-group">
                <label for="id_card">CMND/CCCD *</label>
                <input type="text" id="id_card" name="id_card" required>
            </div>

            <div class="form-group">
                <label for="phone">Số Điện Thoại *</label>
                <input type="tel" id="phone" name="phone" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email">
            </div>

            <div class="form-group">
                <label for="occupation">Nghề Nghiệp</label>
                <input type="text" id="occupation" name="occupation">
            </div>

            <div class="form-group">
                <label for="insurance_type">Loại Bảo Hiểm *</label>
                <select id="insurance_type" name="insurance_type" required>
                    <option value="">Chọn loại bảo hiểm</option>
                    <option value="Bảo hiểm ô tô">Bảo hiểm ô tô</option>
                    <option value="Bảo hiểm xe máy">Bảo hiểm xe máy</option>
                    <option value="Bảo hiểm thân vỏ">Bảo hiểm thân vỏ</option>
                    <option value="Bảo hiểm TNDS">Bảo hiểm TNDS</option>
                </select>
            </div>
        </div>

        <div class="form-group full-width">
            <label for="address">Địa Chỉ *</label>
            <textarea id="address" name="address" rows="3" required></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Lưu Thông Tin
            </button>
            <button type="reset" class="btn btn-secondary">
                <i class="fas fa-redo"></i> Nhập Lại
            </button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>