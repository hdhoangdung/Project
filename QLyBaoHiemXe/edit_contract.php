<?php include 'includes/header.php'; ?>
<?php include 'config/database.php'; ?>
<?php include 'includes/functions.php'; ?>

<?php
if (!isset($_GET['id'])) {
    header('Location: contracts.php');
    exit;
}

$contract_id = $_GET['id'];

// Lấy thông tin hợp đồng
$stmt = $pdo->prepare("SELECT * FROM contracts WHERE id = ?");
$stmt->execute([$contract_id]);
$contract = $stmt->fetch();

if (!$contract) {
    header('Location: contracts.php');
    exit;
}

// Lấy danh sách khách hàng cho dropdown
$stmt = $pdo->query("SELECT id, customer_code, full_name FROM customers ORDER BY full_name");
$customers = $stmt->fetchAll();

if ($_POST) {
    try {
        $sql = "UPDATE contracts SET 
                contract_code = ?, 
                customer_id = ?, 
                vehicle_code = ?, 
                sign_date = ?, 
                expiry_date = ?, 
                insurance_type = ?, 
                insurance_value = ?, 
                premium = ?, 
                status = ? 
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['contract_code'],
            $_POST['customer_id'],
            $_POST['vehicle_code'],
            $_POST['sign_date'],
            $_POST['expiry_date'],
            $_POST['insurance_type'],
            $_POST['insurance_value'],
            $_POST['premium'],
            $_POST['status'],
            $contract_id
        ]);

        header('Location: contracts.php?success=1');
        exit;
    } catch(PDOException $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Tính tỷ lệ phí hiện tại
$current_premium_rate = calculatePremiumRate($contract['insurance_value'], $contract['premium']);
?>

<div class="page-header">
    <h1><i class="fas fa-edit"></i> Sửa Hợp Đồng Bảo Hiểm</h1>
    <a href="contracts.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay Lại
    </a>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<div class="form-section">
    <form method="POST" class="contract-form">
        <div class="form-grid">
            <div class="form-group">
                <label for="contract_code">Mã Hợp Đồng *</label>
                <input type="text" id="contract_code" name="contract_code" 
                       value="<?php echo htmlspecialchars($contract['contract_code']); ?>" required>
            </div>

            <div class="form-group">
                <label for="customer_id">Khách Hàng *</label>
                <select id="customer_id" name="customer_id" required>
                    <option value="">Chọn khách hàng</option>
                    <?php foreach($customers as $customer): ?>
                    <option value="<?php echo $customer['id']; ?>" 
                        <?php echo $contract['customer_id'] == $customer['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($customer['full_name'] . ' - ' . $customer['customer_code']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="vehicle_code">Mã Phương Tiện *</label>
                <input type="text" id="vehicle_code" name="vehicle_code" 
                       value="<?php echo htmlspecialchars($contract['vehicle_code']); ?>" required>
            </div>

            <div class="form-group">
                <label for="sign_date">Ngày Ký *</label>
                <input type="date" id="sign_date" name="sign_date" 
                       value="<?php echo $contract['sign_date']; ?>" required>
            </div>

            <div class="form-group">
                <label for="expiry_date">Ngày Hết Hạn *</label>
                <input type="date" id="expiry_date" name="expiry_date" 
                       value="<?php echo $contract['expiry_date']; ?>" required>
            </div>

            <div class="form-group">
                <label for="insurance_type">Loại Bảo Hiểm *</label>
                <select id="insurance_type" name="insurance_type" required>
                    <option value="">Chọn loại bảo hiểm</option>
                    <option value="Bảo hiểm ô tô" <?php echo $contract['insurance_type'] == 'Bảo hiểm ô tô' ? 'selected' : ''; ?>>Bảo hiểm ô tô</option>
                    <option value="Bảo hiểm xe máy" <?php echo $contract['insurance_type'] == 'Bảo hiểm xe máy' ? 'selected' : ''; ?>>Bảo hiểm xe máy</option>
                    <option value="Bảo hiểm thân vỏ" <?php echo $contract['insurance_type'] == 'Bảo hiểm thân vỏ' ? 'selected' : ''; ?>>Bảo hiểm thân vỏ</option>
                    <option value="Bảo hiểm TNDS" <?php echo $contract['insurance_type'] == 'Bảo hiểm TNDS' ? 'selected' : ''; ?>>Bảo hiểm TNDS</option>
                    <option value="Bảo hiểm tai nạn" <?php echo $contract['insurance_type'] == 'Bảo hiểm tai nạn' ? 'selected' : ''; ?>>Bảo hiểm tai nạn</option>
                    <option value="Bảo hiểm toàn diện" <?php echo $contract['insurance_type'] == 'Bảo hiểm toàn diện' ? 'selected' : ''; ?>>Bảo hiểm toàn diện</option>
                </select>
            </div>

            <div class="form-group">
                <label for="insurance_value">Giá Trị Bảo Hiểm (VNĐ) *</label>
                <input type="number" id="insurance_value" name="insurance_value" step="1000000" 
                       value="<?php echo $contract['insurance_value']; ?>" required
                       onchange="calculatePremium()">
                <small class="form-help">Số tiền tối đa công ty bảo hiểm sẽ bồi thường</small>
            </div>

            <div class="form-group">
                <label for="premium_rate">Tỷ Lệ Phí (%)</label>
                <input type="number" id="premium_rate" step="0.01" 
                       value="<?php echo $current_premium_rate; ?>" 
                       min="0.1" max="10" onchange="calculatePremium()">
                <small class="form-help">Tỷ lệ % để tính phí bảo hiểm (tham khảo)</small>
            </div>

            <div class="form-group">
                <label for="premium">Mức Phí Bảo Hiểm (VNĐ) *</label>
                <input type="number" id="premium" name="premium" step="1000" 
                       value="<?php echo $contract['premium']; ?>" required
                       style="background-color: #f8f9fa; font-weight: bold;">
                <small class="form-help">Số tiền khách hàng phải trả</small>
            </div>

            <div class="form-group">
                <label for="status">Trạng Thái *</label>
                <select id="status" name="status" required>
                    <option value="active" <?php echo $contract['status'] == 'active' ? 'selected' : ''; ?>>Có hiệu lực</option>
                    <option value="suspended" <?php echo $contract['status'] == 'suspended' ? 'selected' : ''; ?>>Tạm ngưng</option>
                    <option value="expired" <?php echo $contract['status'] == 'expired' ? 'selected' : ''; ?>>Hết hạn</option>
                </select>
            </div>
        </div>

        <!-- Hiển thị thông tin tính toán -->
        <div class="calculation-preview">
            <h5><i class="fas fa-info-circle"></i> Thông tin hợp đồng hiện tại:</h5>
            <div class="calculation-item">
                <span class="calculation-label">Giá trị bảo hiểm:</span>
                <span class="calculation-value"><?php echo formatCurrency($contract['insurance_value']); ?></span>
            </div>
            <div class="calculation-item">
                <span class="calculation-label">Mức phí hiện tại:</span>
                <span class="calculation-value"><?php echo formatCurrency($contract['premium']); ?></span>
            </div>
            <div class="calculation-item">
                <span class="calculation-label">Tỷ lệ phí hiện tại:</span>
                <span class="calculation-value"><?php echo $current_premium_rate; ?>%</span>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Cập Nhật Hợp Đồng
            </button>
            <a href="contracts.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Hủy
            </a>
        </div>
    </form>
</div>

<script>
function calculatePremium() {
    const insuranceValue = parseFloat(document.getElementById('insurance_value').value) || 0;
    const premiumRate = parseFloat(document.getElementById('premium_rate').value) || 0;
    
    const premium = insuranceValue * (premiumRate / 100);
    
    document.getElementById('premium').value = Math.round(premium);
}

// Tự động tính ngày hết hạn (1 năm sau ngày ký)
document.getElementById('sign_date').addEventListener('change', function() {
    const signDate = new Date(this.value);
    if (signDate) {
        const expiryDate = new Date(signDate);
        expiryDate.setFullYear(expiryDate.getFullYear() + 1);
        document.getElementById('expiry_date').value = expiryDate.toISOString().split('T')[0];
    }
});
</script>

<?php include 'includes/footer.php'; ?>