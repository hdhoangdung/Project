<?php
/**
 * Vehicle Insurance Management System
 * Public Entry Point with Auth, Layout & Logging (Module 0C)
 * 
 * Router format: index.php?c=ControllerName&m=methodName
 * Example: index.php?c=Customer&m=list
 * 
 * Features:
 * - Session management and authentication
 * - Role-based access control (RBAC)
 * - Automatic layout wrapping (header/footer)
 * - Complete audit logging
 */

// Load configuration
require_once __DIR__ . '/../config/config.php';

// Define BASE_URL for templates
define('BASE_URL', APP_URL);

// Register PSR-4 Autoloader
spl_autoload_register(function ($class) {
    // Map App namespace to app directory
    if (strpos($class, 'App\\') === 0) {
        $relative_class = substr($class, 4);
        $file = __DIR__ . '/../app/' . str_replace('\\', '/', $relative_class) . '.php';
        
        if (file_exists($file)) {
            require $file;
            return;
        }
    }

    // Map core classes to core directory
    if (strpos($class, 'App\\Core\\') === 0) {
        $relative_class = substr($class, 9);
        $file = __DIR__ . '/../core/' . str_replace('\\', '/', $relative_class) . '.php';
        
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize core services
$database = \App\Core\Database::getInstance();
$auth = \App\Core\Auth::getInstance();
$logger = \App\Core\Logger::getInstance();

// Get controller and method from query string
$controller = isset($_GET['c']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['c']) : 'Home';
$method = isset($_GET['m']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['m']) : 'index';

// Build controller class name
$controllerClass = 'App\\Controllers\\' . ucfirst($controller) . 'Controller';

// Check if controller exists
if (!class_exists($controllerClass)) {
    http_response_code(404);
    
    // Include layout if user is logged in
    if ($auth->isLoggedIn()) {
        include __DIR__ . '/../app/Views/layout/header.php';
        echo '<div class="alert alert-danger" role="alert">';
        echo '<h4 class="alert-heading">Error 404</h4>';
        echo '<p>Controller not found: <strong>' . htmlspecialchars($controller) . '</strong></p>';
        echo '</div>';
        include __DIR__ . '/../app/Views/layout/footer.php';
    } else {
        die('Error 404: Controller not found');
    }
    exit;
}

// Check if method exists
$instance = new $controllerClass();
if (!method_exists($instance, $method)) {
    http_response_code(404);
    
    if ($auth->isLoggedIn()) {
        include __DIR__ . '/../app/Views/layout/header.php';
        echo '<div class="alert alert-danger" role="alert">';
        echo '<h4 class="alert-heading">Error 404</h4>';
        echo '<p>Method not found: <strong>' . htmlspecialchars($method) . '</strong></p>';
        echo '</div>';
        include __DIR__ . '/../app/Views/layout/footer.php';
    } else {
        die('Error 404: Method not found');
    }
    exit;
}

// Log the request (skip login/logout to avoid circular logging)
if ($controller !== 'Auth' || !in_array($method, ['login', 'logout'])) {
    $logger->logEvent(
        $auth->isLoggedIn() ? $auth->getUserID() : null,
        "access_{$controller}_{$method}",
        ['controller' => $controller, 'method' => $method]
    );
}

// Capture output from controller method
ob_start();
try {
    call_user_func([$instance, $method]);
    $content = ob_get_clean();
} catch (\Exception $e) {
    ob_get_clean();
    http_response_code(500);
    
    if ($auth->isLoggedIn()) {
        include __DIR__ . '/../app/Views/layout/header.php';
        echo '<div class="alert alert-danger" role="alert">';
        echo '<h4 class="alert-heading">Error 500</h4>';
        if (DEBUG) {
            echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        } else {
            echo '<p>An error occurred. Please try again later.</p>';
        }
        echo '</div>';
        include __DIR__ . '/../app/Views/layout/footer.php';
    } else {
        die('Error 500: Internal Server Error');
    }
    exit;
}

// For Auth/Login views, don't wrap in layout
if ($controller === 'Auth' && in_array($method, ['login', 'logout'])) {
    echo $content;
} else {
    // Wrap content in layout
    if ($auth->isLoggedIn()) {
        include __DIR__ . '/../app/Views/layout/header.php';
        echo $content;
        include __DIR__ . '/../app/Views/layout/footer.php';
    } else {
        // For non-authenticated users accessing non-auth pages, redirect to login
        if ($controller !== 'Auth') {
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . APP_URL . '?c=Auth&m=login');
            exit;
        }
        echo $content;
    }
}


