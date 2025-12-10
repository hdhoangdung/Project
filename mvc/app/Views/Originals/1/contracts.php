<?php include 'includes/header.php'; ?>
<?php include 'config/database.php'; ?>

<div class="page-header">
	<h1><i class="fas fa-file-contract"></i> Quản Lý Hợp Đồng</h1>
	<a href="add_contract.php" class="btn btn-primary">
		<i class="fas fa-file-signature"></i> Tạo Hợp Đồng Mới
	</a>
</div>

<div class="search-section">
	<form method="GET" class="search-form">
		<div class="search-group">
			<input type="text" name="search" placeholder="Tìm theo mã hợp đồng hoặc mã khách hàng..." 
				   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
			<select name="status">
				<option value="">Tất cả trạng thái</option>
				<option value="active" <?php echo isset($_GET['status']) && $_GET['status'] == 'active' ? 'selected' : ''; ?>>Có hiệu lực</option>
				<option value="suspended" <?php echo isset($_GET['status']) && $_GET['status'] == 'suspended' ? 'selected' : ''; ?>>Tạm ngưng</option>
				<option value="expired" <?php echo isset($_GET['status']) && $_GET['status'] == 'expired' ? 'selected' : ''; ?>>Hết hạn</option>
			</select>
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
					<th>Mã HĐ</th>
					<th>Khách Hàng</th>
					<th>Mã Xe</th>
					<th>Ngày Ký</th>
					<th>Ngày Hết Hạn</th>
					<th>Loại BH</th>
					<th>Giá Trị BH</th>
					<th>Phí BH</th>
					<th>Trạng Thái</th>
					<th>Thao Tác</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$search = isset($_GET['search']) ? $_GET['search'] : '';
				$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
                
				$sql = "SELECT c.*, cust.full_name, cust.customer_code as cust_code 
					   FROM contracts c 
					   JOIN customers cust ON c.customer_id = cust.id 
					   WHERE (c.contract_code LIKE ? OR cust.customer_code LIKE ? OR cust.full_name LIKE ?)";
                
				$params = ["%$search%", "%$search%", "%$search%"];
                
				if ($status_filter) {
					$sql .= " AND c.status = ?";
					$params[] = $status_filter;
				}
                
				$sql .= " ORDER BY c.created_at DESC";
                
				$stmt = $pdo->prepare($sql);
				$stmt->execute($params);
				$contracts = $stmt->fetchAll();

				foreach($contracts as $contract):
					$status_badge = [
						'active' => ['class' => 'badge-success', 'text' => 'Có hiệu lực'],
						'suspended' => ['class' => 'badge-warning', 'text' => 'Tạm ngưng'],
						'expired' => ['class' => 'badge-secondary', 'text' => 'Hết hạn']
					];
                    
					$expiry_date = strtotime($contract['expiry_date']);
					$today = strtotime(date('Y-m-d'));
					$days_left = floor((($expiry_date) - $today) / (60 * 60 * 24));
				?>
				<tr>
					<td><?php echo htmlspecialchars($contract['contract_code']); ?></td>
					<td>
						<div>
							<strong><?php echo htmlspecialchars($contract['full_name']); ?></strong>
							<br>
							<small class="text-muted"><?php echo htmlspecialchars($contract['cust_code']); ?></small>
						</div>
					</td>
					<td><?php echo htmlspecialchars($contract['vehicle_code']); ?></td>
					<td><?php echo date('d/m/Y', strtotime($contract['sign_date'])); ?></td>
					<td>
						<?php 
						echo date('d/m/Y', $expiry_date);
						if ($days_left <= 30 && $days_left > 0 && $contract['status'] == 'active') {
							echo ' <span class="badge badge-warning">(' . $days_left . ' ngày)</span>';
						}
						?>
					</td>
					<td><?php echo htmlspecialchars($contract['insurance_type']); ?></td>
					<td><?php echo number_format($contract['insurance_value'], 0, ',', '.'); ?> đ</td>
					<td><?php echo number_format($contract['premium'], 0, ',', '.'); ?> đ</td>
					<td>
						<span class="badge <?php echo $status_badge[$contract['status']]['class']; ?>">
							<?php echo $status_badge[$contract['status']]['text']; ?>
						</span>
					</td>
					<td>
						<div class="action-buttons">
							<a href="edit_contract.php?id=<?php echo $contract['id']; ?>" class="btn btn-sm btn-edit" title="Sửa">
								<i class="fas fa-edit"></i>
							</a>
							<a href="javascript:void(0)" onclick="deleteContract(<?php echo $contract['id']; ?>)" class="btn btn-sm btn-delete" title="Xóa">
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
function deleteContract(id) {
	if (confirm('Bạn có chắc chắn muốn xóa hợp đồng này?')) {
		window.location.href = 'delete_contract.php?id=' + id;
	}
}
</script>

<?php include 'includes/footer.php'; ?>
