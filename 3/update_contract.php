<?php include 'db.php'; ?>
<?php
$customer_id = $_GET['customer_id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $contract_id = $_POST['contract_id'] ?? null;
    if ($action == 'create') {
        $code = $_POST['code'];
        $sign_date = $_POST['sign_date'];
        $expiry_date = $_POST['expiry_date'];
        $type = $_POST['type'];
        $value = $_POST['value'];
        $fee = $_POST['fee'];
        $vehicle_id = $_POST['vehicle_id'];
        $sql = "INSERT INTO contracts (code, sign_date, expiry_date, type, value, fee, customer_id, vehicle_id) VALUES ('$code', '$sign_date', '$expiry_date', '$type', '$value', '$fee', '$customer_id', '$vehicle_id')";
    } elseif ($action == 'extend') {
        $new_expiry = $_POST['new_expiry'];
        $sql = "UPDATE contracts SET expiry_date='$new_expiry', status='active' WHERE id=$contract_id";
    } elseif ($action == 'terminate') {
        $sql = "UPDATE contracts SET status='expired' WHERE id=$contract_id";
    }
    $conn->query($sql);
    header("Location: view_customer.php?id=$customer_id");
}
$contracts = $conn->query("SELECT * FROM contracts WHERE customer_id=$customer_id");
$vehicles = $conn->query("SELECT * FROM vehicles");

