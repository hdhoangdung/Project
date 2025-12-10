<?php
// tiep-nhan.php
// Tiếp nhận yêu cầu bồi thường (Form + xử lý)
// Đặt file này ở C:\xampp\htdocs\PROJECTS\phan-he-2\tiep-nhan.php

// ----- CẤU HÌNH ----- //
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = ''; // đổi theo cấu hình XAMPP của bạn
$dbName = 'qlbh_xe';
$uploadBaseDir = __DIR__ . '/uploads/yeucau'; // folder lưu file
$webUploadBase = '/Projects/phan-he-2/uploads/yeucau'; // đường dẫn public (tùy server config)

// Tạo thư mục upload nếu chưa có
if (!is_dir($uploadBaseDir)) {
    mkdir($uploadBaseDir, 0777, true);
}

// ----- HỖ TRỢ HÀM ----- //
function connect_db() {
    global $dbHost, $dbUser, $dbPass, $dbName;
    $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($mysqli->connect_errno) {
        throw new Exception('Database connection error: ' . $mysqli->connect_error);
    }
    // set charset
    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}

// sinh MaYC: YC + YYYYMMDD + 4-digit; loop nếu trùng
function generateMaYC($mysqli) {
    $dateStr = date('Ymd');
    for ($i = 0; $i < 10; $i++) {
        $seq = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $ma = 'YC' . $dateStr . $seq;
        // kiểm tra tồn tại
        $stmt = $mysqli->prepare("SELECT 1 FROM yeucauboithuong WHERE MaYC = ? LIMIT 1");
        $stmt->bind_param('s', $ma);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        if (!$exists) return $ma;
    }
    // fallback: nếu không tìm được (rất hiếm) dùng uniqid
    return 'YC' . $dateStr . uniqid();
}

// sinh MaHinhAnh
function generateMaHinhAnh() {
    return 'HA' . time() . rand(100,999);
}

// Xóa các file đã upload (nếu rollback)
function cleanupFiles($filesPaths) {
    foreach ($filesPaths as $p) {
        if (file_exists($p)) {
            @unlink($p);
        }
    }
}

