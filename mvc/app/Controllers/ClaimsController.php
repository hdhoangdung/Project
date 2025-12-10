<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\YeuCau;
use App\Models\ThamDinh;
use App\Models\PheDuyet;
use App\Models\Hopdong;
use App\Models\PhieuChi;

class ClaimsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function submit()
    {
        $this->requireRole('Claims Staff');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // create YeuCau (claim request)
            $data = [
                'MaYC' => $_POST['MaYC'] ?? uniqid('YC'),
                'MaHD' => $_POST['MaHD'] ?? null,
                'MaKH' => $_POST['MaKH'] ?? null,
                'MaXe' => $_POST['MaXe'] ?? null,
                'NoiDung' => $_POST['NoiDung'] ?? '',
                'TrangThai' => 1,
            ];
            // validation
            if (empty($data['MaHD']) || empty($data['MaKH']) || empty($data['MaXe'])) { echo 'Missing required fields'; exit; }

            $hopdong = new Hopdong($this->db);
            if ($hopdong->isExpired($data['MaHD'])) { echo 'Cannot create claim: contract expired'; exit; }

            $yc = new YeuCau($this->db);
            $yc->create($data);
            $this->db->logAction($_SESSION['user'] ?? 'system', 'submit_claim', null, $data, $_SERVER['REMOTE_ADDR'] ?? null);
            header('Location: index.php?c=Claims&m=detail&MaYC=' . urlencode($data['MaYC'])); exit;
        }

        $path = __DIR__ . '/../../../../2/phan-he-2/tiep-nhan.php';
        if (file_exists($path)) { include $path; return; }
        include __DIR__ . '/../Views/Claims/submit.php';
    }

    public function detail()
    {
        $this->requireRole('Claims Staff');
        $maYC = $_GET['MaYC'] ?? null;
        if (!$maYC) { echo 'Missing MaYC'; exit; }

        $yc = new YeuCau($this->db);
        $item = $yc->find($maYC);
        if (!$item) { echo 'Claim not found'; exit; }

        // include original UI when available
        $path = __DIR__ . '/../../../../2/phan-he-2/tra-cuu.php';
        if (file_exists($path)) { include $path; return; }

        include __DIR__ . '/../Views/Claims/detail.php';
    }

    public function assess()
    {
        $this->requireRole('Claims Staff');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo 'Method not allowed'; exit; }
        $data = [
            'MaTD' => $_POST['MaTD'] ?? uniqid('TD'),
            'MaYC' => $_POST['MaYC'] ?? null,
            'KetQua' => $_POST['KetQua'] ?? '',
            'TrangThai' => 1,
        ];
        if (empty($data['MaYC'])) { echo 'Missing MaYC'; exit; }
        $td = new ThamDinh($this->db);
        $td->create($data);
        $this->db->logAction($_SESSION['user'] ?? 'system', 'assess_claim', null, $data, $_SERVER['REMOTE_ADDR'] ?? null);
        header('Location: index.php?c=Claims&m=detail&MaYC=' . urlencode($data['MaYC'])); exit;
    }

    public function approve()
    {
        $this->requireRole('Claims Staff');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo 'Method not allowed'; exit; }
        $data = [
            'MaPD' => $_POST['MaPD'] ?? uniqid('PD'),
            'MaTD' => $_POST['MaTD'] ?? null,
            'QuyetDinh' => $_POST['QuyetDinh'] ?? '',
            'TrangThai' => 1,
        ];
        if (empty($data['MaTD'])) { echo 'Missing MaTD'; exit; }
        $pd = new PheDuyet($this->db);
        $pd->create($data);
        $this->db->logAction($_SESSION['user'] ?? 'system', 'approve_claim', null, $data, $_SERVER['REMOTE_ADDR'] ?? null);
        header('Location: index.php?c=Claims&m=detail&MaYC=' . urlencode($_POST['MaYC'] ?? '')); exit;
    }

    public function pushToAccounting()
    {
        $this->requireRole('Claims Staff');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo 'Method not allowed'; exit; }
        $maPD = $_POST['MaPD'] ?? null;
        if (!$maPD) { echo 'Missing MaPD'; exit; }
        // ensure approval exists and is approved
        $pd = new PheDuyet($this->db);
        $ap = $pd->find($maPD);
        if (!$ap || $ap['TrangThai'] != 1) { echo 'Claim not approved'; exit; }

        $phieu = new PhieuChi($this->db);
        $data = [
            'MaPC' => $_POST['MaPC'] ?? uniqid('PC'),
            'MaPD' => $maPD,
            'SoTien' => $_POST['SoTien'] ?? 0.00,
            'NgayChi' => $_POST['NgayChi'] ?? date('Y-m-d'),
            'TrangThai' => 1,
        ];
        $phieu->create($data);
        $this->db->logAction($_SESSION['user'] ?? 'system', 'push_to_accounting', null, $data, $_SERVER['REMOTE_ADDR'] ?? null);
        header('Location: index.php?c=Accounting&m=payout'); exit;
    }
}
