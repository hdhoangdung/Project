<?php
// ======================================================================
//  CHỨC NĂNG: THẨM ĐỊNH YÊU CẦU BỒI THƯỜNG
// ======================================================================

// ================== CẤU HÌNH ================== //
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'qlbh_xe';

// ================== HÀM KẾT NỐI DB ================== //
function connect_db() {
    global $dbHost, $dbUser, $dbPass, $dbName;

    $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($mysqli->connect_errno) {
        throw new Exception("Không thể kết nối database: " . $mysqli->connect_error);
    }
    $mysqli->set_charset("utf8mb4");
    return $mysqli;
}

// ================== KHAI BÁO BIẾN ================== //
$errors = [];
$success = null;
$yeucau = null;
$dsYeuCau = [];

// ======================================================================
// 1) LUÔN LẤY DANH SÁCH HỒ SƠ CHỜ THẨM ĐỊNH
// ======================================================================
try {
    $mysqli = connect_db();

    $stmt = $mysqli->prepare("SELECT * FROM yeucauboithuong 
                              WHERE TrangThai='Chờ thẩm định' 
                              ORDER BY NgayYeuCau DESC");
    $stmt->execute();
    $dsYeuCau = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Nếu có MaYC thì lấy chi tiết hồ sơ
    if (isset($_GET['MaYC']) && trim($_GET['MaYC']) !== '') {

        $MaYC = trim($_GET['MaYC']);

        $stmt2 = $mysqli->prepare("SELECT * FROM yeucauboithuong WHERE MaYC=? LIMIT 1");
        $stmt2->bind_param("s", $MaYC);
        $stmt2->execute();
        $yeucau = $stmt2->get_result()->fetch_assoc();
        $stmt2->close();

        if (!$yeucau) {
            $errors[] = "Không tìm thấy hồ sơ thẩm định với mã " . htmlspecialchars($MaYC);
        }
    }

    $mysqli->close();

} catch (Exception $e) {
    $errors[] = $e->getMessage();
}



// ======================================================================
// 2) XỬ LÝ FORM THẨM ĐỊNH (POST)
// ======================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $MaYC          = trim($_POST['MaYC']);
    $KetQua        = trim($_POST['KetQua']);
    $MaNV          = trim($_POST['MaNV']);
    $SoTienDeXuat  = trim($_POST['SoTienDeXuat']);

    // --------------------- KIỂM TRA DỮ LIỆU --------------------- //
    if ($MaYC === '') {
        $errors[] = "Thiếu mã yêu cầu.";
    }

    if ($KetQua === '') {
        $errors[] = "Vui lòng nhập kết quả thẩm định.";
    }

    if ($MaNV === '') {
        $errors[] = "Thiếu mã nhân viên thẩm định.";
    }

    if ($SoTienDeXuat === '' || !is_numeric($SoTienDeXuat)) {
        $errors[] = "Số tiền đề xuất không hợp lệ.";
    }

    // Ép kiểu số
    $SoTienDeXuat = (float)$SoTienDeXuat;

    if (!empty($errors)) {
        goto END_PROCESS;
    }

    // --------------------- XỬ LÝ CẬP NHẬT --------------------- //
    try {
        $mysqli = connect_db();
        $mysqli->autocommit(false);

        // Lấy dữ liệu cũ
        $stmt = $mysqli->prepare("SELECT TrangThai, SoTienDeXuat, KetQuaThamDinh 
                                  FROM yeucauboithuong WHERE MaYC=? LIMIT 1");
        $stmt->bind_param("s", $MaYC);
        $stmt->execute();
        $old = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$old) {
            throw new Exception("Không tìm thấy yêu cầu.");
        }

        // Chỉ cho phép thẩm định trạng thái hợp lệ
        $allowed = ['Chờ xử lý', 'Chờ thẩm định'];
        if (!in_array($old['TrangThai'], $allowed)) {
            throw new Exception("Hồ sơ không ở trạng thái hợp lệ để thẩm định.");
        }

        // Trạng thái mới — chuyển sang phê duyệt
        $newStatus = "Chờ phê duyệt";

        // Cập nhật hồ sơ
        $sql = "UPDATE yeucauboithuong 
                SET KetQuaThamDinh=?, TrangThai=?, SoTienDeXuat=?
                WHERE MaYC=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssds", $KetQua, $newStatus, $SoTienDeXuat, $MaYC);

        if (!$stmt->execute()) {
            throw new Exception("Lỗi khi cập nhật hồ sơ.");
        }
        $stmt->close();

        // Ghi log thay đổi
        $sqlLog = "INSERT INTO lichsuthaydoi
                   (BangDuLieu, MaBanGhi, HanhDong, DuLieuCu, DuLieuMoi, MaNV, ThoiGian)
                   VALUES ('YeuCauBoiThuong', ?, 'UPDATE', ?, ?, ?, NOW())";

        $duLieuCu = json_encode($old, JSON_UNESCAPED_UNICODE);
        $duLieuMoi = json_encode([
            "KetQuaThamDinh" => $KetQua,
            "TrangThai"      => $newStatus,
            "SoTienDeXuat"   => $SoTienDeXuat
        ], JSON_UNESCAPED_UNICODE);

        $stmtLog = $mysqli->prepare($sqlLog);
        $stmtLog->bind_param("ssss", $MaYC, $duLieuCu, $duLieuMoi, $MaNV);

        if (!$stmtLog->execute()) {
            throw new Exception("Lỗi ghi log thay đổi dữ liệu.");
        }
        $stmtLog->close();

        $mysqli->commit();
        $mysqli->close();

        $success = "Thẩm định hồ sơ thành công.";

    } catch (Exception $e) {
        if (isset($mysqli) && $mysqli instanceof mysqli) {
            $mysqli->rollback();
            $mysqli->close();
        }
        $errors[] = $e->getMessage();
    }
}

