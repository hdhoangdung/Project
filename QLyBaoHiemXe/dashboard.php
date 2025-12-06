<?php 
include 'includes/header.php'; 
include 'config/database.php'; 
?>

<div class="welcome-section">
    <h1><i class="fas fa-tachometer-alt"></i> Bảng Điều Khiển</h1>
    <?php if ($user): ?>
    <p class="welcome-message">
        Chào mừng <strong><?php echo htmlspecialchars($user['full_name']); ?></strong> trở lại hệ thống!
    </p>
    <?php endif; ?>
</div>

<?php
// Lấy thống kê
try {
    // Tổng khách hàng
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM customers");
    $total_customers = $stmt->fetch()['total'];
    
    // Hợp đồng đang hiệu lực
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM contracts WHERE status = 'active'");
    $active_contracts = $stmt->fetch()['total'];
    
    // Hợp đồng sắp hết hạn (30 ngày)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM contracts WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND status = 'active'");
    $expiring_contracts = $stmt->fetch()['total'];
    
    // Tổng giá trị bảo hiểm
    $stmt = $pdo->query("SELECT SUM(insurance_value) as total FROM contracts WHERE status = 'active'");
    $total_insurance_value = $stmt->fetch()['total'] ?? 0;
    
    // Tổng doanh thu (tổng phí bảo hiểm)
    $stmt = $pdo->query("SELECT SUM(premium) as total FROM contracts WHERE status = 'active'");
    $total_revenue = $stmt->fetch()['total'] ?? 0;
    
} catch(PDOException $e) {
    $total_customers = 0;
    $active_contracts = 0;
    $expiring_contracts = 0;
    $total_insurance_value = 0;
    $total_revenue = 0;
}

// Hàm format currency
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . ' đ';
}
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3>Tổng Khách Hàng</h3>
            <span class="stat-number"><?php echo $total_customers; ?></span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-file-contract"></i>
        </div>
        <div class="stat-info">
            <h3>Hợp Đồng Đang Hiệu Lực</h3>
            <span class="stat-number"><?php echo $active_contracts; ?></span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-info">
            <h3>Hợp Đồng Sắp Hết Hạn</h3>
            <span class="stat-number"><?php echo $expiring_contracts; ?></span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-info">
            <h3>Tổng Giá Trị BH</h3>
            <span class="stat-number"><?php echo formatCurrency($total_insurance_value); ?></span>
        </div>
    </div>
</div>

<div class="quick-actions">
    <h2><i class="fas fa-bolt"></i> Thao Tác Nhanh</h2>
    <div class="action-buttons-large">
        <a href="add_customer.php" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Thêm Khách Hàng
        </a>
        <a href="add_contract.php" class="btn btn-success">
            <i class="fas fa-file-signature"></i> Tạo Hợp Đồng Mới
        </a>
        <a href="customers.php" class="btn btn-info">
            <i class="fas fa-users"></i> Danh Sách Khách Hàng
        </a>
        <a href="contracts.php" class="btn btn-warning">
            <i class="fas fa-file-contract"></i> Danh Sách Hợp Đồng
        </a>
    </div>
</div>

