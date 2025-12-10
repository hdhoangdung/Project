<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\PhieuThu;
use App\Models\PhieuChi;
use App\Models\Hopdong;
use App\Models\PheDuyet;

class AccountingController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function receipt()
    {
        $this->requireRole('Accounting Staff');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maHD = $_POST['MaHD'] ?? null;
            $soTien = $_POST['SoTien'] ?? 0.00;
            if (!$maHD) { echo 'Missing MaHD'; exit; }
            $hopdong = new Hopdong($this->db);
            // check if contract exists
            $hd = $hopdong->find($maHD);
            if (!$hd) { echo 'Contract not found'; exit; }
            // check if already paid in full
            $paid = $hopdong->totalPaid($maHD);
            if (($paid + $soTien) >= (float)$hd['SoTien']) { echo 'Contract already paid or this payment would overpay'; exit; }

            $pt = new PhieuThu($this->db);
            $data = [
                'MaPT' => $_POST['MaPT'] ?? uniqid('PT'),
                'MaHD' => $maHD,
                'SoTien' => $soTien,
                'NgayThu' => $_POST['NgayThu'] ?? date('Y-m-d'),
                'TrangThai' => 1,
            ];
            $pt->create($data);
            $this->db->logAction($_SESSION['user'] ?? 'system', 'create_receipt', null, $data, $_SERVER['REMOTE_ADDR'] ?? null);
            header('Location: index.php?c=Accounting&m=receipt'); exit;
        }

        $path = __DIR__ . '/../../../../4/ke-toan/phieu-thu.php';
        if (file_exists($path)) { include $path; return; }
        include __DIR__ . '/../Views/Accounting/receipt.php';
    }

    public function payout()
    {
        $this->requireRole('Accounting Staff');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maPD = $_POST['MaPD'] ?? null;
            $soTien = $_POST['SoTien'] ?? 0.00;
            if (!$maPD) { echo 'Missing MaPD'; exit; }
            $pd = new PheDuyet($this->db);
            $ap = $pd->find($maPD);
            if (!$ap || $ap['TrangThai'] != 1) { echo 'Claim not approved'; exit; }

            $pc = new PhieuChi($this->db);
            $data = [
                'MaPC' => $_POST['MaPC'] ?? uniqid('PC'),
                'MaPD' => $maPD,
                'SoTien' => $soTien,
                'NgayChi' => $_POST['NgayChi'] ?? date('Y-m-d'),
                'TrangThai' => 1,
            ];
            $pc->create($data);
            // update claim/approval status if needed
            $this->db->logAction($_SESSION['user'] ?? 'system', 'create_payout', null, $data, $_SERVER['REMOTE_ADDR'] ?? null);
            header('Location: index.php?c=Accounting&m=payout'); exit;
        }

        $path = __DIR__ . '/../../../../4/ke-toan/phieu-chi.php';
        if (file_exists($path)) { include $path; return; }
        include __DIR__ . '/../Views/Accounting/payout.php';
    }

    public function report()
    {
        $this->requireRole('Accounting Staff');
        $type = $_GET['type'] ?? 'month'; // month|quarter|year
        $year = intval($_GET['year'] ?? date('Y'));
        $month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('n'));

        $pt = new PhieuThu($this->db);
        $pc = new PhieuChi($this->db);

        $receipts = $pt->sumByPeriod($type, $year, $month);
        $payouts = $pc->sumByPeriod($type, $year, $month);

        $data = [
            'type' => $type,
            'year' => $year,
            'month' => $month,
            'receipts' => $receipts,
            'payouts' => $payouts,
            'net' => $receipts - $payouts,
        ];

        $this->db->logAction($_SESSION['user'] ?? 'system', 'generate_report', null, $data, $_SERVER['REMOTE_ADDR'] ?? null);

        include __DIR__ . '/../Views/Accounting/report.php';
    }
}
