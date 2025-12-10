<?php
/**
 * Dashboard Phân hệ 2 - Yêu cầu bồi thường
 * Hiển thị thống kê chung và 10 hồ sơ mới nhất
 */

require_once 'config.php';

// echo '<pre>';
// print_r($_SESSION);
// echo '</pre>';
// exit;


// Thay role nếu bạn dùng tên khác: 'GiamDinh' / 'NhanVien' / 'QuanLy'
requireRole('GiamDinh');

$user = getCurrentUser();

// Thời gian: tháng hiện tại, hôm nay
$monthStart = date('Y-m-01');
$today = date('Y-m-d');

// Thống kê
$stats = [];
// Tổng hồ sơ
$stats['tong'] = (int)dbGetValue("SELECT COUNT(*) FROM yeucauboithuong", []);

// Chờ thẩm định (trạng thái 'Chờ thẩm định' theo schema)
$stats['cho_tham_dinh'] = (int)dbGetValue("SELECT COUNT(*) FROM yeucauboithuong WHERE TrangThai = ?", ['Chờ thẩm định']);

// Chờ phê duyệt (ví dụ 'Chờ duyệt' hoặc 'Chờ phê duyệt' - điều chỉnh nếu khác)
$stats['cho_phe_duyet'] = (int)dbGetValue("SELECT COUNT(*) FROM yeucauboithuong WHERE TrangThai = ?", ['Chờ duyệt']);

// Đã duyệt (Đã duyệt / Đã duyệt - tùy tên)
$stats['da_duyet'] = (int)dbGetValue("SELECT COUNT(*) FROM yeucauboithuong WHERE TrangThai = ?", ['Đã duyệt']);

// Hồ sơ hôm nay
$stats['hom_nay'] = (int)dbGetValue("SELECT COUNT(*) FROM yeucauboithuong WHERE NgayYeuCau = ?", [$today]);

// Lấy 10 hồ sơ mới nhất
$sql_recent = "
    SELECT y.MaYC, y.MaHD, y.NgayYeuCau, y.NgaySuCo, y.DiaDiemSuCo, y.MoTaSuCo, y.SoTienDeXuat, y.SoTienDuyet,
           y.TrangThai, y.MaNVGiamDinh,
           h.MaKH, k.HoTen AS TenKhach
    FROM yeucauboithuong y
    LEFT JOIN hopdong h 
        ON y.MaHD COLLATE utf8mb4_unicode_ci 
           = h.MaHD COLLATE utf8mb4_unicode_ci
    LEFT JOIN khachhang k 
        ON h.MaKH COLLATE utf8mb4_unicode_ci 
           = k.MaKH COLLATE utf8mb4_unicode_ci
    ORDER BY y.NgayYeuCau DESC, y.MaYC DESC
    LIMIT 10
