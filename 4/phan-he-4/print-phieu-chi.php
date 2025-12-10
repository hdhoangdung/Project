<?php
/**
 * Template in phiếu chi: layout sẵn sàng in với CSS nội tuyến
 * Hiển thị thông tin bồi thường hoặc chi phí
 */
require_once '../config.php';
requireRole('KeToan');

$ma_pc = safe($_GET['id'] ?? '');
if (!$ma_pc) die('Không tìm thấy mã phiếu chi!');

// Lấy thông tin bằng dbGetRow
$phieu = dbGetRow("
    SELECT pc.*, yc.MaYC, yc.MoTaSuCo, yc.NgaySuCo, yc.DiaDiemSuCo,
           k.HoTen, k.DiaChi, k.SoDienThoai, k.CCCD,
           x.BienSo, x.HangXe, x.DongXe,
           n.HoTen as TenNV
    FROM phieuchi pc
    LEFT JOIN yeucauboithuong yc ON pc.MaYC = yc.MaYC
    LEFT JOIN hopdong h ON yc.MaHD = h.MaHD
    LEFT JOIN khachhang k ON h.MaKH = k.MaKH
    LEFT JOIN xeoto x ON h.MaXe = x.MaXe
    LEFT JOIN nhanvien n ON pc.MaNV = n.MaNV
    WHERE pc.MaPC = '{$ma_pc}'
") ?: die('Không tìm thấy phiếu chi!');
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Phiếu chi - <?php echo $phieu['MaPC']; ?></title>
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
        color: #e74c3c;
    }

    .receipt-code {
        font-size: 16px;
        color: #c0392b;
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
        background: #fff5f5;
        border: 2px solid #fee;
        border-radius: 8px;
        padding: 20px;
        margin: 25px 0;
    }

    .amount-number {
        font-size: 22px;
        font-weight: bold;
        color: #e74c3c;
        text-align: right;
        margin-bottom: 10px;
    }

    .amount-text {
        font-style: italic;
        text-align: right;
        color: #555;
        padding-top: 10px;
        border-top: 1px solid #fee;
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
        background: #e74c3c;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .print-btn:hover {
        background: #c0392b;
    }

    .divider {
        height: 2px;
        background: linear-gradient(to right, #e74c3c, #3498db);
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

            <div class="receipt-title">PHIẾU CHI</div>
            <div class="receipt-code">Số: <?php echo $phieu['MaPC']; ?></div>
            <div>Ngày chi: <?php echo dateVN($phieu['NgayChi']); ?></div>
        </div>

        <?php if ($phieu['MaYC']): ?>
        <!-- Chi bồi thường -->
        <div class="info-section">
            <div
                style="background: #fee2e2; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                <strong>🔴 CHI BỒI THƯỜNG THEO YÊU CẦU: <?php echo $phieu['MaYC']; ?></strong>
            </div>

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
                <div class="info-label">Biển số xe:</div>
                <div class="info-value"><?php echo $phieu['BienSo']; ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Xe:</div>
                <div class="info-value"><?php echo $phieu['HangXe'] . ' ' . $phieu['DongXe']; ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Ngày sự cố:</div>
                <div class="info-value"><?php echo dateVN($phieu['NgaySuCo']); ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Địa điểm sự cố:</div>
                <div class="info-value"><?php echo $phieu['DiaDiemSuCo']; ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Mô tả sự cố:</div>
                <div class="info-value"><?php echo $phieu['MoTaSuCo']; ?></div>
            </div>
        </div>
        <?php else: ?>
        <!-- Chi phí khác -->
        <div class="info-section">
            <div
                style="background: #fef3c7; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                <strong>⚠️ CHI PHÍ HOẠT ĐỘNG</strong>
            </div>

            <div class="info-row">
                <div class="info-label">Nội dung chi:</div>
                <div class="info-value"><?php echo $phieu['GhiChu'] ?: 'Chi phí khác'; ?></div>
            </div>
        </div>
        <?php endif; ?>

        <div class="amount-section">
            <div style="font-size: 16px; font-weight: bold; margin-bottom: 15px;">
                Lý do chi: <?php echo $phieu['MaYC'] ? 'BỒI THƯỜNG SỰ CỐ' : 'CHI PHÍ HOẠT ĐỘNG'; ?>
            </div>

            <div class="amount-number">
                Số tiền: <?php echo vnd($phieu['SoTien']); ?>
            </div>

            <div class="amount-text">
                Bằng chữ: <strong><?php echo so_chu($phieu['SoTien']); ?></strong>
            </div>
        </div>

        <?php if ($phieu['GhiChu'] && $phieu['MaYC']): ?>
        <div class="note-section">
            <strong>📝 Ghi chú:</strong> <?php echo $phieu['GhiChu']; ?>
        </div>
        <?php endif; ?>

        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-title">Người nhận tiền</div>
                <div class="signature-note">(Ký, ghi rõ họ tên)</div>
                <div class="signature-name"><?php echo $phieu['HoTen'] ?: '...........................'; ?></div>
            </div>

            <div class="signature-box">
                <div class="signature-title">Người chi tiền</div>
                <div class="signature-note">(Ký, ghi rõ họ tên)</div>
                <div class="signature-name"><?php echo $phieu['TenNV'] ?? 'Kế toán'; ?></div>
            </div>
        </div>

        <div class="footer">
            <strong>⚠️ Lưu ý:</strong> Vui lòng giữ phiếu này làm chứng từ chi trả.<br>
            <em>Phiếu in lúc: <?php echo date('H:i:s d/m/Y'); ?></em>
        </div>
    </div>
</body>

</html>