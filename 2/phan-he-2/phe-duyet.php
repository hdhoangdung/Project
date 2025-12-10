<?php
// Projects/phan-he-2/phe-duyet.php
declare(strict_types=1);

session_start();

// include config (PDO + helper functions)
require_once __DIR__ . '/config.php';

// HỖ TRỢ NHIỀU KIỂU SESSION (NGUOI_DUNG hoặc user)
$currentUser = $_SESSION['NGUOI_DUNG'] ?? $_SESSION['user'] ?? null;
if (empty($currentUser)) {
    // chưa đăng nhập -> chuyển về trang đăng nhập dự án
    header('Location: /Projects/dang-nhap.php');
    exit();
}

// chuẩn hóa thông tin người thao tác
$nguoiThucHien = '';
if (is_array($currentUser)) {
    // một số hệ thống dùng 'MaNV' | 'ma_nv' | 'username' | 'id'
    $nguoiThucHien = $currentUser['MaNV'] ?? $currentUser['ma_nv'] ?? $currentUser['username'] ?? $currentUser['id'] ?? ($currentUser['name'] ?? '');
} else {
    $nguoiThucHien = (string) $currentUser;
}

// Biến hiển thị
$message = '';
$errors = [];

// XỬ LÝ PHÊ DUYỆT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Phê duyệt
    if (isset($_POST['approve']) && !empty($_POST['MaYC'])) {
        $MaYC = trim((string)($_POST['MaYC'] ?? ''));
        if ($MaYC === '') {
            $errors[] = "Mã yêu cầu không hợp lệ.";
        } else {
            try {
                db_execute(
                    "UPDATE yeucauboithuong
                     SET TrangThai = 'Đã duyệt',
                         NgayDuyet = CURDATE(),
                         MaNVGiamDinh = ?
                     WHERE MaYC = ?",
                    [$nguoiThucHien, $MaYC]
                );

                // Ghi lịch sử nếu có bảng lichsu_yeucau
                try {
                    db_execute(
                        "INSERT INTO lichsu_yeucau(yeucau_id, hanhdong, thoigian, nguoithuchien)
                         VALUES(?, 'Phê duyệt yêu cầu', NOW(), ?)",
                        [$MaYC, $nguoiThucHien]
                    );
                } catch (Exception $ex) {
                    // không bắt buộc; chỉ log nội bộ nếu cần
                }

                $message = "Đã phê duyệt yêu cầu MaYC = {$MaYC}.";
            } catch (Exception $e) {
                $errors[] = "Lỗi khi phê duyệt: " . $e->getMessage();
            }
        }
    }

    // Từ chối
    if (isset($_POST['deny']) && !empty($_POST['MaYC'])) {
        $MaYC = trim((string)($_POST['MaYC'] ?? ''));
        $lydo = trim((string)($_POST['LyDoTuChoi'] ?? ''));
        if ($MaYC === '') {
            $errors[] = "Mã yêu cầu không hợp lệ.";
        } else {
            try {
                db_execute(
                    "UPDATE yeucauboithuong
                     SET TrangThai = 'Từ chối',
                         LyDoTuChoi = ?,
                         NgayDuyet = CURDATE(),
                         MaNVGiamDinh = ?
                     WHERE MaYC = ?",
                    [$lydo, $nguoiThucHien, $MaYC]
                );

                try {
                    db_execute(
                        "INSERT INTO lichsu_yeucau(yeucau_id, hanhdong, thoigian, nguoithuchien)
                         VALUES(?, 'Từ chối yêu cầu', NOW(), ?)",
                        [$MaYC, $nguoiThucHien]
                    );
                } catch (Exception $ex) {
                    // optional
                }

                $message = "Đã từ chối yêu cầu MaYC = {$MaYC}.";
            } catch (Exception $e) {
                $errors[] = "Lỗi khi từ chối: " . $e->getMessage();
            }
        }
    }
}