";

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Dashboard - Phân hệ Yêu cầu bồi thường</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Nhỏ gọn style bổ sung giống phan-he-4 */
        .app-wrapper { display:flex; min-height:100vh; background:#f5f7fb; }
        .sidebar { width:250px; background:#1f2937; color:#fff; padding:20px 16px; }
        .sidebar-header h2 { margin:0 0 8px 0; font-size:18px; }
        .user-info { font-size:13px; color:#cbd5e1; margin-bottom:18px; }
        .nav-menu { list-style:none; padding:0; margin:0; }
        .nav-menu li { margin-bottom:10px; }
        .nav-menu a { display:block; color:#e2e8f0; text-decoration:none; padding:8px 10px; border-radius:6px; }
        .nav-menu a.active, .nav-menu a:hover { background:#374151; color:#fff; }
        .main-content { flex:1; padding:28px; }
        .page-header h1 { margin:0 0 6px 0; font-size:22px; }
        .breadcrumb { color:#6b7280; font-size:13px; margin-bottom:20px; }

        .stats-grid { display:grid; grid-template-columns: repeat(4,1fr); gap:16px; margin-bottom:22px; }
        .stat-card { background:#fff; padding:18px; border-radius:12px; box-shadow:0 6px 18px rgba(15,23,42,0.06); }
        .stat-card .icon { font-size:22px; display:inline-block; margin-bottom:8px; }
        .stat-card h3 { margin:0; font-size:14px; color:#374151; }
        .stat-card .value { font-size:20px; font-weight:700; margin-top:8px; color:#111827; }
        .stat-card .subtitle { font-size:12px; color:#6b7280; margin-top:6px; }

        .content-card { background:#fff; border-radius:12px; padding:18px; box-shadow:0 6px 18px rgba(15,23,42,0.06); }
        .card-header h2 { margin:0 0 10px 0; font-size:16px; }
        .table-wrapper { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:10px 12px; border-bottom:1px solid #eef2f7; text-align:left; font-size:14px; color:#111827; }
        thead th { background:#fbfdff; color:#374151; font-weight:600; }
        .badge { display:inline-block; padding:6px 8px; border-radius:8px; font-size:12px; color:#fff; }
        .badge-wait { background:#f59e0b; } /* cam - chờ */
        .badge-ok { background:#10b981; } /* xanh - duyệt */
        .badge-rej { background:#ef4444; } /* đỏ - từ chối */
        .muted { color:#6b7280; font-size:13px; }
    </style>
</head>

<body>
    <div class="app-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>🚗 PHÂN HỆ BỒI THƯỜNG</h2>
                <div class="user-info"><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></div>
            </div>

            <ul class="nav-menu">
                <li><a href="index.php" class="active">🏠 Trang chủ</a></li>
                <li><a href="tiep-nhan.php">📥 Tiếp nhận</a></li>
                <li><a href="tham-dinh.php">🔍 Thẩm định</a></li>
                <li><a href="phe-duyet.php">📑 Phê duyệt</a></li>
                <li><a href="tra-cuu.php">🔎 Tra cứu</a></li>
                <li><a href="bao-cao.php">📊 Báo cáo</a></li>
                <li><a href="../logout.php">🚪 Đăng xuất</a></li>
            </ul>
        </aside>

        <!-- Main -->
        <main class="main-content">
            <div class="page-header">
                <h1>Dashboard - Yêu cầu bồi thường</h1>
                <div class="breadcrumb">Trang chủ / Tổng quan</div>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="icon">📂</div>
                    <h3>Tổng hồ sơ</h3>
                    <div class="value"><?php echo number_format($stats['tong']); ?></div>
                    <div class="subtitle">Tổng số hồ sơ đã tiếp nhận</div>
                </div>

                <div class="stat-card">
                    <div class="icon">🕵️‍♂️</div>
                    <h3>Chờ thẩm định</h3>
                    <div class="value"><?php echo number_format($stats['cho_tham_dinh']); ?></div>
                    <div class="subtitle">Hồ sơ cần thẩm định</div>
                </div>

                <div class="stat-card">
                    <div class="icon">✅</div>
                    <h3>Chờ phê duyệt</h3>
                    <div class="value"><?php echo number_format($stats['cho_phe_duyet']); ?></div>
                    <div class="subtitle">Hồ sơ đã thẩm định, chờ quyết định</div>
                </div>

                <div class="stat-card">
                    <div class="icon">✔️</div>
                    <h3>Đã duyệt</h3>
                    <div class="value"><?php echo number_format($stats['da_duyet']); ?></div>
                    <div class="subtitle">Hồ sơ đã được phê duyệt</div>
                </div>
            </div>

            <!-- Today's -->
            <div style="margin-bottom:18px;">
                <div class="stat-card content-card" style="display:flex; align-items:center; justify-content:space-between;">
                    <div>
                        <h3>Hôm nay</h3>
                        <div class="value"><?php echo number_format($stats['hom_nay']); ?></div>
                        <div class="subtitle">Hồ sơ tiếp nhận hôm nay (<?php echo dateVN($today); ?>)</div>
                    </div>
                    <div class="muted">Cập nhật tự động theo ngày</div>
                </div>
            </div>

            <!-- Recent -->
            <div class="content-card">
                <div class="card-header">
                    <h2>10 hồ sơ mới nhất</h2>
                </div>
                <div class="card-body">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>MaYC</th>
                                    <th>MaHD</th>
                                    <th>Khách hàng</th>
                                    <th>Ngày YC</th>
                                    <th>Ngày sự cố</th>
                                    <th>Số tiền đề xuất</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent && $recent->num_rows > 0): ?>
                                    <?php while ($r = $recent->fetch_assoc()): ?>
                                        <tr>
                                            <td><a href="tra-cuu.php?MaYC=<?php echo urlencode($r['MaYC']); ?>"><?php echo htmlspecialchars($r['MaYC']); ?></a></td>
                                            <td><?php echo htmlspecialchars($r['MaHD'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($r['TenKhach'] ?? ($r['MaKH'] ?? '---')); ?></td>
                                            <td><?php echo dateVN($r['NgayYeuCau'] ?? ''); ?></td>
                                            <td><?php echo dateVN($r['NgaySuCo'] ?? ''); ?></td>
                                            <td><?php echo vnd($r['SoTienDeXuat']); ?></td>
                                            <td>
                                                <?php
                                                    $st = $r['TrangThai'] ?? '';
                                                    if (stripos($st, 'Chờ') !== false) {
                                                        echo '<span class="badge badge-wait">'.htmlspecialchars($st).'</span>';
                                                    } elseif (stripos($st, 'Đã') !== false || stripos($st,'Đã duyệt')!==false || stripos($st,'Đã phê duyệt')!==false) {
                                                        echo '<span class="badge badge-ok">'.htmlspecialchars($st).'</span>';
                                                    } elseif (stripos($st,'Từ chối')!==false || stripos($st,'Từ Chối')!==false) {
                                                        echo '<span class="badge badge-rej">'.htmlspecialchars($st).'</span>';
                                                    } else {
                                                        echo '<span class="muted">'.htmlspecialchars($st).'</span>';
                                                    }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" style="text-align:center; padding:30px; color:#6b7280;">
                                            Không có hồ sơ nào.
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
