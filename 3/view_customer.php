<?php include 'db.php'; ?>
<?php
$id = $_GET['id'];
$result = $conn->query("SELECT * FROM customers WHERE id=$id");
$row = $result->fetch_assoc();
$contracts = $conn->query("SELECT * FROM contracts WHERE customer_id=$id");

// Decode history if it's JSON
$history_data = $row['history'];
if ($history_data && $history_data[0] == '[') {
    $history_array = json_decode($history_data, true);
    $history_display = is_array($history_array) ? implode(", ", $history_array) : $history_data;
} else {
    $history_display = $history_data;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi Tiết Khách Hàng - Bảo Hiểm Phương Tiện</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Additional Styles for Customer Detail Page */
        .page-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .customer-header {
            background: rgba(10, 10, 20, 0.9);
            backdrop-filter: blur(15px);
            border: 2px solid var(--primary);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 0 40px rgba(0, 234, 255, 0.3);
            position: relative;
            overflow: hidden;
        }

        .customer-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 234, 255, 0.05), transparent);
            transition: 0.5s;
        }

        .customer-header:hover::before {
            left: 100%;
        }

        .page-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .page-title h1 {
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

        .customer-badge {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #000;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
            box-shadow: 0 0 20px rgba(0, 234, 255, 0.4);
        }

        .customer-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .info-card {
            background: rgba(15, 15, 30, 0.6);
            border: 1px solid rgba(0, 234, 255, 0.2);
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s ease;
        }

        .info-card:hover {
            border-color: var(--primary);
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 234, 255, 0.2);
        }

        .info-card h3 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid rgba(0, 234, 255, 0.3);
            padding-bottom: 8px;
        }

        .info-item {
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .info-label {
            color: var(--text-secondary);
            font-weight: 600;
            min-width: 120px;
        }

        .info-value {
            color: var(--text);
            text-align: right;
            flex: 1;
            margin-left: 15px;
        }

        .contracts-section {
            background: rgba(10, 10, 20, 0.9);
            backdrop-filter: blur(15px);
            border: 2px solid var(--secondary);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 0 40px rgba(255, 106, 0, 0.3);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .section-header h2 {
            background: linear-gradient(45deg, var(--secondary), #ffaa00);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .contract-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .contract-table th {
            background: linear-gradient(135deg, rgba(255, 106, 0, 0.2), rgba(255, 170, 0, 0.2));
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            color: var(--secondary);
            border-bottom: 2px solid var(--secondary);
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .contract-table td {
            padding: 18px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .contract-table tr:hover {
            background: rgba(255, 106, 0, 0.08);
            transform: translateX(5px);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-active {
            background: rgba(0, 255, 157, 0.1);
            color: #00ff9d;
            border: 1px solid #00ff9d;
        }

        .status-expired {
            background: rgba(255, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .status-pending {
            background: rgba(255, 170, 0, 0.1);
            color: #ffaa00;
            border: 1px solid #ffaa00;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #000;
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 0 25px rgba(0, 234, 255, 0.4);
        }

        .btn-secondary {
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
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-back {
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
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover, .btn-secondary:hover, .btn-back:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .btn-primary:hover { box-shadow: 0 0 35px rgba(0, 234, 255, 0.6); }
        .btn-secondary:hover { box-shadow: 0 0 25px rgba(255, 106, 0, 0.4); }
        .btn-back:hover { box-shadow: 0 0 25px rgba(255, 68, 68, 0.4); }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .empty-state .icon {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-container {
                margin: 20px auto;
                padding: 0 15px;
            }

            .customer-header, .contracts-section {
                padding: 25px;
            }

            .customer-info-grid {
                grid-template-columns: 1fr;
            }

            .info-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .info-value {
                text-align: left;
                margin-left: 0;
                margin-top: 5px;
            }

            .section-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-primary, .btn-secondary, .btn-back {
                width: 100%;
                text-align: center;
                justify-content: center;
            }

            .contract-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>

<!-- Top Bar -->
<div class="top-bar">
    <h1>Chi Tiết Khách Hàng</h1>
    <div class="top-menu">
        <div class="user-menu">
            <div class="user-name">
                Hi, <strong><?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></strong> ⌄
            </div>
        </div>
        <a href="index.php" class="btn-back">← Quay Lại DSKH</a>
    </div>
</div>

<div class="page-container">
    <!-- Customer Header -->
    <div class="customer-header fade-in">
        <div class="page-title">
            <h1>THÔNG TIN CHI TIẾT</h1>
            <div class="customer-badge">
                <?= htmlspecialchars($row['code']); ?>
            </div>
        </div>

        <div class="customer-info-grid">
            <!-- Personal Information -->
            <div class="info-card">
                <h3>👤 THÔNG TIN CÁ NHÂN</h3>
                <div class="info-item">
                    <span class="info-label">Họ Tên:</span>
                    <span class="info-value"><?= htmlspecialchars($row['name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Ngày Sinh:</span>
                    <span class="info-value"><?= $row['dob'] ? date('d/m/Y', strtotime($row['dob'])) : 'Chưa cập nhật'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">CMND/CCCD:</span>
                    <span class="info-value"><?= htmlspecialchars($row['id_card']) ?: 'Chưa cập nhật'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Nghề Nghiệp:</span>
                    <span class="info-value"><?= htmlspecialchars($row['occupation']) ?: 'Chưa cập nhật'; ?></span>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="info-card">
                <h3>📞 THÔNG TIN LIÊN HỆ</h3>
                <div class="info-item">
                    <span class="info-label">Địa Chỉ:</span>
                    <span class="info-value"><?= htmlspecialchars($row['address']) ?: 'Chưa cập nhật'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Số Điện Thoại:</span>
                    <span class="info-value"><?= htmlspecialchars($row['phone']) ?: 'Chưa cập nhật'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?= htmlspecialchars($row['email']) ?: 'Chưa cập nhật'; ?></span>
                </div>
            </div>

            <!-- Insurance Information -->
            <div class="info-card">
                <h3>🛡️ THÔNG TIN BẢO HIỂM</h3>
                <div class="info-item">
                    <span class="info-label">Loại Bảo Hiểm:</span>
                    <span class="info-value"><?= htmlspecialchars($row['insurance_type']) ?: 'Chưa cập nhật'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Lịch Sử GD:</span>
                    <span class="info-value"><?= htmlspecialchars($history_display) ?: 'Không có lịch sử'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">ID Khách Hàng:</span>
                    <span class="info-value">#<?= $id; ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Contracts Section -->
    <div class="contracts-section fade-in">
        <div class="section-header">
            <h2>📋 HỢP ĐỒNG BẢO HIỂM</h2>
            <a href="update_contract.php?customer_id=<?= $id; ?>" class="btn-primary">
                ➕ QUẢN LÝ HỢP ĐỒNG
            </a>
        </div>

        <?php if ($contracts->num_rows > 0): ?>
            <table class="contract-table">
                <thead>
                    <tr>
                        <th>Mã HD</th>
                        <th>Ngày Ký</th>
                        <th>Ngày Hết Hạn</th>
                        <th>Loại</th>
                        <th>Giá Trị</th>
                        <th>Phí</th>
                        <th>Trạng Thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($contract = $contracts->fetch_assoc()) { 
                        $status_class = 'status-' . $contract['status'];
                    ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($contract['code']); ?></strong></td>
                            <td><?= date('d/m/Y', strtotime($contract['sign_date'])); ?></td>
                            <td><?= date('d/m/Y', strtotime($contract['expiry_date'])); ?></td>
                            <td><?= htmlspecialchars($contract['type']); ?></td>
                            <td><?= number_format($contract['value']); ?> VND</td>
                            <td><?= number_format($contract['fee']); ?> VND</td>
                            <td>
                                <span class="status-badge <?= $status_class; ?>">
                                    <?= strtoupper($contract['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="icon">📄</div>
                <h3>Chưa có hợp đồng nào</h3>
                <p>Khách hàng này chưa có hợp đồng bảo hiểm nào được tạo.</p>
                <a href="update_contract.php?customer_id=<?= $id; ?>" class="btn-primary" style="margin-top: 20px;">
                    ➕ TẠO HỢP ĐỒNG ĐẦU TIÊN
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="edit_customer.php?id=<?= $id; ?>" class="btn-secondary">
            ✏️ CHỈNH SỬA THÔNG TIN
        </a>
        <a href="update_contract.php?customer_id=<?= $id; ?>" class="btn-primary">
            📄 QUẢN LÝ HỢP ĐỒNG
        </a>
        <a href="index.php" class="btn-back">
            ↩ QUAY LẠI DANH SÁCH
        </a>
    </div>
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