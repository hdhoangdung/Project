<?php
namespace App\Models;

use PDO;
use Exception;
use App\Core\Database;
use App\Core\Logger;

/**
 * Customer Model - Vehicle Insurance Management System (Module 1A)
 * Complete OOP implementation with CRUD, validation, relations, and logging
 * 
 * Table: qlbh_khachhang
 * Fields: MaKH, HoTen, NgaySinh, CCCD, DiaChi, SDT, Email, TrangThai, CreatedAt, UpdatedAt
 * 
 * Features:
 * - Full validation (CCCD, Email, Phone, Name, Date)
 * - CRUD operations with automatic logging
 * - Soft delete enforcement
 * - Relations to Vehicles, Contracts, Claims
 * - Paginated listing and search
 * - Integrity constraint checking
 */
class Customer
{
    private $pdo;
    private $logger;

    // Status constants (soft delete system)
    const STATUS_ACTIVE = 'HoatDong';
    const STATUS_INACTIVE = 'NgungHieuLuc';
    const STATUS_DELETED = 'DaXoa';

    // Validation constants
    const CCCD_LENGTH = 12;
    const PHONE_MIN_LENGTH = 9;
    const PHONE_MAX_LENGTH = 14;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getPDO();
        $this->logger = Logger::getInstance();
    }

    /**
     * Create a new customer
     * 
     * @param array $data Customer data (HoTen, NgaySinh, CCCD, DiaChi, SDT, Email)
     * @param int $userId User ID for logging
     * @return int New customer ID (MaKH)
     * @throws Exception
     */
    public function create($data, $userId)
    {
        // Validate input data
        $this->validateCustomerData($data);

        // Check if CCCD already exists
        if ($this->cccdExists($data['CCCD'])) {
            throw new Exception('CCCD already exists in the system');
        }

        // Prepare customer data
        $hoTen = trim($data['HoTen']);
        $ngaySinh = !empty($data['NgaySinh']) ? $data['NgaySinh'] : null;
        $cccd = trim($data['CCCD']);
        $diaChi = trim($data['DiaChi'] ?? '');
        $sdt = trim($data['SDT'] ?? '');
        $email = trim($data['Email'] ?? '');
        $trangThai = self::STATUS_ACTIVE;

        try {
            // Insert customer
            $sql = "INSERT INTO qlbh_khachhang 
                    (HoTen, NgaySinh, CCCD, DiaChi, SDT, Email, TrangThai, CreatedAt, UpdatedAt)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $hoTen,
                $ngaySinh,
                $cccd,
                $diaChi,
                $sdt,
                $email,
                $trangThai
            ]);

            $maKH = $this->pdo->lastInsertId();

            // Log the creation
            $this->logger->log(
                $userId,
                'CREATE',
                'qlbh_khachhang',
                $maKH,
                null,
                json_encode([
                    'HoTen' => $hoTen,
                    'CCCD' => $cccd,
                    'Email' => $email,
                    'DiaChi' => $diaChi,
                    'SDT' => $sdt,
                    'TrangThai' => $trangThai
                ])
            );

            return $maKH;
        } catch (Exception $e) {
            throw new Exception('Failed to create customer: ' . $e->getMessage());
        }
    }

    /**
     * Get customer by ID
     * 
     * @param int $maKH Customer ID
     * @return array|null Customer data or null if not found
     */
    public function getById($maKH)
    {
        $sql = "SELECT * FROM qlbh_khachhang WHERE MaKH = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$maKH]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update customer data
     * 
     * @param int $maKH Customer ID
     * @param array $data Fields to update (HoTen, NgaySinh, DiaChi, SDT, Email)
     * @param int $userId User ID for logging
     * @return bool Success
     * @throws Exception
     */
    public function update($maKH, $data, $userId)
    {
        // Get current customer data
        $customer = $this->getById($maKH);
        if (!$customer) {
            throw new Exception('Customer not found');
        }

        // Validate input data (only validate provided fields)
        $this->validateCustomerData($data, true);

        // Check if CCCD is being updated and already exists
        if (!empty($data['CCCD']) && $data['CCCD'] !== $customer['CCCD']) {
            if ($this->cccdExists($data['CCCD'])) {
                throw new Exception('CCCD already exists in the system');
            }
        }

        // Build update clause
        $updateFields = [];
        $values = [];
        $allowedFields = ['HoTen', 'NgaySinh', 'CCCD', 'DiaChi', 'SDT', 'Email'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $values[] = $field === 'NgaySinh' && empty($data[$field]) ? null : trim($data[$field]);
            }
        }

        if (empty($updateFields)) {
            throw new Exception('No valid fields to update');
        }

        // Add UpdatedAt
        $updateFields[] = "UpdatedAt = NOW()";

        try {
            // Prepare old values for logging
            $oldValues = [];
            foreach ($allowedFields as $field) {
                if (isset($data[$field]) && $data[$field] !== $customer[$field]) {
                    $oldValues[$field] = $customer[$field];
                }
            }

            // Execute update
            $values[] = $maKH;
            $sql = "UPDATE qlbh_khachhang SET " . implode(', ', $updateFields) . " WHERE MaKH = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($values);

            // Log the update
            if (!empty($oldValues)) {
                $newValues = [];
                foreach ($allowedFields as $field) {
                    if (isset($data[$field])) {
                        $newValues[$field] = $field === 'NgaySinh' && empty($data[$field]) ? null : trim($data[$field]);
                    }
                }

                $this->logger->log(
                    $userId,
                    'UPDATE',
                    'qlbh_khachhang',
                    $maKH,
                    json_encode($oldValues),
                    json_encode($newValues)
                );
            }

            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to update customer: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete customer (set TrangThai='DaXoa')
     * 
     * @param int $maKH Customer ID
     * @param int $userId User ID for logging
     * @return bool Success
     * @throws Exception
     */
    public function softDelete($maKH, $userId)
    {
        // Get customer
        $customer = $this->getById($maKH);
        if (!$customer) {
            throw new Exception('Customer not found');
        }

        // Check integrity constraints
        if ($this->hasActiveVehicles($maKH)) {
            throw new Exception('Cannot delete customer with active vehicles');
        }

        if ($this->hasActiveContracts($maKH)) {
            throw new Exception('Cannot delete customer with active contracts');
        }

        if ($this->hasActiveClaims($maKH)) {
            throw new Exception('Cannot delete customer with active claims');
        }

        try {
            // Soft delete
            $sql = "UPDATE qlbh_khachhang SET TrangThai = ?, UpdatedAt = NOW() WHERE MaKH = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([self::STATUS_DELETED, $maKH]);

            // Log the deletion
            $this->logger->log(
                $userId,
                'DELETE',
                'qlbh_khachhang',
                $maKH,
                json_encode(['TrangThai' => $customer['TrangThai']]),
                json_encode(['TrangThai' => self::STATUS_DELETED])
            );

            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to delete customer: ' . $e->getMessage());
        }
    }

    /**
     * Get paginated list of customers
     * 
     * @param int $page Page number (1-indexed)
     * @param int $pageSize Records per page
     * @param string $status Filter by status (optional)
     * @return array ['data' => [...], 'total' => int, 'pages' => int, 'currentPage' => int]
     */
    public function list($page = 1, $pageSize = 20, $status = null)
    {
        // Validate pagination
        $page = max(1, intval($page));
        $pageSize = max(1, min(100, intval($pageSize)));

        // Build query
        $where = [];
        $params = [];

        if ($status) {
            $where[] = "TrangThai = ?";
            $params[] = $status;
        } else {
            // Default: exclude soft-deleted
            $where[] = "TrangThai != ?";
            $params[] = self::STATUS_DELETED;
        }

        $whereClause = implode(' AND ', $where);

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM qlbh_khachhang WHERE $whereClause";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Get paginated data
        $offset = ($page - 1) * $pageSize;
        $dataSql = "SELECT * FROM qlbh_khachhang WHERE $whereClause ORDER BY CreatedAt DESC LIMIT ? OFFSET ?";
        $dataStmt = $this->pdo->prepare($dataSql);
        $dataParams = array_merge($params, [$pageSize, $offset]);
        $dataStmt->execute($dataParams);
        $data = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate total pages
        $totalPages = ceil($total / $pageSize);

        return [
            'data' => $data,
            'total' => $total,
            'pages' => $totalPages,
            'currentPage' => $page,
            'pageSize' => $pageSize
        ];
    }

    /**
     * Search customers by CCCD or phone number
     * 
     * @param string $cccd Search by CCCD (optional)
     * @param string $phone Search by phone (optional)
     * @return array List of matching customers
     */
    public function search($cccd = '', $phone = '')
    {
        $where = ["TrangThai != ?"];
        $params = [self::STATUS_DELETED];

        if (!empty($cccd)) {
            $where[] = "CCCD LIKE ?";
            $params[] = '%' . trim($cccd) . '%';
        }

        if (!empty($phone)) {
            $where[] = "SDT LIKE ?";
            $params[] = '%' . trim($phone) . '%';
        }

        if (count($where) === 1) {
            // No search criteria
            return [];
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT * FROM qlbh_khachhang WHERE $whereClause ORDER BY HoTen ASC LIMIT 100";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all vehicles belonging to this customer
     * 
     * @param int $maKH Customer ID
     * @return array List of vehicles
     */
    public function getVehicles($maKH)
    {
        $sql = "SELECT * FROM qlbh_xe WHERE MaKH = ? AND TrangThai != ? ORDER BY CreatedAt DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$maKH, 'DaXoa']);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all contracts for this customer
     * 
     * @param int $maKH Customer ID
     * @return array List of contracts
     */
    public function getContracts($maKH)
    {
        $sql = "SELECT * FROM qlbh_hopdong WHERE MaKH = ? AND TrangThai != ? ORDER BY CreatedAt DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$maKH, 'DaXoa']);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all claims by this customer (via contracts)
     * 
     * @param int $maKH Customer ID
     * @return array List of claims
     */
    public function getClaimsByCustomer($maKH)
    {
        $sql = "SELECT yc.* FROM qlbh_yeucau yc
                INNER JOIN qlbh_hopdong hd ON yc.MaHopDong = hd.MaHopDong
                WHERE hd.MaKH = ? AND yc.TrangThai != ?
                ORDER BY yc.CreatedAt DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$maKH, 'DaXoa']);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get customer statistics
     * 
     * @param int $maKH Customer ID
     * @return array Statistics
     */
    public function getStatistics($maKH)
    {
        $stats = [
            'vehicles' => 0,
            'contracts' => 0,
            'claims' => 0,
            'totalClaimAmount' => 0
        ];

        // Count vehicles
        $vehicleSql = "SELECT COUNT(*) as count FROM qlbh_xe WHERE MaKH = ? AND TrangThai != ?";
        $vehicleStmt = $this->pdo->prepare($vehicleSql);
        $vehicleStmt->execute([$maKH, 'DaXoa']);
        $stats['vehicles'] = $vehicleStmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Count contracts
        $contractSql = "SELECT COUNT(*) as count FROM qlbh_hopdong WHERE MaKH = ? AND TrangThai != ?";
        $contractStmt = $this->pdo->prepare($contractSql);
        $contractStmt->execute([$maKH, 'DaXoa']);
        $stats['contracts'] = $contractStmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Count claims
        $claimSql = "SELECT COUNT(*) as count FROM qlbh_yeucau yc
                     INNER JOIN qlbh_hopdong hd ON yc.MaHopDong = hd.MaHopDong
                     WHERE hd.MaKH = ? AND yc.TrangThai != ?";
        $claimStmt = $this->pdo->prepare($claimSql);
        $claimStmt->execute([$maKH, 'DaXoa']);
        $stats['claims'] = $claimStmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Sum claim amounts
        $amountSql = "SELECT COALESCE(SUM(SoTienYeuCau), 0) as total FROM qlbh_yeucau yc
                      INNER JOIN qlbh_hopdong hd ON yc.MaHopDong = hd.MaHopDong
                      WHERE hd.MaKH = ? AND yc.TrangThai != ?";
        $amountStmt = $this->pdo->prepare($amountSql);
        $amountStmt->execute([$maKH, 'DaXoa']);
        $stats['totalClaimAmount'] = floatval($amountStmt->fetch(PDO::FETCH_ASSOC)['total']);

        return $stats;
    }

    /**
     * ========== VALIDATION HELPERS ==========
     */

    /**
     * Validate customer data
     * 
     * @param array $data Customer data
     * @param bool $isPartial Whether this is a partial update (true) or full create (false)
     * @throws Exception
     */
    private function validateCustomerData($data, $isPartial = false)
    {
        // For create: all fields required
        // For update: only provided fields validated
        if (!$isPartial) {
            if (empty($data['HoTen'])) {
                throw new Exception('HoTen is required');
            }
            if (empty($data['CCCD'])) {
                throw new Exception('CCCD is required');
            }
        }

        // Validate HoTen if provided
        if (isset($data['HoTen']) && !$this->validateName($data['HoTen'])) {
            throw new Exception('HoTen must not be empty');
        }

        // Validate CCCD if provided
        if (isset($data['CCCD']) && !$this->validateCCCD($data['CCCD'])) {
            throw new Exception('CCCD must be exactly ' . self::CCCD_LENGTH . ' digits');
        }

        // Validate Email if provided
        if (isset($data['Email']) && !empty($data['Email']) && !$this->validateEmail($data['Email'])) {
            throw new Exception('Invalid email format');
        }

        // Validate SDT if provided
        if (isset($data['SDT']) && !empty($data['SDT']) && !$this->validatePhone($data['SDT'])) {
            throw new Exception('Invalid phone number format (must be 9-14 digits with optional +)');
        }

        // Validate NgaySinh if provided
        if (isset($data['NgaySinh']) && !empty($data['NgaySinh']) && !$this->validateDate($data['NgaySinh'])) {
            throw new Exception('Invalid date format (must be YYYY-MM-DD)');
        }
    }

    /**
     * Validate customer name
     */
    private function validateName($name)
    {
        return !empty(trim($name)) && strlen(trim($name)) <= 255;
    }

    /**
     * Validate CCCD (exactly 12 digits)
     */
    private function validateCCCD($cccd)
    {
        return preg_match('/^\d{' . self::CCCD_LENGTH . '}$/', trim($cccd)) === 1;
    }

    /**
     * Validate email address
     */
    private function validateEmail($email)
    {
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate phone number (numeric, optional +, 9-14 digits)
     */
    private function validatePhone($phone)
    {
        $phone = trim($phone);
        return preg_match('/^\+?\d{' . self::PHONE_MIN_LENGTH . ',' . self::PHONE_MAX_LENGTH . '}$/', $phone) === 1;
    }

    /**
     * Validate date format (YYYY-MM-DD)
     */
    private function validateDate($date)
    {
        $date = trim($date);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        // Verify valid date
        list($year, $month, $day) = explode('-', $date);
        return checkdate((int)$month, (int)$day, (int)$year);
    }

    /**
     * Check if CCCD already exists
     */
    private function cccdExists($cccd)
    {
        $sql = "SELECT COUNT(*) as count FROM qlbh_khachhang WHERE CCCD = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([trim($cccd)]);

        return $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    }

    /**
     * ========== INTEGRITY CONSTRAINT HELPERS ==========
     */

    /**
     * Check if customer has active vehicles
     */
    private function hasActiveVehicles($maKH)
    {
        $sql = "SELECT COUNT(*) as count FROM qlbh_xe WHERE MaKH = ? AND TrangThai != ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$maKH, 'DaXoa']);

        return $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    }

    /**
     * Check if customer has active contracts
     */
    private function hasActiveContracts($maKH)
    {
        $sql = "SELECT COUNT(*) as count FROM qlbh_hopdong WHERE MaKH = ? AND TrangThai != ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$maKH, 'DaXoa']);

        return $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    }

    /**
     * Check if customer has active claims
     */
    private function hasActiveClaims($maKH)
    {
        $sql = "SELECT COUNT(*) as count FROM qlbh_yeucau yc
                INNER JOIN qlbh_hopdong hd ON yc.MaHopDong = hd.MaHopDong
                WHERE hd.MaKH = ? AND yc.TrangThai != ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$maKH, 'DaXoa']);

        return $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    }
}
