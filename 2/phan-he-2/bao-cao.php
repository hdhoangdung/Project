<?php
require_once __DIR__ . '/config.php'; // dùng PDO

function getReportData($pdo, $filters = []) {

    $conditions = [];
    $params = [];

    // Lọc từ ngày
    if (!empty($filters['from_date'])) {
        $conditions[] = "NgayTao >= :from_date";
        $params[':from_date'] = $filters['from_date'];
    }

    // Lọc đến ngày
    if (!empty($filters['to_date'])) {
        $conditions[] = "NgayTao <= :to_date";
        $params[':to_date'] = $filters['to_date'];
    }

    // Lọc trạng thái
    if (!empty($filters['status'])) {
        $conditions[] = "TrangThai = :status";
        $params[':status'] = $filters['status'];
    }

    $sql = "SELECT * FROM yeucauboithuong";

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    // Sắp xếp theo thời điểm tạo hồ sơ
    $sql .= " ORDER BY NgayTao DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Lấy dữ liệu filter từ GET
$filters = [
    'from_date' => $_GET['from_date'] ?? '',
    'to_date'   => $_GET['to_date'] ?? '',
    'status'    => $_GET['status'] ?? ''
];

$data = getReportData($pdo, $filters);
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Báo cáo - Phân hệ Yêu cầu bồi thường</title>
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
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

        .content-card { background:#fff; border-radius:12px; padding:18px; box-shadow:0 6px 18px rgba(15,23,42,0.06); margin-bottom:22px; }
        .card-header h2 { margin:0 0 10px 0; font-size:16px; }

        .search-grid { display:grid; grid-template-columns: repeat(3,1fr); gap:16px; }
        .search-grid input, .search-grid select {
            width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;
        }

        .btn { padding:10px 16px; border:none; border-radius:6px; cursor:pointer; background:#2563eb; color:#fff; }
        .btn:hover { background:#1d4ed8; }

        .table-wrapper { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:10px 12px; border-bottom:1px solid #eef2f7; font-size:14px; color:#111827; }
        thead th { background:#fbfdff; color:#374151; font-weight:600; }

        .badge-wait { background:#f59e0b; color:#fff; padding:6px 8px; border-radius:6px; }
        .badge-ok { background:#10b981; color:#fff; padding:6px 8px; border-radius:6px; }
        .badge-rej { background:#ef4444; color:#fff; padding:6px 8px; border-radius:6px; }
    </style>
</head>

<body>
<div class="app-wrapper">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>🚗 PHÂN HỆ BỒI THƯỜNG</h2>
            <div class="user-info">
                <?php echo htmlspecialchars($_SESSION['NGUOI_DUNG']['name'] ?? 'User'); ?>
            </div>
        </div>

        <ul class="nav-menu">
            <li><a href="index.php">🏠 Trang chủ</a></li>
            <li><a href="tiep-nhan.php">📥 Tiếp nhận</a></li>
            <li><a href="tham-dinh.php">🔍 Thẩm định</a></li>
            <li><a href="phe-duyet.php">📑 Phê duyệt</a></li>
            <li><a href="tra-cuu.php">🔎 Tra cứu</a></li>
            <li><a href="bao-cao.php" class="active">📊 Báo cáo</a></li>
            <li><a href="../dang-xuat.php">🚪 Đăng xuất</a></li>
        </ul>
    </aside>

    <!-- Main -->
    <main class="main-content">
        <div class="page-header">
            <h1>Báo cáo</h1>
            <div class="breadcrumb">Trang chủ / Báo cáo tổng hợp</div>
        </div>

        <!-- Bộ lọc -->
        <div class="content-card">
            <div class="card-header"><h2>Bộ lọc báo cáo</h2></div>

            <form method="GET">
                <div class="search-grid">

                    <div>
                        <label>Từ ngày</label>
                        <input type="date" name="from_date" value="<?php echo $_GET['from_date'] ?? ''; ?>">
                    </div>

                    <div>
                        <label>Đến ngày</label>
                        <input type="date" name="to_date" value="<?php echo $_GET['to_date'] ?? ''; ?>">
                    </div>

                    <div>
                        <label>Trạng thái</label>
                        <select name="status">
                            <option value="">-- Tất cả --</option>
                            <option value="Chờ duyệt" <?php if(($_GET['status'] ?? '')=='Chờ duyệt') echo 'selected'; ?>>Chờ duyệt</option>
                            <option value="Đã duyệt" <?php if(($_GET['status'] ?? '')=='Đã duyệt') echo 'selected'; ?>>Đã duyệt</option>
                            <option value="Từ chối"  <?php if(($_GET['status'] ?? '')=='Từ chối') echo 'selected'; ?>>Từ chối</option>
                        </select>
                    </div>

                </div>

                <div style="margin-top:18px;">
                    <button class="btn">Lọc báo cáo</button>
                </div>
            </form>
        </div>

        <!-- Kết quả báo cáo -->
        <div class="content-card">
            <div class="card-header"><h2>Kết quả</h2></div>

            <div class="table-wrapper">
                <table>
                    <thead>
                    <tr>
                        <th>Mã YC</th>
                        <th>Mã HĐ</th>
                        <th>Ngày yêu cầu</th>
                        <th>Ngày sự cố</th>
                        <th>Số tiền đề xuất</th>
                        <th>Trạng thái</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php if (!empty($data)): ?>

                        <?php foreach ($data as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['MaYC']); ?></td>
                                <td><?php echo htmlspecialchars($r['MaHD']); ?></td>
                                <td><?php echo htmlspecialchars($r['NgayYeuCau']); ?></td>
                                <td><?php echo htmlspecialchars($r['NgaySuCo']); ?></td>
                                <td><?php echo number_format($r['SoTienDeXuat']); ?></td>

                                <td>
                                    <?php
                                    $st = $r['TrangThai'];
                                    if (stripos($st, 'Chờ') !== false) echo '<span class="badge-wait">'.$st.'</span>';
                                    elseif (stripos($st, 'Đã') !== false) echo '<span class="badge-ok">'.$st.'</span>';
                                    else echo '<span class="badge-rej">'.$st.'</span>';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:25px; color:#6b7280;">
                                Không có dữ liệu phù hợp.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>

                </table>
            </div>
        </div>

    </main>
</div>
</body>
</html>