<div class="detail-grid">
    <!-- Khách hàng mới nhất -->
    <div class="detail-card">
        <div class="detail-card-header">
            <h3><i class="fas fa-user-clock"></i> Khách Hàng Mới Nhất</h3>
        </div>
        <div class="detail-card-body">
            <?php
            try {
                $stmt = $pdo->query("SELECT * FROM customers ORDER BY created_at DESC LIMIT 5");
                $recent_customers = $stmt->fetchAll();
                
                if (count($recent_customers) > 0):
            ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mã KH</th>
                            <th>Họ Tên</th>
                            <th>SĐT</th>
                            <th>Loại BH</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_customers as $customer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['customer_code']); ?></td>
                            <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                            <td>
                                <span class="badge badge-info"><?php echo htmlspecialchars($customer['insurance_type']); ?></span>
                            </td>
                            <td>
                                <a href="customer_detail.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-view" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users fa-2x"></i>
                <h4>Chưa có khách hàng</h4>
                <p>Hãy thêm khách hàng đầu tiên vào hệ thống.</p>
                <a href="add_customer.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Thêm Khách Hàng Đầu Tiên
                </a>
            </div>
            <?php endif; 
            } catch(PDOException $e) {
                echo '<div class="alert alert-error">Lỗi khi tải dữ liệu khách hàng: ' . $e->getMessage() . '</div>';
            }
            ?>
        </div>
    </div>

    <!-- Hợp đồng sắp hết hạn -->
    <div class="detail-card">
        <div class="detail-card-header">
            <h3><i class="fas fa-exclamation-circle"></i> Hợp Đồng Sắp Hết Hạn</h3>
        </div>
        <div class="detail-card-body">
            <?php
            try {
                $stmt = $pdo->query("SELECT c.*, cust.full_name, cust.customer_code 
                                   FROM contracts c 
                                   JOIN customers cust ON c.customer_id = cust.id 
                                   WHERE c.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
                                   AND c.status = 'active' 
                                   ORDER BY c.expiry_date ASC 
                                   LIMIT 5");
                $expiring_contracts = $stmt->fetchAll();
                
                if (count($expiring_contracts) > 0):
            ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mã HĐ</th>
                            <th>Khách Hàng</th>
                            <th>Ngày Hết Hạn</th>
                            <th>Còn Lại</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($expiring_contracts as $contract): 
                            $expiry_date = strtotime($contract['expiry_date']);
                            $today = strtotime(date('Y-m-d'));
                            $days_left = floor(($expiry_date - $today) / (60 * 60 * 24));
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($contract['contract_code']); ?></td>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($contract['full_name']); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($contract['customer_code']); ?></small>
                                </div>
                            </td>
                            <td><?php echo date('d/m/Y', $expiry_date); ?></td>
                            <td>
                                <span class="badge <?php echo $days_left <= 7 ? 'badge-warning' : 'badge-info'; ?>">
                                    <?php echo $days_left; ?> ngày
                                </span>
                            </td>
                            <td>
                                <a href="edit_contract.php?id=<?php echo $contract['id']; ?>" class="btn btn-sm btn-edit" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-check-circle fa-2x"></i>
                <h4>Không có hợp đồng sắp hết hạn</h4>
                <p>Tất cả hợp đồng đều trong trạng thái tốt.</p>
            </div>
            <?php endif; 
            } catch(PDOException $e) {
                echo '<div class="alert alert-error">Lỗi khi tải dữ liệu hợp đồng: ' . $e->getMessage() . '</div>';
            }
            ?>
        </div>
    </div>
</div>

<!-- Recent Activity Section -->
<div class="detail-card mt-4">
    <div class="detail-card-header">
        <h3><i class="fas fa-chart-line"></i> Tổng Quan Doanh Thu</h3>
    </div>
    <div class="detail-card-body">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--success);">
                    <i class="fas fa-money-bill"></i>
                </div>
                <div class="stat-info">
                    <h3>Tổng Doanh Thu</h3>
                    <span class="stat-number"><?php echo formatCurrency($total_revenue); ?></span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--info);">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div class="stat-info">
                    <h3>Giá Trị BH Trung Bình</h3>
                    <span class="stat-number">
                        <?php 
                        $avg_insurance = $active_contracts > 0 ? $total_insurance_value / $active_contracts : 0;
                        echo formatCurrency($avg_insurance);
                        ?>
                    </span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--warning);">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-info">
                    <h3>Tỷ Lệ Hiệu Quả</h3>
                    <span class="stat-number">
                        <?php 
                        $efficiency_rate = $total_customers > 0 ? ($active_contracts / $total_customers) * 100 : 0;
                        echo number_format($efficiency_rate, 1) . '%';
                        ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>