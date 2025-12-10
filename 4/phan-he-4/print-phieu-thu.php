<?php
/**
 * Template in phiếu thu: layout sẵn sàng in với CSS nội tuyến
 * Lấy dữ liệu hợp đồng, khách hàng, xe, gói bảo hiểm
 */
require_once '../config.php';
requireRole('KeToan');

// Lấy mã phiếu thu
$ma_pt = safe($_GET['id'] ?? '');
if (!$ma_pt) die('Không tìm thấy mã phiếu thu!');

// Lấy thông tin bằng dbGetRow
$phieu = dbGetRow("
	SELECT pt.*, h.MaHD, h.NgayLap, h.NgayHetHan, h.PhiBaoHiem,
	       k.MaKH, k.HoTen, k.DiaChi, k.SoDienThoai, k.Email, k.CCCD,
	       x.BienSo, x.HangXe, x.DongXe, x.NamSanXuat, x.MauSac,
	       g.TenGoi, g.MoTa as MoTaGoi
	FROM phieuthu pt
	JOIN hopdong h ON pt.MaHD = h.MaHD
	JOIN khachhang k ON h.MaKH = k.MaKH
	JOIN xeoto x ON h.MaXe = x.MaXe
	JOIN goibaohiem g ON h.MaGoi = g.MaGoi
	WHERE pt.MaPT = ?
", [$ma_pt]) ?: die('Không tìm thấy phiếu thu!');
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu thu - <?php echo $phieu['MaPT']; ?></title>
    <style>
    /* Kiểu in */
    @media print {
        .no-print {
            display: none;
        }

        @page {
            margin: 10mm;
        }
    }

    body {
        font-family: 'Times New Roman', Times, serif;
        font-size: 14px;
        line-height: 1.6;
        margin: 0;
        padding: 20px;
        background: #f5f5f5;
    }

    .receipt-container {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .header {
        text-align: center;
        border-bottom: 3px double #000;
        padding-bottom: 20px;
        margin-bottom: 30px;
    }

    .company-name {
        font-size: 18px;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .company-info {
        font-size: 13px;
        color: #555;
        margin-bottom: 20px;
    }

    .receipt-title {
        font-size: 24px;
        font-weight: bold;
        text-transform: uppercase;
        margin: 20px 0;
        color: #2c3e50;
    }

    .receipt-code {
        font-size: 16px;
        color: #e74c3c;
        margin-bottom: 10px;
    }

    .info-section {
        margin: 25px 0;
    }

    .info-row {
        display: flex;
        margin-bottom: 12px;
        line-height: 1.8;
    }

    .info-label {
        font-weight: bold;
        min-width: 180px;
        color: #2c3e50;
    }

    .info-value {
        flex: 1;
        border-bottom: 1px dotted #ccc;
        padding-bottom: 2px;
    }

    .amount-section {
        background: #f8f9fa;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        margin: 25px 0;
    }

    .amount-number {
        font-size: 22px;
        font-weight: bold;
        color: #27ae60;
        text-align: right;
        margin-bottom: 10px;
    }

    .amount-text {
        font-style: italic;
        text-align: right;
        color: #555;
        padding-top: 10px;
        border-top: 1px solid #dee2e6;
    }

    .note-section {
        margin: 25px 0;
        padding: 15px;
        background: #fffbea;
        border-left: 4px solid #f39c12;
    }

    .signature-section {
        display: flex;
        justify-content: space-between;
        margin-top: 50px;
        text-align: center;
    }

    .signature-box {
        width: 45%;
    }

    .signature-title {
        font-weight: bold;
        margin-bottom: 10px;
        text-transform: uppercase;
    }

    .signature-note {
        font-size: 12px;
        font-style: italic;
        color: #777;
        margin-bottom: 60px;
    }

    .signature-name {
        font-weight: bold;
        margin-top: 10px;
    }

    .footer {
        margin-top: 40px;
        padding-top: 20px;
        border-top: 1px solid #ddd;
        text-align: center;
        font-size: 12px;
        color: #777;
    }

    .print-btn {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 24px;
        background: #3498db;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .print-btn:hover {
        background: #2980b9;
    }

    .divider {
        height: 2px;
        background: linear-gradient(to right, #3498db, #e74c3c);
        margin: 30px 0;
    }
    </style>
</head>

<body>
    <!-- Nút in (không in khi in trang) -->
    <button class="print-btn no-print" onclick="window.print()">🖨️ In phiếu</button>

    <div class="receipt-container">
        <div class="header">
            <div class="company-name">CÔNG TY BẢO HIỂM XE MÁY ABC</div>
            <div class="company-info">
                Địa chỉ: Số 123, Đường ABC, Quận XYZ, Hà Nội<br>
                Điện thoại: 024-1234-5678 | Email: contact@baohiemxe.vn<br>
                MST: 0123456789
            </div>

            <div class="receipt-title">PHIẾU THU</div>
            <div class="receipt-code">Số: <?php echo $phieu['MaPT']; ?></div>
            <div>Ngày thu: <?php echo dateVN($phieu['NgayThu']); ?></div>
        </div>

        <div class="info-section">
            <div class="info-row">
                <div class="info-label">Họ và tên khách hàng:</div>
                <div class="info-value"><?php echo $phieu['HoTen']; ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Số CCCD:</div>
                <div class="info-value"><?php echo $phieu['CCCD']; ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Địa chỉ:</div>
                <div class="info-value"><?php echo $phieu['DiaChi']; ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Số điện thoại:</div>
                <div class="info-value"><?php echo $phieu['SoDienThoai']; ?></div>
            </div>
        </div>

        <div class="divider"></div>

        <div class="info-section">
            <div class="info-row">
                <div class="info-label">Số hợp đồng:</div>
                <div class="info-value"><?php echo $phieu['MaHD']; ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Biển số xe:</div>
                <div class="info-value"><?php echo $phieu['BienSo']; ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Xe:</div>
                <div class="info-value">
                    <?php echo $phieu['HangXe'] . ' ' . $phieu['DongXe'] . ' (' . $phieu['NamSanXuat'] . ')'; ?>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Gói bảo hiểm:</div>
                <div class="info-value"><?php echo $phieu['TenGoi']; ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Thời hạn:</div>
                <div class="info-value">
                    Từ <?php echo dateVN($phieu['NgayLap']); ?> đến <?php echo dateVN($phieu['NgayHetHan']); ?>
                </div>
            </div>
        </div>

        <div class="amount-section">
            <div style="font-size: 16px; font-weight: bold; margin-bottom: 15px;">
                Nội dung thu: PHÍ BẢO HIỂM XE MÁY
            </div>

            <div class="amount-number">
                Số tiền: <?php echo vnd($phieu['SoTien']); ?>
            </div>

            <div class="amount-text">
                Bằng chữ: <strong><?php echo so_chu($phieu['SoTien']); ?></strong>
            </div>
        </div>

        <?php if ($phieu['GhiChu']): ?>
        <div class="note-section">
            <strong>📝 Ghi chú:</strong> <?php echo $phieu['GhiChu']; ?>
        </div>
        <?php endif; ?>

        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-title">Người nộp tiền</div>
                <div class="signature-note">(Ký, ghi rõ họ tên)</div>
                <div class="signature-name"><?php echo $phieu['HoTen']; ?></div>
            </div>

            <div class="signature-box">
                <div class="signature-title">Người thu tiền</div>
                <div class="signature-note">(Ký, ghi rõ họ tên)</div>
                <div class="signature-name"><?php echo $phieu['NguoiThucHien'] ?? 'Kế toán'; ?></div>
            </div>
        </div>

        <div class="footer">
            <strong>⚠️ Lưu ý:</strong> Vui lòng giữ phiếu này làm chứng từ thanh toán.<br>
            Phiếu thu có giá trị trong suốt thời hạn hợp đồng.<br>
            <em>Phiếu in lúc: <?php echo date('H:i:s d/m/Y'); ?></em>
        </div>
    </div>

    <script>
    // Tự động in khi load trang (tùy chọn)
    // window.onload = function() { window.print(); }
    </script>
</body>

</html>