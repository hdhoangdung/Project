<?php
/**
 * Dashboard kế toán: tổng thu, chi, lợi nhuận và 10 giao dịch gần đây
 * Hiển thị thống kê tháng hiện tại
 */

// ADJUSTED: load project config and shared middleware (paths updated for new structure)
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../src/shared/components/role_middleware.php';
require_role(['KeToan']); // block access if not logged in or role mismatch

$user = getCurrentUser();

// Sử dụng dbGetValue/dbQuery trực tiếp thay vì Service (tạm thời fix)
$stats = [];
$stats['thu'] = (float)dbGetValue(
    "SELECT COALESCE(SUM(SoTien), 0) FROM phieuthu 
     WHERE NgayThu BETWEEN ? AND ? AND TrangThai = 'Hoạt động'",
    [date('Y-m-01'), date('Y-m-d')]
);

$stats['chi'] = (float)dbGetValue(
    "SELECT COALESCE(SUM(SoTien), 0) FROM phieuchi 
     WHERE NgayChi BETWEEN ? AND ? AND TrangThai = 'Đã chi trả'",
    [date('Y-m-01'), date('Y-m-d')]
);

$stats['loi_nhuan'] = $stats['thu'] - $stats['chi'];

// Lấy giao dịch gần đây (dùng query trực tiếp)
$sql_recent = "SELECT t.*, h.MaHD, k.HoTen as ten_khach, k.SoDienThoai AS SDT
                FROM (
                    SELECT p.MaPT AS MaGD, 'Thu' AS LoaiGD, p.SoTien, p.NgayThu AS NgayGD, p.MaHD, p.GhiChu
                    FROM PhieuThu p WHERE p.TrangThai = 'Hoạt động'
                    UNION ALL
                    SELECT pc.MaPC AS MaGD, 'Chi' AS LoaiGD, pc.SoTien, pc.NgayChi AS NgayGD, yc.MaHD, pc.GhiChu
                    FROM PhieuChi pc
                    LEFT JOIN YeuCauBoiThuong yc ON pc.MaYC = yc.MaYC
                ) t
                LEFT JOIN hopdong h ON t.MaHD = h.MaHD
                LEFT JOIN khachhang k ON h.MaKH = k.MaKH
                ORDER BY t.NgayGD DESC, t.MaGD DESC
                LIMIT 10";

$recent_transactions = dbQuery($sql_recent);
$stats['gd_thang'] = $recent_transactions ? $recent_transactions->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Phân hệ Kế toán</title>
    <!-- use shared asset path -->
    <link rel="stylesheet" href="/src/shared/assets/css/style.css">
</head>

<body>
    <div class="app-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>⚖️ PHÂN HỆ KẾ TOÁN</h2>
                <div class="user-info"><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></div>
            </div>

            <ul class="nav-menu">
                <li><a href="/src/modules/ke-toan/index.php" class="active"><i>🏠</i> Trang chủ</a></li>
                <li><a href="phieu-thu.php"><i>💰</i> Quản lý phiếu thu</a></li>
                <li><a href="phieu-chi.php"><i>💸</i> Quản lý phiếu chi</a></li>
                <li><a href="bao-cao.php"><i>📊</i> Báo cáo thu chi</a></li>
                <li><a href="/logout.php"><i>🚪</i> Đăng xuất</a></li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Dashboard Kế toán</h1>
                <div class="breadcrumb">Trang chủ / Tổng quan</div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card green">
                    <span class="icon">💰</span>
                    <h3>Tổng Thu</h3>
                    <div class="value"><?php echo vnd($stats['thu']); ?></div>
                    <div class="subtitle">Phí bảo hiểm đã thu</div>
                </div>

                <div class="stat-card red">
                    <span class="icon">💸</span>
                    <h3>Tổng Chi</h3>
                    <div class="value"><?php echo vnd($stats['chi']); ?></div>
                    <div class="subtitle">Bồi thường đã chi trả</div>
                </div>

                <div class="stat-card blue">
                    <span class="icon">📈</span>
                    <h3>Lợi nhuận</h3>
                    <div class="value"><?php echo vnd($stats['loi_nhuan']); ?></div>
                    <div class="subtitle">
                        <?php 
                        $ty_le = $stats['thu'] > 0 ? ($stats['loi_nhuan'] / $stats['thu']) * 100 : 0;
                        echo number_format($ty_le, 1) . '%'; 
                        ?> tỷ suất
                    </div>
                </div>

                <div class="stat-card orange">
                    <span class="icon">📝</span>
                    <h3>Giao dịch tháng này</h3>
                    <div class="value"><?php echo $stats['gd_thang']; ?></div>
                    <div class="subtitle">Phiếu thu & chi</div>
                </div>
            </div>

            <!-- Recent Transactions Table -->
            <div class="content-card">
                <div class="card-header">
                    <h2>Giao dịch gần đây</h2>
                </div>
                <div class="card-body">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Mã GD</th>
                                    <th>Loại GD</th>
                                    <th>Khách hàng</th>
                                    <th>SĐT</th>
                                    <th>Số tiền</th>
                                    <th>Ngày GD</th>
                                    <th>Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent_transactions && $recent_transactions->num_rows > 0): ?>
                                <?php while ($row = $recent_transactions->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>GD-<?php echo str_pad($row['MaGD'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                                    <td>
                                        <?php if ($row['LoaiGD'] == 'Thu'): ?>
                                        <span class="badge badge-success">Thu</span>
                                        <?php else: ?>
                                        <span class="badge badge-danger">Chi</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['ten_khach'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['SDT'] ?? '---'); ?></td>
                                    <td>
                                        <strong style="color: <?php echo $row['LoaiGD'] == 'Thu' ? '#10b981' : '#ef4444'; ?>">
                                            <?php echo vnd($row['SoTien']); ?>
                                        </strong>
                                    </td>
                                    <td><?php echo dateVN($row['NgayGD'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars(substr($row['GhiChu'] ?? '', 0, 40) . (strlen($row['GhiChu'] ?? '') > 40 ? '...' : '')); ?></td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px; color: #9ca3af;">
                                        Chưa có giao dịch nào trong hệ thống
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>