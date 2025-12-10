<?php
namespace App\Core;

use PDO;

/**
 * Logger - Audit Trail & System Logging (Module 0C)
 * Complete logging system for compliance and debugging
 * 
 * Features:
 * - Automatic logging of all CRUD operations
 * - JSON storage of before/after values
 * - IP address tracking
 * - User action history
 */
class Logger
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $this->pdo = Database::getInstance()->getPDO();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Log an action to the audit trail
     * 
     * @param int $userID - User who performed action
     * @param string $action - Action type (create, update, delete, login, logout, etc.)
     * @param string $table - Table affected
     * @param mixed $recordID - Record ID affected
     * @param mixed $oldValue - Previous value (array or string)
     * @param mixed $newValue - New value (array or string)
     * @return bool - True if logged successfully
     */
    public function log($userID, $action, $table, $recordID, $oldValue = null, $newValue = null)
    {
        try {
            $sql = "INSERT INTO qlbh_lichsu 
                    (`User`, `Action`, `Timestamp`, OldData, NewData, IP) 
                    VALUES (:user, :action, NOW(), :oldData, :newData, :ip)";

            $stmt = $this->pdo->prepare($sql);

            // Prepare log data
            $actionLog = $action;
            if ($table && $recordID) {
                $actionLog = "{$action}_{$table}#{$recordID}";
            }

            // Convert values to JSON if array
            $oldDataJson = $oldValue !== null ? json_encode($oldValue, JSON_UNESCAPED_UNICODE) : null;
            $newDataJson = $newValue !== null ? json_encode($newValue, JSON_UNESCAPED_UNICODE) : null;

            // Get client IP
            $ip = $this->getClientIP();

            // Get username from Auth if available
            $auth = Auth::getInstance();
            $username = $auth->isLoggedIn() ? $auth->getUsername() : 'system';

            // Execute log
            $result = $stmt->execute([
                ':user' => $username,
                ':action' => $actionLog,
                ':oldData' => $oldDataJson,
                ':newData' => $newDataJson,
                ':ip' => $ip,
            ]);

            // Also write to system log file if DEBUG mode
            if (DEBUG) {
                $this->writeToFile($username, $actionLog, $oldDataJson, $newDataJson, $ip);
            }

            return $result;
        } catch (\Exception $e) {
            // Log errors but don't crash application
            error_log('Logger error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log a custom event (not tied to database operation)
     * 
     * @param int $userID - User performing action
     * @param string $event - Event description
     * @param mixed $data - Event data (optional)
     * @return bool
     */
    public function logEvent($userID, $event, $data = null)
    {
        return $this->log($userID, $event, null, null, null, $data);
    }

    /**
     * Get client IP address
     */
    private function getClientIP()
    {
        // Check for IP from shared internet
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        // Check for IP passed from proxy
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }
        // Check for remote address
        else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        }

        return trim($ip);
    }

    /**
     * Write log entry to system log file
     */
    private function writeToFile($user, $action, $oldData, $newData, $ip)
    {
        try {
            $logFile = LOGS_PATH . '/system.log';
            $timestamp = date('Y-m-d H:i:s');
            $logEntry = sprintf(
                "[%s] User: %s | Action: %s | IP: %s | Old: %s | New: %s\n",
                $timestamp,
                $user,
                $action,
                $ip,
                substr($oldData ?? 'null', 0, 100),
                substr($newData ?? 'null', 0, 100)
            );

            error_log($logEntry, 3, $logFile);
        } catch (\Exception $e) {
            // Silent fail for file logging
        }
    }

    /**
     * Get log entries for a user
     * 
     * @param int $userID - User to get logs for
     * @param int $limit - Limit results
     * @return array - Log entries
     */
    public function getUserLogs($userID, $limit = 100)
    {
        $sql = "SELECT * FROM qlbh_lichsu 
                WHERE `User` = :user 
                ORDER BY `Timestamp` DESC 
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user', $userID, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get log entries for a table
     * 
     * @param string $table - Table name
     * @param int $limit - Limit results
     * @return array - Log entries
     */
    public function getTableLogs($table, $limit = 100)
    {
        $sql = "SELECT * FROM qlbh_lichsu 
                WHERE `Action` LIKE :table 
                ORDER BY `Timestamp` DESC 
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':table', "%{$table}%", PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all logs within date range
     * 
     * @param string $startDate - Start date (YYYY-MM-DD)
     * @param string $endDate - End date (YYYY-MM-DD)
     * @param int $limit - Limit results
     * @return array - Log entries
     */
    public function getLogsByDateRange($startDate, $endDate, $limit = 1000)
    {
        $sql = "SELECT * FROM qlbh_lichsu 
                WHERE `Timestamp` BETWEEN :start AND :end 
                ORDER BY `Timestamp` DESC 
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':start', $startDate . ' 00:00:00', PDO::PARAM_STR);
        $stmt->bindValue(':end', $endDate . ' 23:59:59', PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
