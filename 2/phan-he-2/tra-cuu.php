<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Bật báo lỗi để debug redirect
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();




// KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['NGUOI_DUNG'])) {
    header("Location: /Projects/dang-nhap.php");
    exit();
}

include __DIR__ . '/config.php';

// ==========================
// XUẤT EXCEL
// ==========================
if (isset($_GET['export']) && $_GET['export'] == 1) {

    if (empty($_SESSION['last_query'])) {
        die("Không có dữ liệu để export.");
    }

    // Tách query và params
    list($savedQuery, $savedParams) = explode("|", $_SESSION['last_query']);
    $savedParams = json_decode($savedParams, true);

    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=tra_cuu_boithuong.xls");

    echo "<table border='1'>
            <tr>
                <th>Mã YC</th>
                <th>Mã HĐ</th>
                <th>Mô tả sự cố</th>
                <th>Số tiền đề xuất</th>
                <th>Ngày yêu cầu</th>
                <th>Trạng thái</th>
            </tr>";

    $stmt = $pdo->prepare($savedQuery);
    $stmt->execute($savedParams);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $r) {
        echo "<tr>
                <td>{$r['MaYC']}</td>
                <td>{$r['MaHD']}</td>
                <td>{$r['MoTaSuCo']}</td>
                <td>{$r['SoTienDeXuat']}</td>
                <td>{$r['NgayYeuCau']}</td>
                <td>{$r['TrangThai']}</td>
              </tr>";
    }

    echo "</table>";
    exit();
}

// ==========================
// XỬ LÝ TRA CỨU
// ==========================

$where = " WHERE 1=1 ";
$params = [];

// 🔥 Sửa lỗi sai biến (trước đây bạn dùng $_GET['id'])
if (!empty($_GET['MaYC'])) {
    $where .= " AND MaYC = :MaYC ";
    $params[':MaYC'] = $_GET['MaYC'];
}

// Tìm theo Mã hợp đồng
if (!empty($_GET['MaHD'])) {
    $where .= " AND MaHD LIKE :MaHD ";
    $params[':MaHD'] = "%" . $_GET['MaHD'] . "%";
}

// Trạng thái
if (!empty($_GET['TrangThai'])) {
    $where .= " AND TrangThai = :TrangThai ";
    $params[':TrangThai'] = $_GET['TrangThai'];
}

// Lọc ngày từ
if (!empty($_GET['from'])) {
    $where .= " AND NgayYeuCau >= :NgayFrom ";
    $params[':NgayFrom'] = $_GET['from'];
}

// Lọc ngày đến
if (!empty($_GET['to'])) {
    $where .= " AND NgayYeuCau <= :NgayTo ";
    $params[':NgayTo'] = $_GET['to'];
}

// Lọc tiền từ
if (!empty($_GET['tien_from'])) {
    $where .= " AND SoTienDeXuat >= :TienFrom ";
    $params[':TienFrom'] = $_GET['tien_from'];
}

// Lọc tiền đến
if (!empty($_GET['tien_to'])) {
    $where .= " AND SoTienDeXuat <= :TienTo ";
    $params[':TienTo'] = $_GET['tien_to'];
}

