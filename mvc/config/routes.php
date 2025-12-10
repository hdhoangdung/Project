<?php
/**
 * Route Configuration
 * Maps routes to controllers and methods
 * Format: 'route' => ['controller' => 'ControllerName', 'method' => 'methodName']
 */

return [
    // Default routes (will be expanded in later modules)
    
    // Auth routes (Module 0C)
    'login' => ['controller' => 'Auth', 'method' => 'login'],
    'logout' => ['controller' => 'Auth', 'method' => 'logout'],
    'authenticate' => ['controller' => 'Auth', 'method' => 'authenticate'],
    
    // Customer routes (Module 1)
    'customer/list' => ['controller' => 'Customer', 'method' => 'list'],
    'customer/create' => ['controller' => 'Customer', 'method' => 'create'],
    'customer/edit' => ['controller' => 'Customer', 'method' => 'edit'],
    'customer/delete' => ['controller' => 'Customer', 'method' => 'delete'],
    
    // Vehicle routes (Module 2)
    'vehicle/list' => ['controller' => 'Vehicle', 'method' => 'list'],
    'vehicle/add' => ['controller' => 'Vehicle', 'method' => 'add'],
    'vehicle/edit' => ['controller' => 'Vehicle', 'method' => 'edit'],
    'vehicle/delete' => ['controller' => 'Vehicle', 'method' => 'delete'],
    
    // Claims routes (Module 3)
    'claims/list' => ['controller' => 'Claims', 'method' => 'list'],
    'claims/submit' => ['controller' => 'Claims', 'method' => 'submit'],
    'claims/detail' => ['controller' => 'Claims', 'method' => 'detail'],
    
    // Accounting routes (Module 4)
    'accounting/list' => ['controller' => 'Accounting', 'method' => 'list'],
    'accounting/receipt' => ['controller' => 'Accounting', 'method' => 'receipt'],
    'accounting/payout' => ['controller' => 'Accounting', 'method' => 'payout'],
    
    // Search routes
    'search' => ['controller' => 'Search', 'method' => 'query'],
];
