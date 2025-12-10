<?php
/**
 * Quản lý phiếu thu phí bảo hiểm
 * Tạo, sửa, xóa, lọc phiếu thu
 */
require_once '../config.php';
requireRole('KeToan');

// Autoloader đơn giản
spl_autoload_register(function ($class) {
    $file = dirname(__DIR__) . '/app/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) require_once $file;
});

use App\Models\Receipt;
use App\Services\ReceiptService;

$user = getCurrentUser();

// Khởi tạo Services
$receiptModel = new Receipt($conn);
$receiptService = new ReceiptService($receiptModel);

$message = '';
$message_type = '';

// Xử lý actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'create':
            $result = $receiptService->create([
                'ma_hd' => safe($_POST['ma_hd']),
                'ngay_gd' => safe($_POST['ngay_gd']),
                'so_tien' => safe($_POST['so_tien']),
                'ghi_chu' => safe($_POST['ghi_chu']),
                'ma_nv' => $user['ma_nv']
            ]);
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'error';
            break;

        case 'edit':
            $result = $receiptService->update([
                'ma_gd' => safe($_POST['ma_gd']),
                'so_tien' => safe($_POST['so_tien']),
                'ngay_gd' => safe($_POST['ngay_gd']),
                'ghi_chu' => safe($_POST['ghi_chu'])
            ]);
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'error';
            break;

        case 'delete':
            $result = $receiptService->delete(safe($_POST['ma_gd']));
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'error';
            break;
    }
}

// Lấy dữ liệu
$contracts = $receiptService->getContracts();
$receipts = $receiptService->getList([
    'search' => $_GET['search'] ?? '',
    'tu_ngay' => $_GET['tu_ngay'] ?? '',
    'den_ngay' => $_GET['den_ngay'] ?? ''
]);