$query = "SELECT * FROM yeucauboithuong $where ORDER BY NgayYeuCau DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 🔥 Lưu query và params đúng chuẩn để export
$_SESSION['last_query'] = $query . "|" . json_encode($params);

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Tra cứu hồ sơ - Phân hệ Yêu cầu bồi thường</title>

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

        .search-grid { display:grid; grid-template-columns: repeat(4,1fr); gap:16px; }
        .search-grid input, .search-grid select {
            width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;
        }

        .btn { padding:10px 16px; border:none; border-radius:6px; cursor:pointer; background:#2563eb; color:#fff; }
        .btn:hover { background:#1d4ed8; }

        .btn-export { background:#059669; margin-left:8px; }
        .btn-export:hover { background:#047857; }

        .table-wrapper { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:10px 12px; border-bottom:1px solid #eef2f7; font-size:14px; color:#111827; }
        thead th { background:#fbfdff; color:#374151; font-weight:600; }

        .badge { display:inline-block; padding:6px 8px; border-radius:8px; font-size:12px; color:#fff; }
        .badge-wait { background:#f59e0b; }
        .badge-ok { background:#10b981; }
        .badge-rej { background:#ef4444; }
        .muted { color:#6b7280; font-size:13px; }
    </style>
</head>

<body>
    <div class="app-wrapper">

        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>🚗 PHÂN HỆ BỒI THƯỜNG</h2>
                
                <div class="user-info">
                    <!-- SỬA session CHO ĐÚNG -->
                    <?php echo htmlspecialchars($_SESSION['NGUOI_DUNG']['name'] ?? 'User'); ?>
                </div>
            </div>

            <ul class="nav-menu">
                <li><a href="index.php">🏠 Trang chủ</a></li>
                <li><a href="tiep-nhan.php">📥 Tiếp nhận</a></li>
                <li><a href="tham-dinh.php">🔍 Thẩm định</a></li>
                <li><a href="phe-duyet.php">📑 Phê duyệt</a></li>
                
                <!-- SỬA: đánh dấu trang hiện tại -->
                <li><a href="tra-cuu.php" class="active">🔎 Tra cứu</a></li>

                <li><a href="bao-cao.php">📊 Báo cáo</a></li>

                <!-- SỬA: Đăng xuất phải đi ra ngoài 1 cấp -->
                <li><a href="../dang-xuat.php">🚪 Đăng xuất</a></li>
            </ul>
        </aside>

        <!-- Main -->
        <main class="main-content">

            <div class="page-header">
                <h1>Tra cứu hồ sơ</h1>
                <div class="breadcrumb">Trang chủ / Tra cứu</div>
            </div>

            <!-- Form lọc -->
            <div class="content-card">
                <div class="card-header"><h2>Bộ lọc tra cứu</h2></div>

                <form method="GET">
                    <div class="search-grid">

                        <div>
                            <input type="text" name="MaYC" placeholder="Mã yêu cầu" 
                                   value="<?php echo $_GET['MaYC'] ?? ''; ?>">
                        </div>

                        <div>
                            <input type="text" name="MaHD" placeholder="Mã hợp đồng"
                                   value="<?php echo $_GET['MaHD'] ?? ''; ?>">
                        </div>

                        <div>
                            <select name="TrangThai">
                                <option value="">-- Trạng thái --</option>
                                <option value="Chờ duyệt"  <?php if(($_GET['TrangThai'] ?? '')=='Chờ duyệt') echo 'selected'; ?>>Chờ duyệt</option>
                                <option value="Đã duyệt"   <?php if(($_GET['TrangThai'] ?? '')=='Đã duyệt') echo 'selected'; ?>>Đã duyệt</option>
                                <option value="Từ chối"    <?php if(($_GET['TrangThai'] ?? '')=='Từ chối') echo 'selected'; ?>>Từ chối</option>
                            </select>
                        </div>

                        <div>
                            <input type="date" name="from" value="<?php echo $_GET['from'] ?? ''; ?>">
                        </div>

                        <div>
                            <input type="date" name="to" value="<?php echo $_GET['to'] ?? ''; ?>">
                        </div>

                        <!-- Thêm 2 lọc tiền -->
                        <div>
                            <input type="number" name="tien_from" placeholder="Số tiền từ"
                            value="<?php echo $_GET['tien_from'] ?? ''; ?>">
                        </div>

                        <div>
                            <input type="number" name="tien_to" placeholder="Số tiền đến"
                            value="<?php echo $_GET['tien_to'] ?? ''; ?>">
                        </div>

                    </div>

                    <div style="margin-top:18px;">
                        <button class="btn">Tra cứu</button>

                        <a class="btn btn-export" href="tra-cuu.php?export=1">
                            Xuất Excel
                        </a>
                    </div>
                </form>
            </div>

            <!-- Kết quả -->
            <div class="content-card">
                <div class="card-header"><h2>Kết quả tra cứu</h2></div>

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
                            <?php if (count($result) > 0): ?>
                                <?php foreach ($result as $r): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($r['MaYC']); ?></td>
                                    <td><?php echo htmlspecialchars($r['MaHD']); ?></td>
                                    <td><?php echo htmlspecialchars($r['NgayYeuCau']); ?></td>
                                    <td><?php echo htmlspecialchars($r['NgaySuCo']); ?></td>
                                    <td><?php echo number_format($r['SoTienDeXuat']); ?></td>

                                    <td>
                                        <?php
                                            $st = $r['TrangThai'];
                                            if (stripos($st, 'Chờ') !== false) {
                                                echo '<span class="badge badge-wait">'.$st.'</span>';
                                            } elseif (stripos($st, 'Đã') !== false) {
                                                echo '<span class="badge badge-ok">'.$st.'</span>';
                                            } else {
                                                echo '<span class="badge badge-rej">'.$st.'</span>';
                                            }
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>

                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align:center; padding:25px; color:#6b7280;">
                                        Không tìm thấy dữ liệu.
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
