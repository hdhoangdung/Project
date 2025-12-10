<?php

declare(strict_types=1);

use UngDung\DichVu\NhatKyDichVu;
use UngDung\DichVu\XacThucDichVu;
use UngDung\HoTro\DinhDang;
use UngDung\KhoDuLieu\PhieuChiKho;

require_once __DIR__ . '/../cau-hinh.php';

$xacThuc = new XacThucDichVu();
$xacThuc->batBuocVaiTro('KeToan');
$nguoiDung = $xacThuc->nguoiDung();

$phieuChiKho = new PhieuChiKho();
$nhatKy = new NhatKyDichVu();

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'create') {
        $duLieu = [
            'ma_yc' => trim((string) ($_POST['ma_yc'] ?? '')),
            'noi_dung' => trim((string) ($_POST['noi_dung'] ?? '')),
            'so_tien' => (float) ($_POST['so_tien'] ?? 0),
            'ngay_chi' => $_POST['ngay_chi'] ?? date('Y-m-d'),
            'ghi_chu' => trim((string) ($_POST['ghi_chu'] ?? '')),
        ];
        $ketQua = $phieuChiKho->tao($duLieu, $nguoiDung ?? []);
        $message = $ketQua['thong_diep'];
        $message_type = $ketQua['ok'] ? 'success' : 'error';
        if ($ketQua['ok']) {
            $nhatKy->ghi('phieuchi', (string) ($ketQua['ma_pc'] ?? ''), 'CREATE', null, $duLieu, $nguoiDung['ma_nv'] ?? null);
        }
    } elseif ($action === 'edit') {
        $duLieu = [
            'ma_pc' => $_POST['ma_pc'] ?? '',
            'so_tien' => (float) ($_POST['so_tien'] ?? 0),
            'ngay_chi' => $_POST['ngay_chi'] ?? date('Y-m-d'),
            'ghi_chu' => trim((string) ($_POST['ghi_chu'] ?? '')),
        ];
        $ketQua = $phieuChiKho->capNhat($duLieu);
        $message = $ketQua['thong_diep'];
        $message_type = $ketQua['ok'] ? 'success' : 'error';
        if ($ketQua['ok']) {
            $nhatKy->ghi('phieuchi', (string) $duLieu['ma_pc'], 'UPDATE', null, $duLieu, $nguoiDung['ma_nv'] ?? null);
        }
    } elseif ($action === 'delete') {
        $maPc = $_POST['ma_pc'] ?? '';
        $ketQua = $phieuChiKho->xoa($maPc);
        $message = $ketQua['thong_diep'];
        $message_type = $ketQua['ok'] ? 'success' : 'error';
        if ($ketQua['ok']) {
            $nhatKy->ghi('phieuchi', (string) $maPc, 'DELETE', null, null, $nguoiDung['ma_nv'] ?? null);
        }
    }
}

$boLoc = [
    'tu_khoa' => $_GET['search'] ?? '',
    'tu_ngay' => $_GET['tu_ngay'] ?? '',
    'den_ngay' => $_GET['den_ngay'] ?? '',
    'loai_chi' => $_GET['loai_chi'] ?? '',
];

$list_yeucau = $phieuChiKho->danhSachYeuCau();
$list_phieuchi = $phieuChiKho->danhSach($boLoc);
$tong_chi = $phieuChiKho->tongChi($boLoc);