END_PROCESS:

?>



<?php
// Phần xử lý PHP (đã tối ưu ở đoạn trước)
// ...
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Thẩm định yêu cầu</title>
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

        .content-card { background:#fff; border-radius:12px; padding:18px; margin-bottom:20px;
            box-shadow:0 6px 18px rgba(15,23,42,0.06); }

        table { width:100%; border-collapse:collapse; }
        th, td { padding:10px 12px; border-bottom:1px solid #eef2f7; text-align:left; font-size:14px; color:#111827; }
        thead th { background:#fbfdff; color:#374151; font-weight:600; }

        .form-group { margin-bottom:14px; }
        .form-group label { font-weight:600; display:block; margin-bottom:6px; }
        .form-group input, .form-group textarea {
            width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px;
        }
        textarea { resize:vertical; min-height:90px; }

        .btn-primary {
            background:#2563eb; color:#fff; padding:10px 16px; border-radius:8px;
            border:none; cursor:pointer; font-weight:600;
        }
        .btn-primary:hover { background:#1d4ed8; }

        .alert { padding:12px 16px; border-radius:8px; margin-bottom:16px; }
        .alert-error { background:#fee2e2; color:#991b1b; }
        .alert-success { background:#dcfce7; color:#166534; }

        .two-cols { display:grid; grid-template-columns:1fr 350px; gap:20px; }
    </style>
</head>

<body>
<div class="app-wrapper">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>🚗 PHÂN HỆ BỒI THƯỜNG</h2>
            <div class="user-info">
                <?php echo htmlspecialchars($user['name'] ?? 'Nhân viên'); ?>
            </div>
        </div>

        <ul class="nav-menu">
            <li><a href="index.php">🏠 Trang chủ</a></li>
            <li><a href="tiep-nhan.php">📥 Tiếp nhận</a></li>
            <li><a href="tham-dinh.php" class="active">🔍 Thẩm định</a></li>
            <li><a href="phe-duyet.php">📑 Phê duyệt</a></li>
            <li><a href="tra-cuu.php">🔎 Tra cứu</a></li>
            <li><a href="bao-cao.php">📊 Báo cáo</a></li>
            <li><a href="../dang-xuat.php">🚪 Đăng xuất</a></li>
        </ul>
    </aside>

    <!-- Main -->
    <main class="main-content">

        <div class="page-header">
            <h1>Thẩm định yêu cầu bồi thường</h1>
            <div class="breadcrumb">Trang chủ / Thẩm định</div>
        </div>

        <!-- THÔNG BÁO -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $e) echo "- " . htmlspecialchars($e) . "<br>"; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="two-cols">

            <!-- CỘT 1: FORM HOẶC THÔNG BÁO “CHỌN HỒ SƠ” -->
            <div>
                <?php if (!$yeucau): ?>
                    <div class="content-card">
                        <h3>👈 Vui lòng chọn một hồ sơ từ danh sách bên phải</h3>
                    </div>
                <?php else: ?>

                <!-- THÔNG TIN HỒ SƠ -->
                <div class="content-card">
                    <h2>Thông tin yêu cầu</h2>
                    <table>
                        <tr><th>Mã yêu cầu</th><td><?php echo htmlspecialchars($yeucau['MaYC']); ?></td></tr>
                        <tr><th>Mã hợp đồng</th><td><?php echo htmlspecialchars($yeucau['MaHD']); ?></td></tr>
                        <tr><th>Mã khách hàng</th><td><?php echo htmlspecialchars($yeucau['MaKH']); ?></td></tr>
                        <tr><th>Ngày yêu cầu</th><td><?php echo htmlspecialchars($yeucau['NgayYeuCau']); ?></td></tr>
                        <tr><th>Mô tả</th><td><?php echo nl2br(htmlspecialchars($yeucau['MoTaSuCo'])); ?></td></tr>
                        <tr><th>Trạng thái</th><td><?php echo htmlspecialchars($yeucau['TrangThai']); ?></td></tr>
                    </table>
                </div>

                <!-- FORM THẨM ĐỊNH -->
                <div class="content-card">
                    <h2>Kết quả thẩm định</h2>

                    <form method="POST">
                        <input type="hidden" name="MaYC" value="<?php echo htmlspecialchars($yeucau['MaYC']); ?>">
                        <input type="hidden" name="MaNV" value="<?php echo htmlspecialchars($user['id'] ?? 'NV01'); ?>">

                        <div class="form-group">
                            <label>Kết quả *</label>
                            <textarea name="KetQua" required><?php echo htmlspecialchars($_POST['KetQua'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Số tiền đề xuất *</label>
                            <input type="number" step="1000" min="0"
                                   name="SoTienDeXuat"
                                   value="<?php echo htmlspecialchars($_POST['SoTienDeXuat'] ?? ''); ?>"
                                   required>
                        </div>

                        <button class="btn-primary" type="submit">Lưu kết quả</button>
                    </form>
                </div>

                <?php endif; ?>
            </div>

            <!-- CỘT 2: DANH SÁCH HỒ SƠ CHỜ THẨM ĐỊNH -->
            <div>
                <div class="content-card">
                    <h2>Danh sách chờ thẩm định</h2>

                    <table>
                        <thead>
                        <tr>
                            <th>Mã YC</th>
                            <th>Ngày YC</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($dsYeuCau)): ?>
                            <tr><td colspan="3">Không có hồ sơ nào.</td></tr>
                        <?php else: ?>
                            <?php foreach ($dsYeuCau as $yc): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($yc['MaYC']); ?></td>
                                    <td><?php echo htmlspecialchars($yc['NgayYeuCau']); ?></td>
                                    <td>
                                        <a href="tham-dinh.php?MaYC=<?php echo urlencode($yc['MaYC']); ?>"
                                           class="btn-primary"
                                           style="padding:6px 10px; font-size:12px;">Xem</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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
