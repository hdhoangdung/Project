<?php

declare(strict_types=1);

use UngDung\DichVu\CoSoDuLieu;
use UngDung\DichVu\XacThucDichVu;
use UngDung\HoTro\DinhDang;
use UngDung\KhoDuLieu\BaoCaoKho;

require_once __DIR__ . '/../cau-hinh.php';

$xacThuc = new XacThucDichVu();
$xacThuc->batBuocVaiTro('KeToan');
$nguoiDung = $xacThuc->nguoiDung();

$tu_ngay = isset($_GET['tu_ngay']) && strtotime($_GET['tu_ngay']) ? $_GET['tu_ngay'] : date('Y-m-01');
$den_ngay = isset($_GET['den_ngay']) && strtotime($_GET['den_ngay']) ? $_GET['den_ngay'] : date('Y-m-d');

$baoCao = new BaoCaoKho();
$csdl = CoSoDuLieu::layInstance();

$tong_thu = $baoCao->tongThu($tu_ngay, $den_ngay);
$tong_chi = $baoCao->tongChi($tu_ngay, $den_ngay);
$loi_nhuan = $tong_thu - $tong_chi;
$ty_le_loi_nhuan = $tong_thu > 0 ? ($loi_nhuan / $tong_thu) * 100 : 0;

$so_phieu_thu = (int) $csdl->layGiaTri(
    "SELECT COUNT(*) FROM PhieuThu WHERE NgayThu BETWEEN ? AND ? AND TrangThai = 'Hoạt động'",
    [$tu_ngay, $den_ngay]
);
$so_phieu_chi = (int) $csdl->layGiaTri(
    "SELECT COUNT(*) FROM PhieuChi WHERE NgayChi BETWEEN ? AND ? AND TrangThai = 'Đã chi trả'",
    [$tu_ngay, $den_ngay]
);

$rows_by_day = $baoCao->chiTietNgay($tu_ngay, $den_ngay);
$labels = array_map(static fn ($r) => date('d/m', strtotime($r['ngay'])), $rows_by_day);
$data_thu = array_map(static fn ($r) => (float) $r['thu'], $rows_by_day);
$data_chi = array_map(static fn ($r) => (float) $r['chi'], $rows_by_day);

$top_khach_hang = $baoCao->topKhachHang($tu_ngay, $den_ngay);
$top_boi_thuong = $baoCao->topBoiThuong($tu_ngay, $den_ngay);

$period_start = strtotime($tu_ngay) ?: time();
$period_end = strtotime($den_ngay) ?: $period_start;
$period_days = max(1, (int) floor(($period_end - $period_start) / 86400) + 1);

$prev_end_ts = max(0, (int) ($period_start - 86400));
$prev_start_ts = max(0, (int) ($period_start - ($period_days * 86400)));
$prev_end = date('Y-m-d', $prev_end_ts);
$prev_start = date('Y-m-d', $prev_start_ts);

$thu_prev = $baoCao->tongThu($prev_start, $prev_end);
$chi_prev = $baoCao->tongChi($prev_start, $prev_end);

$tang_giam_thu = $thu_prev > 0 ? (($tong_thu - $thu_prev) / $thu_prev) * 100 : ($tong_thu > 0 ? 100 : 0);
$tang_giam_chi = $chi_prev > 0 ? (($tong_chi - $chi_prev) / $chi_prev) * 100 : ($tong_chi > 0 ? 100 : 0);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo tổng hợp - Kế toán</title>
    <link rel="stylesheet" href="../tai-nguyen/css/style.css">
    <link rel="stylesheet" href="../tai-nguyen/css/bao-cao.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>