// LẤY DANH SÁCH HỒ SƠ CHỜ DUYỆT
try {
    $dsYeuCau = db_select("
        SELECT MaYC, MaHD, NgayYeuCau, NgaySuCo, SoTienDeXuat, TrangThai
        FROM yeucauboithuong
        WHERE TrangThai = 'Chờ phê duyệt'
        ORDER BY NgayTao DESC
        LIMIT 100
    ");
} catch (Exception $e) {
    $dsYeuCau = [];
    $errors[] = "Lỗi khi truy vấn danh sách: " . $e->getMessage();
}

// LẤY CHI TIẾT NẾU CÓ THAM SỐ id
$detail = null;
if (!empty($_GET['id'])) {
    $id = trim((string)$_GET['id']);
    if ($id !== '') {
        $rows = db_select("SELECT * FROM yeucauboithuong WHERE MaYC = ? LIMIT 1", [$id]);
        $detail = $rows[0] ?? null;
    }
}

// Hỗ trợ hàm hiển thị ngày và tiền
function fmtDate($d) {
    if (empty($d) || $d === '0000-00-00') return '';
    return date('d/m/Y', strtotime($d));
}
function fmtMoney($n) {
    if ($n === null || $n === '') return '';
    return number_format((float)$n, 0, '.', ',') . ' đ';
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Phê duyệt hồ sơ - Phân hệ bồi thường</title>
    <link rel="stylesheet" href="/Projects/assets/css/style.css">
    <style>
        /* Nhỏ gọn style (giữ giống layout index) */
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

        .table-wrapper { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:10px 12px; border-bottom:1px solid #eef2f7; font-size:14px; color:#111827; }
        thead th { background:#fbfdff; color:#374151; font-weight:600; text-align:left; }

        .badge { display:inline-block; padding:6px 8px; border-radius:8px; font-size:12px; color:#fff; }
        .badge-wait { background:#f59e0b; }
        .badge-ok { background:#10b981; }
        .badge-rej { background:#ef4444; }
        .muted { color:#6b7280; font-size:13px; }

        label { font-size:14px; font-weight:600; margin-bottom:6px; display:block; }
        textarea { width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px; }
        .btn { padding:10px 16px; border-radius:6px; border:none; cursor:pointer; }
        .btn-approve { background:#10b981; color:white; }
        .btn-deny { background:#ef4444; color:white; }
        .form-actions { display:flex; gap:10px; margin-top:14px; }
        .alert { padding:12px; border-radius:8px; margin-bottom:12px; }
        .alert-success { background:#ecfdf5; color:#065f46; border-left:4px solid #10b981; }
        .alert-error { background:#fef2f2; color:#991b1b; border-left:4px solid #ef4444; }
    </style>
</head>
<body>
<div class="app-wrapper">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>🚗 PHÂN HỆ BỒI THƯỜNG</h2>
            <div class="user-info"><?php echo htmlspecialchars($currentUser['name'] ?? $currentUser['username'] ?? $currentUser); ?></div>
        </div>

        <ul class="nav-menu">
            <li><a href="/Projects/phan-he-2/index.php">🏠 Trang chủ</a></li>
            <li><a href="/Projects/phan-he-2/tiep-nhan.php">📥 Tiếp nhận</a></li>
            <li><a href="/Projects/phan-he-2/tham-dinh.php">🔍 Thẩm định</a></li>
            <li><a href="/Projects/phan-he-2/phe-duyet.php" class="active">📑 Phê duyệt</a></li>
            <li><a href="/Projects/phan-he-2/tra-cuu.php">🔎 Tra cứu</a></li>
            <li><a href="/Projects/phan-he-2/bao-cao.php">📊 Báo cáo</a></li>
            <li><a href="/Projects/dang-xuat.php">🚪 Đăng xuất</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>Phê duyệt hồ sơ</h1>
            <div class="breadcrumb">Trang chủ / Phê duyệt</div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $err): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($err); ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="content-card">
            <div class="card-header"><h2>Danh sách hồ sơ chờ phê duyệt</h2></div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>MaYC</th>
                            <th>Mã HĐ</th>
                            <th>Ngày YC</th>
                            <th>Ngày sự cố</th>
                            <th>Số tiền đề xuất</th>
                            <th>Trạng thái</th>
                            <th>Xem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($dsYeuCau)): ?>
                            <?php foreach ($dsYeuCau as $r): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($r['MaYC']); ?></td>
                                    <td><?php echo htmlspecialchars($r['MaHD'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars(fmtDate($r['NgayYeuCau'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars(fmtDate($r['NgaySuCo'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars(fmtMoney($r['SoTienDeXuat'] ?? 0)); ?></td>
                                    <td>
                                        <?php
                                            $st = $r['TrangThai'] ?? '';
                                            if (stripos($st, 'Chờ') !== false) {
                                                echo '<span class="badge badge-wait">'.htmlspecialchars($st).'</span>';
                                            } elseif (stripos($st, 'Đã') !== false) {
                                                echo '<span class="badge badge-ok">'.htmlspecialchars($st).'</span>';
                                            } elseif (stripos($st, 'Từ') !== false) {
                                                echo '<span class="badge badge-rej">'.htmlspecialchars($st).'</span>';
                                            } else {
                                                echo '<span class="muted">'.htmlspecialchars($st).'</span>';
                                            }
                                        ?>
                                    </td>
                                    <td><a href="/Projects/phan-he-2/phe-duyet.php?id=<?php echo urlencode($r['MaYC']); ?>">Chi tiết</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align:center; padding:20px;" class="muted">Không có hồ sơ cần phê duyệt.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (!empty($detail)): ?>
            <div class="content-card">
                <div class="card-header"><h2>Chi tiết hồ sơ</h2></div>

                <p><strong>Mã YC:</strong> <?php echo htmlspecialchars($detail['MaYC']); ?></p>
                <p><strong>Mã hợp đồng:</strong> <?php echo htmlspecialchars($detail['MaHD']); ?></p>
                <p><strong>Ngày yêu cầu:</strong> <?php echo htmlspecialchars(fmtDate($detail['NgayYeuCau'])); ?></p>
                <p><strong>Ngày sự cố:</strong> <?php echo htmlspecialchars(fmtDate($detail['NgaySuCo'])); ?></p>
                <p><strong>Địa điểm:</strong> <?php echo htmlspecialchars($detail['DiaDiemSuCo'] ?? ''); ?></p>
                <p><strong>Mô tả tổn thất:</strong> <?php echo nl2br(htmlspecialchars($detail['MoTaSuCo'] ?? '')); ?></p>
                <p><strong>Số tiền đề xuất:</strong> <?php echo htmlspecialchars(fmtMoney($detail['SoTienDeXuat'] ?? 0)); ?></p>
                <p><strong>Số tiền đã duyệt:</strong> <?php echo htmlspecialchars(fmtMoney($detail['SoTienDuyet'] ?? 0)); ?></p>
                <p><strong>Trạng thái:</strong> <?php echo htmlspecialchars($detail['TrangThai'] ?? ''); ?></p>
                <?php if (!empty($detail['LyDoTuChoi'])): ?>
                    <p><strong>Lý do từ chối:</strong> <?php echo nl2br(htmlspecialchars($detail['LyDoTuChoi'])); ?></p>
                <?php endif; ?>

                <form method="post" style="margin-top:12px;">
                    <input type="hidden" name="MaYC" value="<?php echo htmlspecialchars($detail['MaYC']); ?>">
                    <label for="LyDoTuChoi">Lý do từ chối (nếu từ chối):</label>
                    <textarea name="LyDoTuChoi" id="LyDoTuChoi" rows="4"><?php echo htmlspecialchars(''); ?></textarea>

                    <div class="form-actions">
                        <button type="submit" name="approve" class="btn btn-approve">Phê duyệt</button>
                        <button type="submit" name="deny" class="btn btn-deny">Từ chối</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

    </main>
</div>
</body>
</html>
