<?php
namespace App\Core;

class Router
{
    /**
     * Dispatch request to appropriate controller and method
     * Expected format: ?c=ControllerName&m=methodName
     */
    public static function dispatch()
    {
        // Get controller and method from query string
        $controller = isset($_GET['c']) ? self::sanitize($_GET['c']) : 'home';
        $method     = isset($_GET['m']) ? self::sanitize($_GET['m']) : 'index';

        // Build class name
        $controllerClass = 'App\\Controllers\\' . ucfirst($controller) . 'Controller';

        // Check if controller exists
        if (!class_exists($controllerClass)) {
            http_response_code(404);
            die('Error 404: Controller not found - ' . htmlspecialchars($controller));
        }

        // Instantiate and call method
        $instance = new $controllerClass();

        if (!method_exists($instance, $method)) {
            http_response_code(404);
            die('Error 404: Method not found - ' . htmlspecialchars($method));
        }

        // Execute controller method
        call_user_func([$instance, $method]);
    }

    /**
     * Sanitize controller/method names to prevent injection
     */
    private static function sanitize($input)
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $input);
    }
}
