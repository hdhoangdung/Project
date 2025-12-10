<?php include 'includes/header.php'; ?>
<?php include 'config/database.php'; ?>
<?php
// Lấy danh sách khách hàng cho dropdown
$stmt = $pdo->query("SELECT id, customer_code, full_name FROM customers ORDER BY full_name");
$customers = $stmt->fetchAll();

if ($_POST) {
    try {
        // Xóa dấu phân cách nếu có trong giá trị nhập
        $insurance_value = str_replace(['.', ','], '', $_POST['insurance_value']);
        $premium = str_replace(['.', ','], '', $_POST['premium']);
        
        $sql = "INSERT INTO contracts (contract_code, customer_id, vehicle_code, sign_date, expiry_date, insurance_type, insurance_value, premium, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['contract_code'],
            $_POST['customer_id'],
            $_POST['vehicle_code'],
            $_POST['sign_date'],
            $_POST['expiry_date'],
            $_POST['insurance_type'],
            $insurance_value,
            $premium,
            $_POST['status']
        ]);

        header('Location: contracts.php?success=1');
        exit;
    } catch(PDOException $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}
?>

<div class="page-header">
    <h1><i class="fas fa-file-signature"></i> Tạo Hợp Đồng Mới</h1>
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
                <input type="text" id="contract_code" name="contract_code" required>
            </div>

            <div class="form-group">
                <label for="customer_id">Khách Hàng *</label>
                <select id="customer_id" name="customer_id" required>
                    <option value="">Chọn khách hàng</option>
                    <?php foreach($customers as $customer): ?>
                    <option value="<?php echo $customer['id']; ?>" <?php echo isset($_GET['customer_id']) && $_GET['customer_id'] == $customer['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($customer['full_name'] . ' - ' . $customer['customer_code']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="vehicle_code">Mã Phương Tiện *</label>
                <input type="text" id="vehicle_code" name="vehicle_code" required>
            </div>

            <div class="form-group">
                <label for="sign_date">Ngày Ký *</label>
                <input type="date" id="sign_date" name="sign_date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label for="expiry_date">Ngày Hết Hạn *</label>
                <input type="date" id="expiry_date" name="expiry_date" required>
            </div>

            <div class="form-group">
                <label for="insurance_type">Loại Bảo Hiểm *</label>
                <select id="insurance_type" name="insurance_type" required>
                    <option value="">Chọn loại bảo hiểm</option>
                    <option value="Bảo hiểm ô tô">Bảo hiểm ô tô</option>
                    <option value="Bảo hiểm xe máy">Bảo hiểm xe máy</option>
                    <option value="Bảo hiểm thân vỏ">Bảo hiểm thân vỏ</option>
                    <option value="Bảo hiểm TNDS">Bảo hiểm TNDS</option>
                    <option value="Bảo hiểm tai nạn">Bảo hiểm tai nạn</option>
                    <option value="Bảo hiểm toàn diện">Bảo hiểm toàn diện</option>
                </select>
            </div>

            <div class="form-group">
                <label for="insurance_value">Giá Trị Bảo Hiểm (VNĐ) *</label>
                <input type="text" id="insurance_value" name="insurance_value" 
                       placeholder="50000000" required
                       oninput="formatCurrencyInput(this)"
                       onchange="calculatePremium()">
                <small class="form-help">
                    <strong>Nhập số tiền bằng số, không dấu chấm/phẩy:</strong><br>
                    • 40,000,000 đ → Nhập: <code>40000000</code><br>
                    • 500,000,000 đ → Nhập: <code>500000000</code>
                </small>
            </div>

            <div class="form-group">
                <label for="premium_rate">Tỷ Lệ Phí (%) *</label>
                <input type="number" id="premium_rate" name="premium_rate" step="0.01" 
                       value="1.5" min="0.1" max="10" required
                       onchange="calculatePremium()">
                <small class="form-help">
                    Tỷ lệ % để tính phí bảo hiểm (thường từ 1% đến 3%)
                </small>
            </div>

            <div class="form-group">
                <label for="premium">Mức Phí Bảo Hiểm (VNĐ) *</label>
                <input type="text" id="premium" name="premium" readonly
                       style="background-color: #e8f5e8; font-weight: bold; color: #2e7d32;"
                       placeholder="Sẽ tự động tính..." required>
                <small class="form-help">
                    <strong>Tự động tính:</strong> Giá trị BH × Tỷ lệ phí
                </small>
            </div>

            <div class="form-group">
                <label for="status">Trạng Thái *</label>
                <select id="status" name="status" required>
                    <option value="active">Có hiệu lực</option>
                    <option value="suspended">Tạm ngưng</option>
                    <option value="expired">Hết hạn</option>
                </select>
            </div>
        </div>

        <!-- Hướng dẫn nhập liệu -->
        <div class="input-guide">
            <h4><i class="fas fa-lightbulb"></i> Hướng Dẫn Nhập Liệu</h4>
            <div class="guide-examples">
                <div class="guide-item">
                    <span class="guide-label">Xe máy:</span>
                    <span class="guide-value">Giá trị: <strong>40,000,000 đ</strong> → Nhập: <code>40000000</code></span>
                </div>
                <div class="guide-item">
                    <span class="guide-label">Ô tô con:</span>
                    <span class="guide-value">Giá trị: <strong>500,000,000 đ</strong> → Nhập: <code>500000000</code></span>
                </div>
                <div class="guide-item">
                    <span class="guide-label">Xe tải:</span>
                    <span class="guide-value">Giá trị: <strong>800,000,000 đ</strong> → Nhập: <code>800000000</code></span>
                </div>
            </div>
        </div>

        <!-- Phần ví dụ tính phí -->
        <div class="insurance-examples">
            <h5><i class="fas fa-calculator"></i> Ví dụ về tính phí bảo hiểm:</h5>
            <div class="example-item">
                <span>Xe máy 40 triệu, tỷ lệ 2%:</span>
                <strong>800,000 đ</strong>
            </div>
            <div class="example-item">
                <span>Ô tô 500 triệu, tỷ lệ 1.5%:</span>
                <strong>7,500,000 đ</strong>
            </div>
            <div class="example-item">
                <span>Xe tải 800 triệu, tỷ lệ 2%:</span>
                <strong>16,000,000 đ</strong>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Tạo Hợp Đồng
            </button>
            <button type="reset" class="btn btn-secondary" onclick="resetForm()">
                <i class="fas fa-redo"></i> Nhập Lại
            </button>
        </div>
    </form>
</div>

<script>
function calculatePremium() {
    // Lấy giá trị và xóa dấu phân cách
    let insuranceValue = document.getElementById('insurance_value').value;
    insuranceValue = insuranceValue.replace(/\./g, '');
    insuranceValue = parseFloat(insuranceValue) || 0;
    
    const premiumRate = parseFloat(document.getElementById('premium_rate').value) || 0;
    
    // Tính phí bảo hiểm
    const premium = insuranceValue * (premiumRate / 100);
    
    // Làm tròn đến nghìn đồng
    const roundedPremium = Math.round(premium / 1000) * 1000;
    
    // Định dạng và hiển thị
    document.getElementById('premium').value = formatCurrency(roundedPremium);
}

function formatCurrencyInput(input) {
    // Lấy giá trị và xóa tất cả ký tự không phải số
    let value = input.value.replace(/[^\d]/g, '');
    
    // Định dạng thành số có dấu chấm phân cách
    if (value) {
        value = parseInt(value).toLocaleString('vi-VN');
    }
    
    input.value = value;
    
    // Tính lại phí nếu có giá trị
    if (value.replace(/\./g, '')) {
        calculatePremium();
    }
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN').format(amount);
}

function resetForm() {
    // Reset tỷ lệ phí về mặc định
    document.getElementById('premium_rate').value = 1.5;
    
    // Tính lại phí sau khi reset
    setTimeout(calculatePremium, 100);
}

// Tự động tính phí khi trang load
document.addEventListener('DOMContentLoaded', function() {
    calculatePremium();
    
    // Tự động tính ngày hết hạn (1 năm sau ngày ký)
    document.getElementById('sign_date').addEventListener('change', function() {
        const signDate = new Date(this.value);
        if (signDate) {
            const expiryDate = new Date(signDate);
            expiryDate.setFullYear(expiryDate.getFullYear() + 1);
            document.getElementById('expiry_date').value = expiryDate.toISOString().split('T')[0];
        }
    });
    
    // Tự động tính khi thay đổi tỷ lệ phí
    document.getElementById('premium_rate').addEventListener('input', calculatePremium);
});
</script>

<style>
.input-guide {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1.5rem;
    margin: 1rem 0;
}

.input-guide h4 {
    color: var(--primary);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.guide-examples {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.guide-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem;
    background: white;
    border-radius: 5px;
    border-left: 4px solid var(--secondary);
}

.guide-label {
    font-weight: 600;
    color: var(--dark);
    min-width: 100px;
}

.guide-value {
    color: #555;
}

.guide-value code {
    background: #e9ecef;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: monospace;
    color: #d63384;
}

.form-help {
    display: block;
    margin-top: 5px;
    font-size: 0.8rem;
    color: #6c757d;
    line-height: 1.4;
}

.form-help strong {
    color: var(--dark);
}

.form-help code {
    background: #f8f9fa;
    padding: 1px 4px;
    border-radius: 3px;
    border: 1px solid #dee2e6;
    font-family: 'Courier New', monospace;
    color: #e83e8c;
}

.insurance-examples {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 8px;
    padding: 1.5rem;
    margin: 1rem 0;
}

.insurance-examples h5 {
    color: #856404;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.example-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem;
    background: white;
    border-radius: 5px;
    margin-bottom: 0.5rem;
}

.example-item:last-child {
    margin-bottom: 0;
}
</style>

<?php include 'includes/footer.php'; ?>