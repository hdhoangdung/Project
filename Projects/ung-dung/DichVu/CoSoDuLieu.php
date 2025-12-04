<?php

declare(strict_types=1);

namespace UngDung\DichVu;

use Exception;
use mysqli;
use mysqli_result;

/**
 * Lớp chịu trách nhiệm quản lý kết nối MySQL (Singleton).
 * Cung cấp các helper truy vấn có hỗ trợ prepared statement.
 */
class CoSoDuLieu
{
    private static ?self $instance = null;

    private mysqli $ketNoi;

    private function __construct()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->ketNoi = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->ketNoi->connect_error) {
            throw new Exception('Không thể kết nối CSDL: ' . $this->ketNoi->connect_error);
        }
        $this->ketNoi->set_charset(DB_CHARSET);
    }

    public static function layInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function ketNoi(): mysqli
    {
        return $this->ketNoi;
    }

    /**
     * Thực thi truy vấn với/không có tham số.
     *
     * @return mysqli_result|bool
     */
    public function truyVan(string $sql, array $thamSo = [])
    {
        if (empty($thamSo)) {
            return $this->ketNoi->query($sql);
        }

        $stmt = $this->ketNoi->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Không thể chuẩn bị truy vấn: ' . $this->ketNoi->error);
        }

        $types = '';
        $values = [];
        foreach ($thamSo as $thamSoItem) {
            if (is_int($thamSoItem)) {
                $types .= 'i';
            } elseif (is_float($thamSoItem)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $values[] = $thamSoItem;
        }

        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        $ketQua = $stmt->get_result();

        if ($ketQua instanceof mysqli_result) {
            $stmt->close();
            return $ketQua;
        }

        $thanhCong = $stmt->affected_rows >= 0;
        $stmt->close();

        return $thanhCong;
    }

    public function layDong(string $sql, array $thamSo = []): ?array
    {
        $ketQua = $this->truyVan($sql, $thamSo);
        if ($ketQua instanceof mysqli_result) {
            $row = $ketQua->fetch_assoc();
            $ketQua->free();
            return $row ?: null;
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function layTatCa(string $sql, array $thamSo = []): array
    {
        $ketQua = $this->truyVan($sql, $thamSo);
        if ($ketQua instanceof mysqli_result) {
            $rows = $ketQua->fetch_all(MYSQLI_ASSOC);
            $ketQua->free();
            return $rows;
        }

        return [];
    }

    public function layGiaTri(string $sql, array $thamSo = [])
    {
        $dong = $this->layDong($sql, $thamSo);
        if (!$dong) {
            return null;
        }

        return array_values($dong)[0] ?? null;
    }
}

