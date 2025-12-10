<?php
/**
 * Hàm tiện ích cho hệ thống Quản Lý Bảo Hiểm Xe
 */

/**
 * Format số tiền theo định dạng Việt Nam
 */
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . ' đ';
}

/**
 * Format ngày theo định dạng Việt Nam
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date) || $date == '0000-00-00') {
        return 'Chưa cập nhật';
    }
    return date($format, strtotime($date));
}

/**
 * Tính số ngày còn lại đến ngày hết hạn
 */
function daysUntilExpiry($expiry_date) {
    $expiry = strtotime($expiry_date);
    $today = strtotime(date('Y-m-d'));
    $diff = $expiry - $today;
    return floor($diff / (60 * 60 * 24));
}

/**
 * Lấy badge class cho trạng thái hợp đồng
 */
function getContractStatusBadge($status) {
    $badges = [
        'active' => 'badge-success',
        'suspended' => 'badge-warning',
        'expired' => 'badge-secondary'
    ];
    return $badges[$status] ?? 'badge-secondary';
}

/**
 * Lấy text hiển thị cho trạng thái hợp đồng
 */
function getContractStatusText($status) {
    $texts = [
        'active' => 'Có hiệu lực',
        'suspended' => 'Tạm ngưng',
        'expired' => 'Hết hạn'
    ];
    return $texts[$status] ?? 'Không xác định';
}

/**
 * Kiểm tra hợp đồng sắp hết hạn (còn <= 30 ngày)
 */
function isContractExpiring($expiry_date, $status = 'active') {
    if ($status !== 'active') {
        return false;
    }
    $days_left = daysUntilExpiry($expiry_date);
    return $days_left <= 30 && $days_left > 0;
}

/**
 * Tạo mã tự động cho khách hàng mới
 */
function generateCustomerCode($pdo) {
    $prefix = 'KH';
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM customers");
        $count = $stmt->fetch()['count'] + 1;
        return $prefix . str_pad($count, 6, '0', STR_PAD_LEFT);
    } catch(PDOException $e) {
        return $prefix . '000001';
    }
}

/**
 * Tạo mã tự động cho hợp đồng mới
 */
function generateContractCode($pdo) {
    $prefix = 'HD';
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM contracts");
        $count = $stmt->fetch()['count'] + 1;
        return $prefix . str_pad($count, 6, '0', STR_PAD_LEFT);
    } catch(PDOException $e) {
        return $prefix . '000001';
    }
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate số điện thoại Việt Nam
 */
function isValidPhone($phone) {
    return preg_match('/^(0[3|5|7|8|9])+([0-9]{8})$/', $phone);
}

/**
 * Validate CMND/CCCD
 */
function isValidIdCard($id_card) {
    return preg_match('/^[0-9]{9,12}$/', $id_card);
}

/**
 * Lấy thống kê nhanh cho dashboard
 */
function getDashboardStats($pdo) {
    $stats = [];
    
    try {
        // Tổng khách hàng
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM customers");
        $stats['total_customers'] = $stmt->fetch()['total'];
        
        // Hợp đồng đang hiệu lực
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM contracts WHERE status = 'active'");
        $stats['active_contracts'] = $stmt->fetch()['total'];
        
        // Hợp đồng sắp hết hạn (30 ngày)
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM contracts WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND status = 'active'");
        $stats['expiring_contracts'] = $stmt->fetch()['total'];
        
        // Tổng giá trị bảo hiểm
        $stmt = $pdo->query("SELECT SUM(insurance_value) as total FROM contracts WHERE status = 'active'");
        $stats['total_insurance_value'] = $stmt->fetch()['total'] ?? 0;
    } catch(PDOException $e) {
        // Trả về giá trị mặc định nếu có lỗi
        $stats['total_customers'] = 0;
        $stats['active_contracts'] = 0;
        $stats['expiring_contracts'] = 0;
        $stats['total_insurance_value'] = 0;
    }
    
    return $stats;
}

/**
 * Chuyển hướng với thông báo
 */
function redirectWithMessage($url, $type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
    header("Location: $url");
    exit;
}

/**
 * Hiển thị flash message
 */
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $message['type'];
        $text = $message['message'];
        
        $alert_class = '';
        switch ($type) {
            case 'success':
                $alert_class = 'alert-success';
                break;
            case 'error':
                $alert_class = 'alert-error';
                break;
            case 'warning':
                $alert_class = 'alert-warning';
                break;
            case 'info':
                $alert_class = 'alert-info';
                break;
        }
        
        echo "<div class='alert $alert_class'>$text</div>";
        unset($_SESSION['flash_message']);
    }
}

/**
 * Tính tỷ lệ phí dựa trên giá trị bảo hiểm và mức phí
 */
function calculatePremiumRate($insurance_value, $premium) {
    if ($insurance_value > 0) {
        return round(($premium / $insurance_value) * 100, 2);
    }
    return 1.5; // Tỷ lệ mặc định
}

/**
 * Tính mức phí tự động
 */
function calculateAutoPremium($insurance_value, $premium_rate = 1.5) {
    return $insurance_value * ($premium_rate / 100);
}

/**
 * Format số tiền với tooltip giải thích
 */
function formatInsuranceValue($amount) {
    $formatted = formatCurrency($amount);
    return '<span title="Giá trị bảo hiểm: Số tiền tối đa được bồi thường">' . $formatted . '</span>';
}

function formatPremium($amount) {
    $formatted = formatCurrency($amount);
    return '<span title="Mức phí: Số tiền khách hàng phải trả">' . $formatted . '</span>';
}

/**
 * Log activity
 */
function logActivity($pdo, $action, $description, $user_id = null) {
    try {
        $sql = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $user_id,
            $action,
            $description,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

/**
 * Backup database
 */
function backupDatabase($pdo, $backup_path) {
    try {
        $tables = array();
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        $return = '';
        foreach ($tables as $table) {
            $stmt = $pdo->query("SELECT * FROM $table");
            $num_fields = $stmt->columnCount();
            
            $return .= "DROP TABLE IF EXISTS $table;\n";
            $stmt2 = $pdo->query("SHOW CREATE TABLE $table");
            $row2 = $stmt2->fetch(PDO::FETCH_NUM);
            $return .= $row2[1] . ";\n\n";
            
            for ($i = 0; $i < $num_fields; $i++) {
                while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                    $return .= "INSERT INTO $table VALUES(";
                    for ($j = 0; $j < $num_fields; $j++) {
                        $row[$j] = addslashes($row[$j]);
                        $row[$j] = preg_replace("/\n/", "\\n", $row[$j]);
                        if (isset($row[$j])) {
                            $return .= '"' . $row[$j] . '"';
                        } else {
                            $return .= '""';
                        }
                        if ($j < ($num_fields - 1)) {
                            $return .= ',';
                        }
                    }
                    $return .= ");\n";
                }
            }
            $return .= "\n\n";
        }
        
        // Save file
        $filename = $backup_path . 'db_backup_' . date('Y-m-d_H-i-s') . '.sql';
        file_put_contents($filename, $return);
        
        return $filename;
    } catch (Exception $e) {
        return false;
    }
}
?>