// Lấy thông tin khách hàng để hiển thị
$customer_result = $conn->query("SELECT * FROM customers WHERE id=$customer_id");
$customer = $customer_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Hợp Đồng - Bảo Hiểm Phương Tiện</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Additional Styles for Contract Page */
        .page-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .contract-section {
            background: rgba(10, 10, 20, 0.9);
            backdrop-filter: blur(15px);
            border: 2px solid var(--primary);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 0 40px rgba(0, 234, 255, 0.3);
            position: relative;
            overflow: hidden;
        }

        .contract-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 234, 255, 0.05), transparent);
            transition: 0.5s;
        }

        .contract-section:hover::before {
            left: 100%;
        }

        .section-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-header h1 {
            background: linear-gradient(45deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2.5rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-shadow: 0 0 20px rgba(0, 234, 255, 0.5);
            margin-bottom: 10px;
        }

        .section-header h2 {
            color: var(--secondary);
            font-size: 1.8rem;
            margin: 30px 0 20px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .customer-info {
            background: rgba(0, 234, 255, 0.1);
            border: 1px solid var(--primary);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: center;
        }

        .customer-info h3 {
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 1.3rem;
        }

        .customer-info p {
            margin: 5px 0;
            color: var(--text-secondary);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-full-width {
            grid-column: 1 / -1;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--primary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid rgba(0, 234, 255, 0.3);
            border-radius: 12px;
            background: rgba(15, 15, 30, 0.8);
            color: var(--text);
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 25px rgba(0, 234, 255, 0.4);
            background: rgba(20, 20, 40, 0.9);
            transform: translateY(-2px);
        }

        select.form-input {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%2300eaff' viewBox='0 0 24 24'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 20px;
        }

        .radio-group {
            display: flex;
            gap: 30px;
            margin: 20px 0;
        }

        .radio-label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 12px 20px;
            border: 2px solid rgba(255, 106, 0, 0.3);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .radio-label:hover {
            border-color: var(--secondary);
            background: rgba(255, 106, 0, 0.1);
        }

        .radio-label input[type="radio"] {
            accent-color: var(--secondary);
            transform: scale(1.2);
        }

        .form-actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
        }

        .btn-create {
            background: linear-gradient(135deg, var(--primary), var(--accent));
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
            box-shadow: 0 0 25px rgba(0, 234, 255, 0.4);
        }

        .btn-extend {
            background: linear-gradient(135deg, var(--secondary), #ffaa00);
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

        .btn-terminate {
            background: rgba(255, 68, 68, 0.1);
            color: var(--danger);
            padding: 15px 30px;
            border: 2px solid var(--danger);
            border-radius: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-back {
            background: rgba(255, 106, 0, 0.1);
            color: var(--secondary);
            padding: 15px 30px;
            border: 2px solid var(--secondary);
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            text-align: center;
        }

        .btn-create:hover, .btn-extend:hover {
            transform: translateY(-3px);
            box-shadow: 0 0 35px rgba(0, 234, 255, 0.6);
        }

        .btn-terminate:hover {
            background: rgba(255, 68, 68, 0.2);
            box-shadow: 0 0 25px rgba(255, 68, 68, 0.4);
            transform: translateY(-3px);
        }

        .btn-back:hover {
            background: rgba(255, 106, 0, 0.2);
            box-shadow: 0 0 25px rgba(255, 106, 0, 0.4);
            transform: translateY(-3px);
        }

        .contract-list {
            margin-top: 30px;
        }

        .contract-item {
            background: rgba(255, 106, 0, 0.05);
            border: 1px solid rgba(255, 106, 0, 0.3);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
        }

        .contract-item h4 {
            color: var(--secondary);
            margin-bottom: 10px;
        }

        .contract-item p {
            margin: 5px 0;
            color: var(--text-secondary);
        }

        /* Animations */
        @keyframes contractGlow {
            0%, 100% {
                box-shadow: 0 0 30px rgba(0, 234, 255, 0.3);
            }
            50% {
                box-shadow: 0 0 50px rgba(0, 234, 255, 0.5);
            }
        }

        .contract-section {
            animation: contractGlow 3s ease-in-out infinite alternate;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-container {
                margin: 20px auto;
                padding: 0 15px;
            }

            .contract-section {
                padding: 25px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .radio-group {
                flex-direction: column;
                gap: 15px;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-create, .btn-extend, .btn-terminate, .btn-back {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<!-- Top Bar -->
<div class="top-bar">
    <h1>Quản Lý Hợp Đồng Bảo Hiểm</h1>
    <div class="top-menu">
        <div class="user-menu">
            <div class="user-name">
                Hi, <strong><?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></strong> ⌄
            </div>
        </div>
        <a href="view_customer.php?id=<?= $customer_id; ?>" class="btn-back">← Quay Lại</a>
    </div>
</div>

<div class="page-container">
    <!-- Customer Information -->
    <div class="contract-section">
        <div class="section-header">
            <h1>Quản Lý Hợp Đồng</h1>
        </div>

        <div class="customer-info">
            <h3>Thông Tin Khách Hàng</h3>
            <p><strong>Mã KH:</strong> <?= htmlspecialchars($customer['code']); ?> | <strong>Họ Tên:</strong> <?= htmlspecialchars($customer['name']); ?></p>
            <p><strong>SĐT:</strong> <?= htmlspecialchars($customer['phone']); ?> | <strong>Loại BH:</strong> <?= htmlspecialchars($customer['insurance_type']); ?></p>
        </div>

        <!-- Create New Contract Form -->
        <h2 style="color: var(--primary); text-align: center; margin-bottom: 30px;">📝 TẠO HỢP ĐỒNG MỚI</h2>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="form-grid">
                <div class="form-group">
                    <label for="code">MÃ HỢP ĐỒNG *</label>
                    <input type="text" id="code" name="code" class="form-input" required placeholder="HD2024XXXX">
                </div>

                <div class="form-group">
                    <label for="sign_date">NGÀY KÝ *</label>
                    <input type="date" id="sign_date" name="sign_date" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="expiry_date">NGÀY HẾT HẠN *</label>
                    <input type="date" id="expiry_date" name="expiry_date" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="type">LOẠI HỢP ĐỒNG</label>
                    <input type="text" id="type" name="type" class="form-input" placeholder="Bảo hiểm vật chất...">
                </div>

                <div class="form-group">
                    <label for="value">GIÁ TRỊ HỢP ĐỒNG (VND)</label>
                    <input type="number" id="value" name="value" class="form-input" placeholder="10000000">
                </div>

                <div class="form-group">
                    <label for="fee">PHÍ BẢO HIỂM (VND)</label>
                    <input type="number" id="fee" name="fee" class="form-input" placeholder="500000">
                </div>

                <div class="form-group">
                    <label for="vehicle_id">PHƯƠNG TIỆN</label>
                    <select id="vehicle_id" name="vehicle_id" class="form-input">
                        <?php while ($v = $vehicles->fetch_assoc()) { 
                            echo "<option value='{$v['id']}'>{$v['code']} - {$v['type']}</option>"; 
                        } ?>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-create">
                    🚀 TẠO HỢP ĐỒNG
                </button>
            </div>
        </form>
    </div>

    <!-- Extend/Terminate Contract Section -->
    <div class="contract-section">
        <h2 style="color: var(--secondary); text-align: center; margin-bottom: 30px;">⚡ GIA HẠN HOẶC CHẤM DỨT HỢP ĐỒNG</h2>
        
        <form method="POST">
            <div class="form-group">
                <label for="contract_id">CHỌN HỢP ĐỒNG</label>
                <select id="contract_id" name="contract_id" class="form-input" required>
                    <?php 
                    $contracts->data_seek(0); // Reset pointer
                    while ($c = $contracts->fetch_assoc()) { 
                        $status_color = $c['status'] == 'active' ? 'var(--success)' : 'var(--danger)';
                        echo "<option value='{$c['id']}' style='color: {$status_color}'>
                                {$c['code']} - {$c['type']} ({$c['status']})
                              </option>"; 
                    } 
                    ?>
                </select>
            </div>

            <div class="radio-group">
                <label class="radio-label">
                    <input type="radio" name="action" value="extend" required>
                    <span style="color: var(--secondary); font-weight: 600;">🔄 GIA HẠN HỢP ĐỒNG</span>
                </label>
                <label class="radio-label">
                    <input type="radio" name="action" value="terminate">
                    <span style="color: var(--danger); font-weight: 600;">⏹️ CHẤM DỨT HỢP ĐỒNG</span>
                </label>
            </div>

            <div class="form-group" id="extend-date-group">
                <label for="new_expiry">NGÀY HẾT HẠN MỚI</label>
                <input type="date" id="new_expiry" name="new_expiry" class="form-input">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-extend">
                    ⚡ CẬP NHẬT
                </button>
                <a href="view_customer.php?id=<?= $customer_id; ?>" class="btn-back">
                    ↩ HUỶ
                </a>
            </div>
        </form>

        <!-- Contract List -->
        <div class="contract-list">
            <h3 style="color: var(--text-secondary); margin-bottom: 20px;">📋 DANH SÁCH HỢP ĐỒNG HIỆN TẠI</h3>
            <?php 
            $contracts->data_seek(0);
            while ($c = $contracts->fetch_assoc()) { 
                $status_color = $c['status'] == 'active' ? 'var(--success)' : 'var(--danger)';
            ?>
                <div class="contract-item">
                    <h4><?= htmlspecialchars($c['code']); ?> - <span style="color: <?= $status_color; ?>"><?= strtoupper($c['status']); ?></span></h4>
                    <p><strong>Loại:</strong> <?= htmlspecialchars($c['type']); ?> | <strong>Giá trị:</strong> <?= number_format($c['value']); ?> VND</p>
                    <p><strong>Ngày ký:</strong> <?= $c['sign_date']; ?> | <strong>Ngày hết hạn:</strong> <?= $c['expiry_date']; ?></p>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="site-footer">
    <div class="container">
        <p>&copy; <?= date("Y"); ?> Bảo Hiểm Phương Tiện. All Rights Reserved.</p>
        <p>Địa chỉ: 123 Đường ABC, Quận 1, TP.HCM | Email: info@baohiempt.com | Hotline: 1900 1234</p>
    </div>
</footer>

<script>
    // Show/hide extend date based on radio selection
    document.querySelectorAll('input[name="action"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const extendDateGroup = document.getElementById('extend-date-group');
            if (this.value === 'extend') {
                extendDateGroup.style.display = 'block';
            } else {
                extendDateGroup.style.display = 'none';
            }
        });
    });

    // Initialize hide extend date group if terminate is selected
    document.addEventListener('DOMContentLoaded', function() {
        const extendDateGroup = document.getElementById('extend-date-group');
        const terminateRadio = document.querySelector('input[value="terminate"]');
        if (terminateRadio.checked) {
            extendDateGroup.style.display = 'none';
        }
    });
</script>

</body>
</html>