$list_phieuthu = $receipts['list'];
$tong_thu = $receipts['total'];
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý phiếu thu - Kế toán</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Kiểu CSS nội tuyến -->
    <style>
    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 13px;
    }

    .btn-warning {
        background: #f59e0b;
        color: white;
    }

    .btn-warning:hover {
        background: #d97706;
    }

    .btn-info {
        background: #3b82f6;
        color: white;
    }

    .btn-info:hover {
        background: #2563eb;
    }

    /* Modal styles - Enhanced & Modern */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: white;
        padding: 0;
        border-radius: 16px;
        max-width: 480px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from {
            transform: translateY(30px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 24px;
        border-radius: 16px 16px 0 0;
        margin-bottom: 0;
        border: none;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 600;
        letter-spacing: 0.3px;
    }

    .modal-footer {
        padding: 20px 24px;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
        border-radius: 0 0 16px 16px;
    }

    /* Form styles inside modal */
    .modal-content .form-group {
        padding: 0 24px;
        margin-bottom: 16px;
    }

    .modal-content .form-group:first-of-type {
        padding-top: 24px;
    }

    .modal-content .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        font-size: 13px;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .modal-content input[type="text"],
    .modal-content input[type="date"],
    .modal-content input[type="number"] {
        width: 100%;
        padding: 11px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s;
        background: #f9fafb;
    }

    .modal-content input[type="text"]:focus,
    .modal-content input[type="date"]:focus,
    .modal-content input[type="number"]:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .modal-content input[type="text"]:disabled {
        background: #f3f4f6;
        color: #9ca3af;
        cursor: not-allowed;
    }

    /* Button styles in modal */
    .modal-footer .btn {
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.2s;
        cursor: pointer;
        border: none;
    }

    .modal-footer .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        flex: 1;
    }

    .modal-footer .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }

    .modal-footer .btn-secondary {
        background: #e5e7eb;
        color: #374151;
    }

    .modal-footer .btn-secondary:hover {
        background: #d1d5db;
    }

    .filter-box {
        background: #f9fafb;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .filter-row {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: end;
    }

    .filter-row .form-group {
        margin-bottom: 0;
        flex: 1;
        min-width: 200px;
    }

    .stat-summary {
        display: flex;
        gap: 16px;
        margin-bottom: 20px;
    }

    .stat-summary .stat-item {
        background: #f0fdf4;
        padding: 16px;
        border-radius: 8px;
        flex: 1;
    }

    .stat-summary .stat-item .label {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 4px;
    }

    .stat-summary .stat-item .value {
        font-size: 20px;
        font-weight: 700;
        color: #10b981;
    }

    /* Tabs specific styles */
    .tabs {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        border-bottom: 2px solid #e5e7eb;
    }

    .tab {
        padding: 12px 24px;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        font-weight: 500;
        transition: all 0.3s;
        color: #6b7280;
    }

    .tab:hover {
        background: #f9fafb;
        color: #374151;
    }

    .tab.active {
        border-bottom-color: #667eea;
        color: #667eea;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }
    </style>
</head>

<body>
    <div class="app-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>⚖️ PHÂN HỆ KẾ TOÁN</h2>
                <div class="user-info"><?php echo $user['name']; ?></div>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php"><i>🏠</i> Trang chủ</a></li>
                <li><a href="phieu-thu.php" class="active"><i>💰</i> Quản lý phiếu thu</a></li>
                <li><a href="phieu-chi.php"><i>💸</i> Quản lý phiếu chi</a></li>
                <li><a href="bao-cao.php"><i>📊</i> Báo cáo thu chi</a></li>
                <li><a href="../logout.php"><i>🚪</i> Đăng xuất</a></li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <div class="page-header">
                <h1>Quản lý phiếu thu</h1>
                <div class="breadcrumb">Kế toán / Phiếu thu phí bảo hiểm</div>
            </div>

            <!-- Thông báo -->
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <!-- Tabs -->
            <div class="tabs">
                <div class="tab active" onclick="switchTab('create')">➕ Tạo phiếu thu</div>
                <div class="tab" onclick="switchTab('list')">📋 Danh sách phiếu thu</div>
            </div>

            <!-- Tab: Tạo phiếu thu -->
            <div id="tab-create" class="tab-content active">
                <!-- Form tạo phiếu thu -->
                <div class="content-card">
                    <div class="card-header">
                        <h2>Tạo phiếu thu mới</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" onsubmit="return validateForm()">
                            <input type="hidden" name="action" value="create">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Chọn hợp đồng *</label>
                                    <select name="ma_hd" id="ma_hd" required onchange="autoFillAmount(this)">
                                        <option value="">-- Chọn hợp đồng --</option>
                                        <?php foreach ($contracts as $hd): ?>
                                        <option value="<?php echo $hd['MaHD']; ?>"
                                            data-phi="<?php echo $hd['PhiBaoHiem']; ?>">
                                            HD-<?php echo str_pad($hd['MaHD'], 3, '0', STR_PAD_LEFT); ?> |
                                            <?php echo htmlspecialchars($hd['HoTen']); ?> |
                                            <?php echo htmlspecialchars($hd['BienSo']); ?> |
                                            Phí: <?php echo vnd($hd['PhiBaoHiem']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="so_tien">Số tiền thu (VNĐ) *</label>
                                    <input type="number" name="so_tien" id="so_tien" required min="1000" step="1000">
                                </div>
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="ngay_gd">Ngày thu *</label>
                                    <input type="date" name="ngay_gd" id="ngay_gd" required
                                        value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="ghi_chu">Ghi chú</label>
                                    <input type="text" name="ghi_chu" id="ghi_chu"
                                        placeholder="VD: Thu phí bảo hiểm năm 2024">
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">✓ Tạo phiếu thu</button>
                                <button type="reset" class="btn btn-secondary">↻ Làm mới</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tab: Danh sách -->
            <div id="tab-list" class="tab-content">
                <!-- Bộ lọc & tìm kiếm -->
                <div class="content-card">
                    <div class="card-header">
                        <h2>Tìm kiếm & lọc</h2>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <input type="hidden" name="tab" value="list" id="tabInput">
                            <div class="filter-row">
                                <div class="form-group">
                                    <label>Từ khóa (Mã/Tên KH/Biển số)</label>
                                    <input type="text" name="search"
                                        value="<?php echo htmlspecialchars($search_keyword); ?>"
                                        placeholder="Nhập từ khóa...">
                                </div>
                                <div class="form-group">
                                    <label>Từ ngày</label>
                                    <input type="date" name="tu_ngay" value="<?php echo $tu_ngay; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Đến ngày</label>
                                    <input type="date" name="den_ngay" value="<?php echo $den_ngay; ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">🔍 Tìm kiếm</button>
                                <a href="phieu-thu.php" class="btn btn-secondary">↻ Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Thống kê -->
                <div class="stat-summary">
                    <div class="stat-item">
                        <div class="label">Tổng số phiếu</div>
                        <div class="value"><?php echo $receipts['count']; ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="label">Tổng tiền thu</div>
                        <div class="value"><?php echo vnd($tong_thu); ?></div>
                    </div>
                </div>

                <!-- Danh sách phiếu thu -->
                <div class="content-card">
                    <div class="card-header">
                        <h2>Danh sách phiếu thu</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Mã phiếu</th>
                                        <th>Khách hàng</th>
                                        <th>Biển số</th>
                                        <th>Số tiền</th>
                                        <th>Ngày thu</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($list_phieuthu) > 0): ?>
                                    <?php foreach ($list_phieuthu as $pt): ?>
                                    <tr>
                                        <td><strong>PT-<?php echo str_pad($pt['MaPT'], 4, '0', STR_PAD_LEFT); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($pt['HoTen']); ?></td>
                                        <td><?php echo htmlspecialchars($pt['BienSo']); ?></td>
                                        <td style="color: #10b981;"><strong><?php echo vnd($pt['SoTien']); ?></strong>
                                        </td>
                                        <td><?php echo dateVN($pt['NgayThu']); ?></td>
                                        <td><span class="badge badge-success">Hoạt động</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button onclick='openEditModal(<?php echo json_encode($pt); ?>)' class="btn btn-sm btn-warning">✏️ Sửa</button>
                                                <button onclick="confirmDelete('<?php echo $pt['MaPT']; ?>')" class="btn btn-sm btn-danger">🗑️ Xóa</button>
                                                <button onclick="printReceipt('<?php echo $pt['MaPT']; ?>')"
                                                    class="btn btn-sm btn-info">🖨️</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <tr>
                                        <td colspan="7">Không có phiếu thu nào</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> <!-- end tab-list -->
        </main>
    </div>

    <!-- Modal sửa phiếu -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✏️ Sửa phiếu thu</h3>
            </div>
            <form method="POST" action="" onsubmit="return validateEditForm()">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="ma_gd" id="edit_ma_gd">

                <div class="form-group">
                    <label>Mã phiếu</label>
                    <input type="text" id="edit_ma_phieu" disabled>
                </div>

                <div class="form-group">
                    <label for="edit_so_tien">Số tiền (VNĐ) *</label>
                    <input type="number" name="so_tien" id="edit_so_tien" required min="1000" step="1000">
                </div>

                <div class="form-group">
                    <label for="edit_ngay_gd">Ngày thu *</label>
                    <input type="date" name="ngay_gd" id="edit_ngay_gd" required max="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label for="edit_ghi_chu">Ghi chú</label>
                    <input type="text" name="ghi_chu" id="edit_ghi_chu">
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">💾 Lưu thay đổi</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Hủy</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Form xóa ẩn -->
    <form id="deleteForm" method="POST" action="" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="ma_gd" id="delete_ma_gd">
    </form>

    <script>
    // Chuyển đổi tab
    function switchTab(tab) {
        // Remove active class from all tabs and content
        const allTabs = document.querySelectorAll('.tabs .tab');
        const allContents = document.querySelectorAll('.tab-content');

        allTabs.forEach(t => t.classList.remove('active'));
        allContents.forEach(c => c.classList.remove('active'));

        // Add active class to selected tab and content
        if (tab === 'create') {
            if (allTabs[0]) allTabs[0].classList.add('active');
            const createTab = document.getElementById('tab-create');
            if (createTab) createTab.classList.add('active');
        } else if (tab === 'list') {
            if (allTabs[1]) allTabs[1].classList.add('active');
            const listTab = document.getElementById('tab-list');
            if (listTab) listTab.classList.add('active');
        }

        // Save active tab to sessionStorage
        sessionStorage.setItem('activeTab', tab);
    }

    // On page load, restore the active tab from URL parameter or sessionStorage
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        let activeTab = urlParams.get('tab');

        // If no tab parameter in URL, check sessionStorage
        if (!activeTab) {
            activeTab = sessionStorage.getItem('activeTab') || 'create';
        }

        // Switch to the appropriate tab
        switchTab(activeTab);
    });

    // Các hàm form
    function autoFillAmount(select) {
        const phi = select.options[select.selectedIndex].getAttribute('data-phi');
        if (phi) document.getElementById('so_tien').value = phi;
    }

    function validateForm() {
        const soTien = document.getElementById('so_tien').value;
        if (soTien < 1000) {
            alert('Số tiền phải lớn hơn 1,000 VNĐ');
            return false;
        }
        return confirm('Xác nhận tạo phiếu thu?');
    }

    function openEditModal(data) {
        document.getElementById('edit_ma_gd').value = data.MaPT;
        document.getElementById('edit_ma_phieu').value = 'PT-' + String(data.MaPT).padStart(4, '0');
        document.getElementById('edit_so_tien').value = data.SoTien;
        document.getElementById('edit_ngay_gd').value = data.NgayThu;
        document.getElementById('edit_ghi_chu').value = data.GhiChu || '';
        document.getElementById('editModal').classList.add('show');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.remove('show');
    }

    function validateEditForm() {
        return confirm('Xác nhận lưu thay đổi?');
    }

    function confirmDelete(maPT, maPhieu) {
        if (confirm('⚠️ Xác nhận xóa phiếu ' + maPhieu +
                '?\n\nPhiếu sẽ bị đánh dấu "Đã hủy" và không thể khôi phục!')) {
            document.getElementById('delete_ma_gd').value = maPT;
            document.getElementById('deleteForm').submit();
        }
    }

    function printReceipt(maPT) {
        window.open('print-phieu-thu.php?id=' + maPT, '_blank', 'width=800,height=600');
    }

    // Đóng modal khi click bên ngoài
    window.onclick = function(event) {
        const modal = document.getElementById('editModal');
        if (event.target == modal) {
            closeEditModal();
        }
    }
    </script>
</body>

</html>