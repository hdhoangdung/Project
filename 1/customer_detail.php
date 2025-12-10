<?php 
include 'includes/header.php'; 
include 'config/database.php'; 
?>

<div class="page-header">
    <h1><i class="fas fa-users"></i> Quản Lý Khách Hàng</h1>
    <a href="add_customer.php" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Thêm Khách Hàng
    </a>
</div>

<div class="search-section">
    <form method="GET" class="search-form">
        <div class="search-group">
            <input type="text" name="search" placeholder="Tìm theo tên hoặc mã khách hàng..." 
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit" class="btn btn-search">
                <i class="fas fa-search"></i> Tìm Kiếm
            </button>
        </div>
    </form>
</div>

<div class="table-section">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Mã KH</th>
                    <th>Họ Tên</th>
                    <th>Số Điện Thoại</th>
                    <th>Loại Bảo Hiểm</th>
                    <th>Trạng Thái</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $search = isset($_GET['search']) ? $_GET['search'] : '';
                $sql = "SELECT c.*, 
                       (SELECT COUNT(*) FROM contracts WHERE customer_id = c.id AND status = 'active') as active_contracts
                       FROM customers c 
                       WHERE c.full_name LIKE ? OR c.customer_code LIKE ?
                       ORDER BY c.created_at DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(["%$search%", "%$search%"]);
                $customers = $stmt->fetchAll();

                foreach($customers as $customer):
                    $status = $customer['active_contracts'] > 0 ? 'Có hiệu lực' : 'Không có hợp đồng';
                    $status_class = $customer['active_contracts'] > 0 ? 'badge-success' : 'badge-secondary';
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($customer['customer_code']); ?></td>
                    <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                    <td><?php echo htmlspecialchars($customer['insurance_type']); ?></td>
                    <td>
                        <span class="badge <?php echo $status_class; ?>">
                            <?php echo $status; ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="customer_detail.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-view" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="edit_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-edit" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="javascript:void(0)" onclick="deleteCustomer(<?php echo $customer['id']; ?>)" class="btn btn-sm btn-delete" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function deleteCustomer(id) {
    if (confirm('Bạn có chắc chắn muốn xóa khách hàng này?')) {
        window.location.href = 'delete_customer.php?id=' + id;
    }
}
</script>

<?php include 'includes/footer.php'; ?>