<body>
    <div class="app-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>⚖️ PHÂN HỆ KẾ TOÁN</h2>
                <div class="user-info"><?php echo htmlspecialchars($nguoiDung['name'] ?? ''); ?></div>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php"><i>🏠</i> Trang chủ</a></li>
                <li><a href="phieu-thu.php"><i>💰</i> Quản lý phiếu thu</a></li>
                <li><a href="phieu-chi.php"><i>💸</i> Quản lý phiếu chi</a></li>
                <li><a href="bao-cao.php" class="active"><i>📊</i> Báo cáo thu chi</a></li>
                <li><a href="../dang-xuat.php"><i>🚪</i> Đăng xuất</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Báo cáo Thu - Chi</h1>
                <div class="breadcrumb">Kế toán / Báo cáo tổng hợp</div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h2>🔍 Lọc báo cáo</h2>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="form-grid" style="grid-template-columns: 1fr 1fr auto auto;">
                            <div class="form-group">
                                <label>Từ ngày:</label>
                                <input type="date" name="tu_ngay" value="<?php echo htmlspecialchars($tu_ngay); ?>"
                                    required>
                            </div>
                            <div class="form-group">
                                <label>Đến ngày:</label>
                                <input type="date" name="den_ngay" value="<?php echo htmlspecialchars($den_ngay); ?>"
                                    required>
                            </div>
                            <div class="form-group" style="display: flex; align-items: flex-end;">
                                <button type="submit" class="btn btn-primary">🔍 Xem báo cáo</button>
                            </div>
                            <div class="form-group" style="display: flex; align-items: flex-end;">
                                <button type="button" class="btn btn-success" onclick="window.print()">🖨️ In báo
                                    cáo</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-grid" style="margin-bottom: 30px;">
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <h3>Tổng Thu</h3>
                    <div class="stat-value"><?php echo DinhDang::tien($tong_thu); ?></div>
                    <small><?php echo $so_phieu_thu; ?> phiếu thu</small>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">💸</div>
                    <h3>Tổng Chi</h3>
                    <div class="stat-value"><?php echo DinhDang::tien($tong_chi); ?></div>
                    <small><?php echo $so_phieu_chi; ?> phiếu chi</small>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📈</div>
                    <h3>Lợi nhuận</h3>
                    <div class="stat-value" style="color: <?php echo $loi_nhuan >= 0 ? '#10b981' : '#ef4444'; ?>">
                        <?php echo DinhDang::tien($loi_nhuan); ?>
                    </div>
                    <small>Tỷ lệ: <?php echo number_format($ty_le_loi_nhuan, 1); ?>%</small>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <h3>Trung bình/ngày</h3>
                    <div class="stat-value">
                        <?php echo DinhDang::tien($tong_thu / max(1, $period_days)); ?>
                    </div>
                    <small><?php echo $period_days; ?> ngày</small>
                </div>
            </div>

            <!-- So sánh với kỳ trước -->
            <div class="content-card">
                <div class="card-header">
                    <h2>📊 So sánh với kỳ trước</h2>
                </div>
                <div class="card-body">
                    <div class="comparison-box">
                        <div class="stat-card" style="flex: 1;">
                            <h4>Doanh thu</h4>
                            <div class="value"><?php echo DinhDang::tien($tong_thu); ?></div>
                            <div class="<?php echo $tang_giam_thu >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo $tang_giam_thu >= 0 ? '▲' : '▼'; ?>
                                <?php echo number_format(abs((float)$tang_giam_thu), 1); ?>%
                            </div>
                        </div>
                        <div class="stat-card" style="flex: 1;">
                            <h4>Chi phí</h4>
                            <div class="value"><?php echo DinhDang::tien($tong_chi); ?></div>
                            <div class="<?php echo $tang_giam_chi >= 0 ? 'negative' : 'positive'; ?>">
                                <?php echo $tang_giam_chi >= 0 ? '▲' : '▼'; ?>
                                <?php echo number_format(abs((float)$tang_giam_chi), 1); ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Biểu đồ thu chi theo ngày -->
            <div class="content-card">
                <div class="card-header">
                    <h2>📈 Biểu đồ thu chi theo ngày</h2>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Cơ cấu thu chi -->
            <div class="content-card">
                <div class="card-header">
                    <h2>🥧 Cơ cấu thu chi</h2>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Báo cáo chi tiết theo ngày -->
            <div class="content-card">
                <div class="card-header">
                    <h2>📋 Báo cáo chi tiết theo ngày</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Ngày</th>
                                    <th>Thu</th>
                                    <th>Chi</th>
                                    <th>Lợi nhuận</th>
                                    <th>Tỷ lệ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($rows_by_day)): ?>
                                <?php
                                    $tong_thu_ct = $tong_chi_ct = 0;
                                    foreach ($rows_by_day as $row):
                                        $thu = (float)$row['thu'];
                                        $chi = (float)$row['chi'];
                                        $ln = (float)$row['loi_nhuan_ngay'];
                                        $tong_thu_ct += $thu;
                                        $tong_chi_ct += $chi;
                                        $ty_le_ngay = $thu > 0 ? ($ln / $thu) * 100 : 0;
                                    ?>
                                <tr>
                                    <td><strong><?php echo DinhDang::ngay($row['ngay']); ?></strong></td>
                                    <td style="color:#27ae60"><strong><?php echo DinhDang::tien($thu); ?></strong></td>
                                    <td style="color:#e74c3c"><strong><?php echo DinhDang::tien($chi); ?></strong></td>
                                    <td style="color:<?php echo $ln >= 0 ? '#3498db' : '#e74c3c'; ?>">
                                        <strong><?php echo DinhDang::tien($ln); ?></strong>
                                    </td>
                                    <td><?php echo number_format($ty_le_ngay, 1); ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                                <tr style="background:#f8f9fa;font-weight:bold">
                                    <td>TỔNG CỘNG</td>
                                    <td style="color:#27ae60"><?php echo DinhDang::tien($tong_thu_ct); ?></td>
                                    <td style="color:#e74c3c"><?php echo DinhDang::tien($tong_chi_ct); ?></td>
                                    <td style="color:#3498db"><?php echo DinhDang::tien($tong_thu_ct - $tong_chi_ct); ?>
                                    </td>
                                    <td><?php echo $tong_thu_ct > 0 ? number_format((($tong_thu_ct - $tong_chi_ct)/$tong_thu_ct)*100, 1) . '%' : '0%'; ?>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align:center;color:#999">Không có dữ liệu trong khoảng
                                        thời gian này</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Top 5 khách hàng -->
            <div class="content-card">
                <div class="card-header">
                    <h2>🏆 Top 5 khách hàng đóng phí nhiều nhất</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Khách hàng</th>
                                    <th>SĐT</th>
                                    <th>Tổng đóng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($top_khach_hang)): 
                                    $i = 1;
                                    foreach ($top_khach_hang as $r): 
                                ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($r['HoTen']); ?></td>
                                    <td><?php echo htmlspecialchars($r['SDT']); ?></td>
                                    <td style="color:#27ae60">
                                        <strong><?php echo DinhDang::tien($r['tong_dong']); ?></strong>
                                    </td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="4" style="text-align:center;color:#999">Chưa có dữ liệu</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Top 5 bồi thường -->
            <div class="content-card">
                <div class="card-header">
                    <h2>💸 Top 5 yêu cầu bồi thường lớn nhất</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Khách hàng</th>
                                    <th>Nội dung</th>
                                    <th>Số tiền</th>
                                    <th>Ngày chi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($top_boi_thuong)): 
                                    $i = 1;
                                    foreach ($top_boi_thuong as $r): 
                                ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($r['HoTen']); ?></td>
                                    <td><?php echo htmlspecialchars(mb_substr($r['MoTaSuCo'], 0, 80) . '...'); ?></td>
                                    <td style="color:#e74c3c"><strong><?php echo DinhDang::tien($r['SoTien']); ?></strong>
                                    </td>
                                    <td><?php echo DinhDang::ngay($r['NgayChi']); ?></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="5" style="text-align:center;color:#999">Chưa có dữ liệu</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
    const labels = <?php echo json_encode($labels ?? []); ?>;
    const dataThu = <?php echo json_encode($data_thu ?? []); ?>;
    const dataChi = <?php echo json_encode($data_chi ?? []); ?>;

    const ctx = document.getElementById('revenueChart')?.getContext('2d');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                        label: 'Thu',
                        data: dataThu,
                        backgroundColor: 'rgba(46,204,113,0.8)'
                    },
                    {
                        label: 'Chi',
                        data: dataChi,
                        backgroundColor: 'rgba(231,76,60,0.8)'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: v => (v / 1000000).toFixed(0) + 'M'
                        }
                    }
                }
            }
        });
    }

    const ctx2 = document.getElementById('pieChart')?.getContext('2d');
    if (ctx2) {
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Thu', 'Chi', 'Lợi nhuận'],
                datasets: [{
                    data: [<?php echo $tong_thu; ?>, <?php echo $tong_chi; ?>,
                        <?php echo $loi_nhuan; ?>
                    ],
                    backgroundColor: ['rgba(46,204,113,0.85)', 'rgba(231,76,60,0.85)',
                        'rgba(52,152,219,0.85)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
    </script>
</body>

</html>