// ----- XỬ LÝ FORM KHI POST ----- //
$errors = [];
$success = null;
$resultData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // nhận dữ liệu
    $MaHD = isset($_POST['MaHD']) ? trim($_POST['MaHD']) : '';
    $NgaySuCo = isset($_POST['NgaySuCo']) ? trim($_POST['NgaySuCo']) : '';
    $DiaDiemSuCo = isset($_POST['DiaDiemSuCo']) ? trim($_POST['DiaDiemSuCo']) : '';
    $MoTaSuCo = isset($_POST['MoTaSuCo']) ? trim($_POST['MoTaSuCo']) : '';
    $SoTienDeXuat = isset($_POST['SoTienDeXuat']) ? trim($_POST['SoTienDeXuat']) : '';
    $MaNV = isset($_POST['MaNV']) ? trim($_POST['MaNV']) : null;

    // validate cơ bản
    if ($MaHD === '') $errors[] = 'Vui lòng nhập MaHD (Mã hợp đồng).';
    if ($NgaySuCo === '' || !DateTime::createFromFormat('Y-m-d', $NgaySuCo)) $errors[] = 'Ngày xảy ra sự cố không hợp lệ (định dạng YYYY-MM-DD).';
    if ($DiaDiemSuCo === '') $errors[] = 'Vui lòng nhập địa điểm sự cố.';
    if ($MoTaSuCo === '') $errors[] = 'Vui lòng mô tả sự cố.';
    if ($SoTienDeXuat === '' || !is_numeric($SoTienDeXuat) || floatval($SoTienDeXuat) < 0) $errors[] = 'Số tiền đề xuất không hợp lệ.';

    // xử lý tiếp khi không có lỗi form
    if (empty($errors)) {
        $mysqli = null;
        $uploadedFilePaths = []; // lưu để cleanup nếu rollback
        try {
            $mysqli = connect_db();
            // bật autocommit false (transaction)
            $mysqli->autocommit(false);

            // 1) kiểm tra hợp đồng có tồn tại không
$stmt = $mysqli->prepare("SELECT MaHD FROM hopdong WHERE MaHD = ? LIMIT 1");
$stmt->bind_param('s', $MaHD);
$stmt->execute();
$res = $stmt->get_result();
$hopdong = $res->fetch_assoc();
$stmt->close();

// Nếu MaHD đã tồn tại → không cho dùng
if ($hopdong) {
    throw new Exception('Mã hợp đồng đã được sử dụng. Vui lòng nhập mã mới.');
}

// Nếu MaHD chưa tồn tại → tự tạo hợp đồng mới
$sqlNew = "INSERT INTO hopdong (MaHD, NgayLap, NgayHetHan, PhiBaoHiem, TrangThai)
           VALUES (?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 0, 'Chưa thanh toán')";
$stmtNew = $mysqli->prepare($sqlNew);
$stmtNew->bind_param("s", $MaHD);
if (!$stmtNew->execute()) {
    throw new Exception('Không thể tạo hợp đồng mới: ' . $mysqli->error);
}
$stmtNew->close();

// Gán ngày hết hạn để dùng ở bước sau
$hopdong = [
    'MaHD' => $MaHD,
    'NgayHetHan' => date('Y-m-d', strtotime("+1 year"))
];


            // so sánh ngày
            $ngaySuCoDT = new DateTime($NgaySuCo);
            // HopDong.NgayHetHan có kiểu DATE trong DB; fetch_assoc trả về string
            $ngayHetHanDT = new DateTime($hopdong['NgayHetHan']);
            if ($ngaySuCoDT > $ngayHetHanDT) {
                throw new Exception('Hợp đồng đã hết hiệu lực vào thời điểm xảy ra sự cố.');
            }

            // 2) sinh MaYC (kiểm tra tồn tại để tránh trùng)
            $MaYC = generateMaYC($mysqli);

            // 3) Insert vào yeucauboithuong
            $ngayYeuCau = date('Y-m-d');
            $trangThai = 'Chờ thẩm định';
            $insertSql = "INSERT INTO yeucauboithuong
                (MaYC, MaHD, NgayYeuCau, NgaySuCo, DiaDiemSuCo, MoTaSuCo, SoTienDeXuat, TrangThai, NgayTao)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $mysqli->prepare($insertSql);
            if (!$stmt) throw new Exception('Prepare insert yeucauboithuong thất bại: ' . $mysqli->error);
            $stmt->bind_param('sssssdss',
                $MaYC, $MaHD, $ngayYeuCau, $NgaySuCo, $DiaDiemSuCo, $MoTaSuCo, $SoTienDeXuat, $trangThai
            );
            if (!$stmt->execute()) {
                $stmt->close();
                throw new Exception('Lỗi khi insert yeucauboithuong: ' . $mysqli->error);
            }
            $stmt->close();

            // 4) Lưu file (nếu có) và insert vào ghinhanhsuco
            if (!empty($_FILES['files']) && is_array($_FILES['files']['name'])) {
                // tạo folder riêng cho MaYC
                $targetDir = rtrim($uploadBaseDir, '/') . '/' . $MaYC;
                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

                $fileCount = count($_FILES['files']['name']);
                $insertImgSql = "INSERT INTO ghinhanhsuco (MaHinhAnh, MaYC, TenFile, DuongDan, NgayTaiLen, MoTa)
                                VALUES (?, ?, ?, ?, NOW(), ?)";
                $stmtImg = $mysqli->prepare($insertImgSql);
                if (!$stmtImg) throw new Exception('Prepare insert ghinhanhsuco thất bại: ' . $mysqli->error);

                for ($i = 0; $i < $fileCount; $i++) {
                    $err = $_FILES['files']['error'][$i];
                    if ($err !== UPLOAD_ERR_OK) continue; // bỏ file lỗi (hoặc bạn có thể ném exception)
                    $tmpName = $_FILES['files']['tmp_name'][$i];
                    $origName = basename($_FILES['files']['name'][$i]);
                    // giới hạn kích thước file (5MB)
                    if ($_FILES['files']['size'][$i] > 5 * 1024 * 1024) {
                        throw new Exception("File quá lớn: $origName (max 5MB).");
                    }
                    // kiểm tra extension an toàn
                    $allowedExt = ['jpg','jpeg','png','pdf'];
                    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                    if (!in_array($ext, $allowedExt)) {
                        throw new Exception("Định dạng file không được chấp nhận: $origName");
                    }

                    $newName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    $destPath = $targetDir . '/' . $newName;
                    if (!move_uploaded_file($tmpName, $destPath)) {
                        throw new Exception("Không thể lưu file: $origName");
                    }
                    $uploadedFilePaths[] = $destPath;

                    // DuongDan lưu đường dẫn tương đối web (tùy cấu hình). Ta lưu dạng /PROJECTS/phan-he-2/uploads/yeucau/{MaYC}/{file}
                    $duongDan = $webUploadBase . '/' . $MaYC . '/' . $newName;
                    $MaHinhAnh = generateMaHinhAnh();
                    $moTaHinh = null;

                    $stmtImg->bind_param('sssss', $MaHinhAnh, $MaYC, $origName, $duongDan, $moTaHinh);
                    if (!$stmtImg->execute()) {
                        $stmtImg->close();
                        throw new Exception('Lỗi khi insert ghinhanhsuco: ' . $mysqli->error);
                    }
                }
                $stmtImg->close();
            }

            // 5) Ghi log vào lichsuthaydoi
            $insertLogSql = "INSERT INTO lichsuthaydoi (BangDuLieu, MaBanGhi, HanhDong, DuLieuCu, DuLieuMoi, MaNV, ThoiGian)
                             VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmtLog = $mysqli->prepare($insertLogSql);
            if (!$stmtLog) throw new Exception('Prepare insert lichsuthaydoi thất bại: ' . $mysqli->error);
            $bang = 'YeuCauBoiThuong';
            $maBanGhi = $MaYC;
            $hanhDong = 'INSERT';
            $duLieuCu = null;
            $duLieuMoiArr = [
                'MaYC' => $MaYC,
                'MaHD' => $MaHD,
                'NgayYeuCau' => $ngayYeuCau,
                'NgaySuCo' => $NgaySuCo,
                'SoTienDeXuat' => floatval($SoTienDeXuat)
            ];
            $duLieuMoi = json_encode($duLieuMoiArr, JSON_UNESCAPED_UNICODE);
            $stmtLog->bind_param('ssssss', $bang, $maBanGhi, $hanhDong, $duLieuCu, $duLieuMoi, $MaNV);
            if (!$stmtLog->execute()) {
                $stmtLog->close();
                throw new Exception('Lỗi khi insert lichsuthaydoi: ' . $mysqli->error);
            }
            $stmtLog->close();

            // commit
            if (!$mysqli->commit()) {
                throw new Exception('Commit thất bại: ' . $mysqli->error);
            }

            $success = 'Tiếp nhận yêu cầu thành công.';
            $resultData = [
                'MaYC' => $MaYC,
                'MaHD' => $MaHD,
                'TrangThai' => $trangThai
            ];

        } catch (Exception $ex) {
            // rollback và cleanup file đã upload
            if ($mysqli) {
                $mysqli->rollback();
            }
            if (!empty($uploadedFilePaths)) cleanupFiles($uploadedFilePaths);
            $errors[] = 'Lỗi: ' . $ex->getMessage();
        } finally {
            
            if (isset($mysqli) && $mysqli) $mysqli->close();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tiếp nhận yêu cầu bồi thường</title>
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

        .content-card { background:#fff; border-radius:12px; padding:20px; box-shadow:0 6px 18px rgba(15,23,42,0.06); }
        .form-group { margin-bottom:16px; }
        label { font-weight:600; font-size:14px; display:block; margin-bottom:6px; color:#374151; }
        input, textarea { width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; }
        textarea { min-height:100px; }
        .btn-submit { background:#2563eb; color:#fff; border:none; padding:12px 18px; border-radius:8px; cursor:pointer; font-size:15px; }
        .btn-submit:hover { background:#1d4ed8; }

        .alert { padding:12px 16px; border-radius:8px; margin-bottom:16px; }
        .alert-error { background:#fee2e2; color:#b91c1c; }
        .alert-success { background:#dcfce7; color:#166534; }
        
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
                <?php echo htmlspecialchars($user['name'] ?? 'User'); ?>
            </div>
        </div>

        <ul class="nav-menu">
            <li><a href="index.php">🏠 Trang chủ</a></li>
            <li><a href="tiep-nhan.php" class="active">📥 Tiếp nhận</a></li>
            <li><a href="tham-dinh.php">🔍 Thẩm định</a></li>
            <li><a href="phe-duyet.php">📑 Phê duyệt</a></li>
            <li><a href="tra-cuu.php">🔎 Tra cứu</a></li>
            <li><a href="bao-cao.php">📊 Báo cáo</a></li>
            <li><a href="../dang-xuat.php">🚪 Đăng xuất</a></li>
        </ul>
    </aside>

    <!-- Main -->
    <main class="main-content">

        <div class="page-header">
            <h1>Tiếp nhận yêu cầu bồi thường</h1>
            <div class="breadcrumb">Trang chủ / Tiếp nhận yêu cầu</div>
        </div>

        <div class="content-card">

            <!-- Alert lỗi -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <strong>Lỗi:</strong>
                    <ul>
                        <?php foreach ($errors as $e): ?>
                            <li><?php echo htmlspecialchars($e); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Alert thành công -->
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <?php if ($resultData): ?>
                        <div class="muted">Mã yêu cầu: <strong><?php echo $resultData['MaYC']; ?></strong></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>


            <!-- FORM TIẾP NHẬN -->
            <form action="" method="post" enctype="multipart/form-data">

                <div class="form-group">
                    <label>Mã hợp đồng (MaHD)</label>
                    <input type="text" name="MaHD" required>
                </div>

                <div class="form-group">
                    <label>Ngày xảy ra sự cố</label>
                    <input type="date" name="NgaySuCo" required>
                </div>

                <div class="form-group">
                    <label>Địa điểm sự cố</label>
                    <input type="text" name="DiaDiemSuCo" required>
                </div>

                <div class="form-group">
                    <label>Mô tả sự cố</label>
                    <textarea name="MoTaSuCo" required></textarea>
                </div>

                <div class="form-group">
                    <label>Số tiền đề xuất (VNĐ)</label>
                    <input type="number" name="SoTienDeXuat" min="0" required>
                </div>

                <div class="form-group">
                    <label>Ảnh minh chứng (tối đa 5MB mỗi file)</label>
                    <input type="file" name="files[]" multiple accept=".jpg,.jpeg,.png,.pdf">
                    <div class="muted">Định dạng cho phép: JPG, PNG, PDF</div>
                </div>

                <div class="form-group">
                    <label>Mã nhân viên (tùy chọn)</label>
                    <input type="text" name="MaNV">
                </div>

                <button type="submit" class="btn-submit">Gửi yêu cầu</button>
            </form>

        </div>

    </main>
</div>

</body>
</html>
