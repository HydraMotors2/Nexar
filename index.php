<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (file_exists(__DIR__ . '/config/config.php')) {
    require_once __DIR__ . '/config/config.php';
}

$route = '';

if (isset($_GET['_route']) && is_string($_GET['_route'])) {
    $route = strtolower(trim($_GET['_route']));
    $route = preg_replace('/[^a-z0-9\-\/]/', '', $route);
    $route = trim($route, '/');
}

$routes = [
    '' => 'pages/home.php',
    'login' => 'pages/login.php',
    'register' => 'pages/register.php',
    'dashboard' => 'pages/dashboard.php',
    'profile' => 'pages/profile.php',
    'my-suppliers' => 'pages/my-suppliers.php',
    'my-orders' => 'pages/my-orders.php',
    'logout' => 'pages/logout.php',
    'provider' => 'pages/provider.php'
];

if (isset($routes[$route])) {
    $targetFile = $routes[$route];
    $fullPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . $targetFile);
    $projectRoot = realpath(__DIR__);
    
    if ($fullPath && $projectRoot && strpos($fullPath, $projectRoot) === 0 && file_exists($fullPath)) {
        $extension = pathinfo($fullPath, PATHINFO_EXTENSION);
        
        if ($extension === 'html') {
            header('Content-Type: text/html; charset=utf-8');
        } elseif ($extension === 'php') {
            header('Content-Type: text/html; charset=utf-8');
        }
        
        require $fullPath;
        exit;
    }
}

http_response_code(404);
$notFoundFile = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . '404.php');
if ($notFoundFile && file_exists($notFoundFile)) {
    require $notFoundFile;
} else {
    echo '<!DOCTYPE html><html><head><title>404</title></head><body><h1>404 Não Encontrado</h1></body></html>';
}
exit;
