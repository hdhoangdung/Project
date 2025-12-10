<?php

declare(strict_types=1);

use UngDung\DichVu\XacThucDichVu;
use UngDung\HoTro\DinhDang;
use UngDung\KhoDuLieu\BaoCaoKho;

require_once __DIR__ . '/../cau-hinh.php';

$xacThuc = new XacThucDichVu();
$xacThuc->batBuocVaiTro('KeToan');
$nguoiDung = $xacThuc->nguoiDung();

$baoCao = new BaoCaoKho();
$tongThu = $baoCao->tongThu();
$tongChi = $baoCao->tongChi();
$loiNhuan = $tongThu - $tongChi;
$giaoDichThang = $baoCao->giaoDichThangNay();
$giaoDichGanDay = $baoCao->giaoDichGanDay();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Phân hệ Kế toán</title>
    <link rel="stylesheet" href="../tai-nguyen/css/style.css">
</head>

<body>
    <div class="app-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>⚖️ PHÂN HỆ KẾ TOÁN</h2>
                <div class="user-info"><?php echo htmlspecialchars($nguoiDung['name'] ?? 'User'); ?></div>
            </div>

            <ul class="nav-menu">
                <li><a href="index.php" class="active"><i>🏠</i> Trang chủ</a></li>
                <li><a href="phieu-thu.php"><i>💰</i> Quản lý phiếu thu</a></li>
                <li><a href="phieu-chi.php"><i>💸</i> Quản lý phiếu chi</a></li>
                <li><a href="bao-cao.php"><i>📊</i> Báo cáo thu chi</a></li>
                <li><a href="../dang-xuat.php"><i>🚪</i> Đăng xuất</a></li>
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
                    <div class="value"><?php echo DinhDang::tien($tongThu); ?></div>
                    <div class="subtitle">Phí bảo hiểm đã thu</div>
                </div>

                <div class="stat-card red">
                    <span class="icon">💸</span>
                    <h3>Tổng Chi</h3>
                    <div class="value"><?php echo DinhDang::tien($tongChi); ?></div>
                    <div class="subtitle">Bồi thường đã chi trả</div>
                </div>

                <div class="stat-card blue">
                    <span class="icon">📈</span>
                    <h3>Lợi nhuận</h3>
                    <div class="value"><?php echo DinhDang::tien($loiNhuan); ?></div>
                    <div class="subtitle">
                        <?php 
                        $ty_le = $tongThu > 0 ? ($loiNhuan / $tongThu) * 100 : 0;
                        echo number_format($ty_le, 1) . '%'; 
                        ?> tỷ suất
                    </div>
                </div>

                <div class="stat-card orange">
                    <span class="icon">📝</span>
                    <h3>Giao dịch tháng này</h3>
                    <div class="value"><?php echo $giaoDichThang; ?></div>
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
                                <?php if (!empty($giaoDichGanDay)): ?>
                                <?php foreach ($giaoDichGanDay as $row): ?>
                                <tr>
                                    <td><strong>GD-<?php echo str_pad($row['MaGD'], 4, '0', STR_PAD_LEFT); ?></strong>
                                    </td>
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
                                        <strong
                                            style="color: <?php echo $row['LoaiGD'] == 'Thu' ? '#10b981' : '#ef4444'; ?>">
                                            <?php echo DinhDang::tien((float) $row['SoTien']); ?>
                                        </strong>
                                    </td>
                                    <td><?php echo DinhDang::ngay($row['NgayGD'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars(substr($row['GhiChu'] ?? '', 0, 40) . (strlen($row['GhiChu'] ?? '') > 40 ? '...' : '')); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
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