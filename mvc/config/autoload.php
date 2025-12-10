<?php
/**
 * PSR-4 Autoloader Configuration
 * Registers namespace to directory mappings for automatic class loading
 */

spl_autoload_register(function ($class) {
    // Remove leading backslash if present
    $class = ltrim($class, '\\');

    // Map namespaces to directories
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';

    // Check if the class uses the App namespace
    if (strpos($class, $prefix) === 0) {
        // Remove the namespace prefix and convert to file path
        $relative_class = substr($class, strlen($prefix));
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }

    // Check for core classes
    $prefix = 'App\\Core\\';
    $base_dir = __DIR__ . '/../core/';
    
    if (strpos($class, $prefix) === 0) {
        $relative_class = substr($class, strlen($prefix));
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }

    return false;
});