$search_keyword = $boLoc['tu_khoa'];
$tu_ngay = $boLoc['tu_ngay'];
$den_ngay = $boLoc['den_ngay'];
$loai_chi = $boLoc['loai_chi'];
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý phiếu chi - Kế toán</title>
    <link rel="stylesheet" href="../tai-nguyen/css/style.css">
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

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

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

    .modal-content .form-group {
        padding: 0 24px;
        margin-bottom: 18px;
    }

    .modal-content .form-group:first-of-type {
        padding-top: 24px;
    }

    .modal-content input,
    .modal-content select,
    .modal-content textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
    }

    .modal-content input:focus,
    .modal-content select:focus,
    .modal-content textarea:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        background: #f9fafb;
    }

    .modal-content input:disabled,
    .modal-content select:disabled {
        background: #f3f4f6;
        color: #9ca3af;
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 24px;
        border-radius: 16px 16px 0 0;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 20px;
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

    .modal-footer .btn {
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .modal-footer .btn.btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .modal-footer .btn.btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }

    .modal-footer .btn.btn-secondary {
        background: #e5e7eb;
        color: #374151;
    }

    .modal-footer .btn.btn-secondary:hover {
        background: #d1d5db;
        transform: translateY(-2px);
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
        min-width: 180px;
    }

    .stat-summary {
        display: flex;
        gap: 16px;
        margin-bottom: 20px;
    }

    .stat-summary .stat-item {
        background: #fef2f2;
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
        color: #ef4444;
    }

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
    }

    .tab:hover {
        background: #f9fafb;
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
                <div class="user-info"><?php echo htmlspecialchars($nguoiDung['name'] ?? ''); ?></div>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php"><i>🏠</i> Trang chủ</a></li>
                <li><a href="phieu-thu.php"><i>💰</i> Quản lý phiếu thu</a></li>
                <li><a href="phieu-chi.php" class="active"><i>💸</i> Quản lý phiếu chi</a></li>
                <li><a href="bao-cao.php"><i>📊</i> Báo cáo thu chi</a></li>
                <li><a href="../dang-xuat.php"><i>🚪</i> Đăng xuất</a></li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <div class="page-header">
                <h1>Quản lý phiếu chi</h1>
                <div class="breadcrumb">Kế toán / Phiếu chi</div>
            </div>

            <!-- Thông báo -->
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <!-- Tabs -->
            <div class="tabs">
                <div class="tab active" onclick="switchTab('create')">➕ Tạo phiếu chi</div>
                <div class="tab" onclick="switchTab('list')">📋 Danh sách phiếu chi</div>
            </div>

            <!-- Tab: Tạo phiếu chi -->
            <div id="tab-create" class="tab-content active">
                <div class="content-card">
                    <div class="card-header">
                        <h2>Tạo phiếu chi mới</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" onsubmit="return validateForm()">
                            <input type="hidden" name="action" value="create">

                            <div class="form-group">
                                <label>Loại chi *</label>
                                <select id="loai_chi_select" onchange="toggleChiFields(this.value)" required>
                                    <option value="">-- Chọn loại chi --</option>
                                    <option value="boi_thuong">Chi bồi thường</option>
                                    <option value="khac">Chi phí khác</option>
                                </select>
                            </div>

                            <!-- Chi bồi thường -->
                            <div id="boi_thuong_fields" style="display:none;">
                                <div class="form-group">
                                    <label>Chọn yêu cầu bồi thường *</label>
                                    <select name="ma_yc" id="ma_yc" onchange="autoFillBT(this)">
                                        <option value="">-- Chọn yêu cầu đã duyệt --</option>
                                        <?php foreach ($list_yeucau as $yc): ?>
                                        <option value="<?php echo htmlspecialchars($yc['MaYC']); ?>"
                                            data-tien="<?php echo htmlspecialchars($yc['SoTienDuyet']); ?>">
                                            <?php echo htmlspecialchars($yc['MaYC']); ?> |
                                            <?php echo htmlspecialchars($yc['HoTen']); ?> |
                                            <?php echo htmlspecialchars($yc['BienSo']); ?> |
                                            <?php echo DinhDang::tien((float) $yc['SoTienDuyet']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Chi phí khác -->
                            <div id="khac_fields" style="display:none;">
                                <div class="form-group">
                                    <label>Nội dung chi *</label>
                                    <select name="noi_dung" id="noi_dung">
                                        <option value="Chi hoa hồng đại lý">Chi hoa hồng đại lý</option>
                                        <option value="Chi phí bảo dưỡng hệ thống">Chi phí bảo dưỡng hệ thống</option>
                                        <option value="Chi phí quảng cáo">Chi phí quảng cáo</option>
                                        <option value="Chi lương nhân viên">Chi lương nhân viên</option>
                                        <option value="Chi khác">Chi khác</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Số tiền chi (VNĐ) *</label>
                                    <input type="number" name="so_tien" id="so_tien" required min="1000" step="1000">
                                </div>
                                <div class="form-group">
                                    <label>Ngày chi *</label>
                                    <input type="date" name="ngay_chi" id="ngay_chi" required value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Ghi chú</label>
                                <input type="text" name="ghi_chu" id="ghi_chu" placeholder="VD: Chi phí tháng 10/2025">
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">✓ Tạo phiếu chi</button>
                                <button type="reset" class="btn btn-secondary" onclick="resetForm()">↻ Làm mới</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tab: Danh sách -->
            <div id="tab-list" class="tab-content">
                <div class="content-card">
                    <div class="card-header">
                        <h2>Tìm kiếm & lọc</h2>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="filter-row">
                                <div class="form-group">
                                    <label>Từ khóa</label>
                                    <input type="text" name="search" value="<?php echo htmlspecialchars($search_keyword); ?>" placeholder="Mã phiếu, ghi chú...">
                                </div>
                                <div class="form-group">
                                    <label>Từ ngày</label>
                                    <input type="date" name="tu_ngay" value="<?php echo htmlspecialchars($tu_ngay); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Đến ngày</label>
                                    <input type="date" name="den_ngay" value="<?php echo htmlspecialchars($den_ngay); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Loại chi</label>
                                    <select name="loai_chi">
                                        <option value="">Tất cả</option>
                                        <option value="boi_thuong" <?php echo $loai_chi === 'boi_thuong' ? 'selected' : ''; ?>>Bồi thường</option>
                                        <option value="khac" <?php echo $loai_chi === 'khac' ? 'selected' : ''; ?>>Chi phí khác</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">🔍 Tìm</button>
                                <a href="phieu-chi.php" class="btn btn-secondary">↻ Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="stat-summary">
                    <div class="stat-item">
                        <div class="label">Tổng số phiếu</div>
                        <div class="value"><?php echo count($list_phieuchi); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="label">Tổng tiền chi</div>
                        <div class="value"><?php echo DinhDang::tien($tong_chi); ?></div>
                    </div>
                </div>

                <div class="content-card">
                    <div class="card-header">
                        <h2>Danh sách phiếu chi</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Mã phiếu</th>
                                        <th>Loại chi</th>
                                        <th>Khách hàng/Nội dung</th>
                                        <th>Biển số</th>
                                        <th>Số tiền</th>
                                        <th>Ngày chi</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($list_phieuchi) > 0): ?>
                                        <?php foreach ($list_phieuchi as $pc): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($pc['MaPC']); ?></strong></td>
                                            <td>
                                                <?php if ($pc['MaYC']): ?>
                                                <span class="badge badge-danger">Bồi thường</span>
                                                <?php else: ?>
                                                <span class="badge badge-warning">Chi phí</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($pc['MaYC']): ?>
                                                <?php echo htmlspecialchars($pc['HoTen'] ?? 'N/A'); ?><br>
                                                <small><?php echo htmlspecialchars(substr($pc['MoTaSuCo'] ?? '', 0, 40)); ?>...</small>
                                                <?php else: ?>
                                                <?php echo htmlspecialchars($pc['GhiChu'] ?? 'Chi phí khác'); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($pc['BienSo'] ?? '---'); ?></td>
                                            <td><strong style="color: #ef4444; font-size: 16px;"><?php echo DinhDang::tien((float) $pc['SoTien']); ?></strong></td>
                                            <td><?php echo DinhDang::ngay($pc['NgayChi']); ?></td>
                                            <td>
                                                <?php if (($pc['TrangThai'] ?? 'Đã chi trả') === 'Đã chi trả'): ?>
                                                <span class="badge badge-success">Đã chi trả</span>
                                                <?php else: ?>
                                                <span class="badge badge-danger">Đã hủy</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (($pc['TrangThai'] ?? 'Đã chi trả') === 'Đã chi trả'): ?>
                                                <div class="action-buttons">
                                                    <button onclick='openEditModal(<?php echo json_encode($pc); ?>)' class="btn btn-sm btn-warning">✏️ Sửa</button>
                                                    <button onclick="confirmDelete('<?php echo htmlspecialchars($pc['MaPC']); ?>')" class="btn btn-sm btn-danger">🗑️ Xóa</button>
                                                    <button onclick="printReceipt('<?php echo htmlspecialchars($pc['MaPC']); ?>')" class="btn btn-sm btn-info">🖨️</button>
                                                </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                    <tr>
                                        <td colspan="8" style="text-align: center; padding: 40px; color: #9ca3af;">Không tìm thấy phiếu chi nào</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal sửa -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✏️ Sửa phiếu chi</h3>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="ma_pc" id="edit_ma_pc">
                <div class="form-group">
                    <label>Mã phiếu</label>
                    <input type="text" id="edit_ma_phieu" disabled>
                </div>
                <div class="form-group">
                    <label>Số tiền (VNĐ) *</label>
                    <input type="number" name="so_tien" id="edit_so_tien" required min="1000" step="1000">
                </div>
                <div class="form-group">
                    <label>Ngày chi *</label>
                    <input type="date" name="ngay_chi" id="edit_ngay_chi" required max="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label>Ghi chú</label>
                    <input type="text" name="ghi_chu" id="edit_ghi_chu">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">💾 Lưu</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Hủy</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Form xóa ẩn -->
    <form id="deleteForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="ma_pc" id="delete_ma_pc">
    </form>

    <script>
    // Chuyển đổi tab
    function switchTab(tab) {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

        if (tab === 'create') {
            document.querySelector('.tab:nth-child(1)').classList.add('active');
            document.getElementById('tab-create').classList.add('active');
        } else {
            document.querySelector('.tab:nth-child(2)').classList.add('active');
            document.getElementById('tab-list').classList.add('active');
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

    function toggleChiFields(loai) {
        document.getElementById('boi_thuong_fields').style.display = loai === 'boi_thuong' ? 'block' : 'none';
        document.getElementById('khac_fields').style.display = loai === 'khac' ? 'block' : 'none';

        if (loai === 'boi_thuong') {
            document.getElementById('ma_yc').setAttribute('required', 'required');
            document.getElementById('noi_dung').removeAttribute('required');
        } else if (loai === 'khac') {
            document.getElementById('noi_dung').setAttribute('required', 'required');
            document.getElementById('ma_yc').removeAttribute('required');
        }
    }

    function autoFillBT(select) {
        const tien = select.options[select.selectedIndex].getAttribute('data-tien');
        if (tien) document.getElementById('so_tien').value = tien;
    }

    function validateForm() {
        const soTien = document.getElementById('so_tien').value;
        if (soTien < 1000) {
            alert('Số tiền phải lớn hơn 1,000 VNĐ');
            return false;
        }
        return confirm('Xác nhận tạo phiếu chi?');
    }

    function resetForm() {
        document.getElementById('loai_chi_select').value = '';
        toggleChiFields('');
    }

    function openEditModal(data) {
        document.getElementById('edit_ma_pc').value = data.MaPC;
        document.getElementById('edit_ma_phieu').value = data.MaPC;
        document.getElementById('edit_so_tien').value = data.SoTien;
        document.getElementById('edit_ngay_chi').value = data.NgayChi;
        document.getElementById('edit_ghi_chu').value = data.GhiChu || '';
        document.getElementById('editModal').classList.add('show');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.remove('show');
    }

    function confirmDelete(maPC) {
        if (confirm('⚠️ Xác nhận xóa phiếu ' + maPC + '?\n\nPhiếu sẽ bị đánh dấu "Đã hủy"!')) {
            document.getElementById('delete_ma_pc').value = maPC;
            document.getElementById('deleteForm').submit();
        }
    }

    function printReceipt(maPC) {
        window.open('print-phieu-chi.php?id=' + maPC, '_blank', 'width=800,height=600');
    }

    // Đóng modal khi click bên ngoài
    window.onclick = function(event) {
        const modal = document.getElementById('editModal');
        if (event.target === modal) closeEditModal();
    }
    </script>
</body>

</html>