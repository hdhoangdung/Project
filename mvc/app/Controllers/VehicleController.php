<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\Vehicle;

class VehicleController extends Controller
{
    protected $vehicleModel;

    public function __construct()
    {
        parent::__construct();
        $this->vehicleModel = new Vehicle($this->db);
    }

    public function add()
    {
        $this->requireRole('Vehicle Staff');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'MaXe' => $_POST['MaXe'] ?? null,
                'BienSoXe' => $_POST['BienSoXe'] ?? null,
                'SoKhung' => $_POST['SoKhung'] ?? null,
                'SoMay' => $_POST['SoMay'] ?? null,
                'MaKH' => $_POST['MaKH'] ?? null,
                'HangXe' => $_POST['HangXe'] ?? null,
                'NamSX' => $_POST['NamSX'] ?? null,
                'LoaiXe' => $_POST['LoaiXe'] ?? null,
                'TrangThai' => 1,
            ];
            // duplicate checks
            $exists = $this->vehicleModel->all(['(BienSoXe = :bs OR SoKhung = :sk OR SoMay = :sm)', 'TrangThai = 1'], [':bs' => $data['BienSoXe'], ':sk' => $data['SoKhung'], ':sm' => $data['SoMay']]);
            if ($exists) {
                echo 'Duplicate plate/chassis/engine detected.'; exit;
            }
            $this->vehicleModel->create($data);
            $this->db->logAction($_SESSION['user'] ?? 'system', 'create_vehicle', null, $data, $_SERVER['REMOTE_ADDR'] ?? null);
            header('Location: index.php?c=Vehicle&m=list'); exit;
        }
        $path = __DIR__ . '/../../../../1/add_contract.php';
        if (file_exists($path)) { include $path; return; }
        include __DIR__ . '/../Views/Vehicle/add.php';
    }

    public function delete()
    {
        $this->requireRole('Vehicle Staff');
        $id = $_GET['id'] ?? null;
        if (!$id) { echo 'Missing id'; exit; }
        if ($this->vehicleModel->hasContract($id)) { echo 'Cannot delete vehicle: used in a contract'; exit; }
        $this->vehicleModel->softDelete($id);
        $this->db->logAction($_SESSION['user'] ?? 'system', 'soft_delete_vehicle', ['id' => $id], null, $_SERVER['REMOTE_ADDR'] ?? null);
        header('Location: index.php?c=Vehicle&m=list'); exit;
    }

    public function list()
    {
        $this->requireRole('Vehicle Staff');
        $path = __DIR__ . '/../../../../1/contracts.php';
        if (file_exists($path)) { include $path; return; }
        $vehicles = $this->vehicleModel->all(['TrangThai = 1']);
        include __DIR__ . '/../Views/Vehicle/list.php';
    }

    public function edit()
    {
        $this->requireRole('Vehicle Staff');
        $id = $_GET['id'] ?? null;
        if (!$id) { echo 'Missing id'; exit; }
        $vehicle = $this->vehicleModel->find($id);
        if (!$vehicle) { echo 'Vehicle not found'; exit; }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'BienSoXe' => $_POST['BienSoXe'] ?? $vehicle['BienSoXe'],
                'SoKhung' => $_POST['SoKhung'] ?? $vehicle['SoKhung'],
                'SoMay' => $_POST['SoMay'] ?? $vehicle['SoMay'],
                'HangXe' => $_POST['HangXe'] ?? $vehicle['HangXe'],
                'NamSX' => $_POST['NamSX'] ?? $vehicle['NamSX'],
                'LoaiXe' => $_POST['LoaiXe'] ?? $vehicle['LoaiXe'],
            ];
            // duplicate checks (exclude current vehicle)
            $exists = $this->vehicleModel->all(['(BienSoXe = :bs OR SoKhung = :sk OR SoMay = :sm)', 'TrangThai = 1'], [':bs' => $data['BienSoXe'], ':sk' => $data['SoKhung'], ':sm' => $data['SoMay']]);
            foreach ($exists as $e) {
                if ($e['MaXe'] !== $id) { echo 'Duplicate plate/chassis/engine detected.'; exit; }
            }
            $this->vehicleModel->update($id, $data);
            $this->db->logAction($_SESSION['user'] ?? 'system', 'edit_vehicle', $vehicle, $data, $_SERVER['REMOTE_ADDR'] ?? null);
            header('Location: index.php?c=Vehicle&m=list'); exit;
        }
        include __DIR__ . '/../Views/Vehicle/edit.php';
    }
}
