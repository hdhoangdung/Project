<?php

declare(strict_types=1);

/**
 * Tệp tự động tải các lớp thuộc không gian tên UngDung.
 * Các lớp được phân chia theo thư mục con trong `ung-dung/`.
 */
spl_autoload_register(static function (string $class): void {
    $prefix = 'UngDung\\';
    $baseDir = __DIR__ . '/../';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});