<?php
define('NEXAR_APP', true);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../php/database.php';
require_once __DIR__ . '/../php/auth.php';

// Headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Error handling: convert warnings/notices to exceptions and ensure JSON output
ob_start();
set_error_handler(function ($severity, $message, $file, $line) {
    if (error_reporting() === 0) return false; // respect @
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function ($e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    $payload = ['success' => false, 'message' => 'Internal Server Error'];
    if (function_exists('is_debug') && is_debug()) {
        $payload['error'] = $e->getMessage();
        $payload['trace'] = $e->getTraceAsString();
    }
    echo json_encode($payload, JSON_PRETTY_PRINT);
    error_log((string)$e);
    while (ob_get_level() > 0) ob_end_flush();
    exit;
});

// Quick APIResponse helper
class ApiResponse {
    public static function success($data = null, string $message = 'Success', int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'message' => $message, 'data' => $data], JSON_PRETTY_PRINT);
        exit;
    }

    public static function error(string $message = 'Error', int $statusCode = 400, $errors = null): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $message, 'errors' => $errors], JSON_PRETTY_PRINT);
        exit;
    }

    public static function unauthorized(string $message = 'Unauthorized'): void { self::error($message, 401); }
    public static function forbidden(string $message = 'Forbidden'): void { self::error($message, 403); }
    public static function notFound(string $message = 'Resource not found'): void { self::error($message, 404); }
    public static function validationError(array $errors): void { self::error('Validation failed', 422, $errors); }
}

// Route handling
class APIRouter {
    private array $routes = [];
    private string $basePath = API_PREFIX;

    public function __construct() {
        $this->registerRoutes();
    }

    private function registerRoutes(): void {
        $this->routes['POST']['/auth/register'] = ['AuthController', 'register'];
        $this->routes['POST']['/auth/login'] = ['AuthController', 'login'];
        $this->routes['POST']['/auth/logout'] = ['AuthController', 'logout', 'auth'];
        $this->routes['GET']['/auth/me'] = ['AuthController', 'me', 'auth'];
        $this->routes['POST']['/auth/verify-email'] = ['AuthController', 'verifyEmail'];

        $this->routes['GET']['/dashboard/stats.php'] = ['file', __DIR__ . '/dashboard/stats.php'];
        $this->routes['GET']['/dashboard/revenue.php'] = ['file', __DIR__ . '/dashboard/revenue.php'];
        $this->routes['GET']['/dashboard/activity.php'] = ['file', __DIR__ . '/dashboard/activity.php'];
        $this->routes['GET']['/dashboard/notifications.php'] = ['file', __DIR__ . '/dashboard/notifications.php'];
        $this->routes['GET']['/dashboard/search.php'] = ['file', __DIR__ . '/dashboard/search.php'];
        $this->routes['GET']['/dashboard/messages.php'] = ['file', __DIR__ . '/dashboard/messages.php'];
        $this->routes['POST']['/dashboard/notifications.php'] = ['file', __DIR__ . '/dashboard/notifications.php'];

        // other routes omitted for brevity; controllers.php implements them as needed
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = preg_replace('#^' . preg_quote($this->basePath, '#') . '(?:/' . preg_quote(API_VERSION, '#') . ')?#', '', $path);

        if (empty($path) || $path === '/') {
            ApiResponse::success(['name' => 'NEXAR API', 'version' => API_VERSION, 'endpoints' => array_keys($this->routes[$method] ?? [])], 'Welcome to NEXAR API');
        }

        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';
            if (preg_match($pattern, $path, $matches)) {
                preg_match_all('/\{([^}]+)\}/', $route, $paramNames);
                $params = [];
                foreach ($paramNames[1] as $index => $name) {
                    $params[$name] = $matches[$index + 1] ?? null;
                }

                // Load controllers if not loaded
                if (!class_exists($handler[0])) {
                    $controllersFile = __DIR__ . '/controllers.php';
                    if (file_exists($controllersFile)) require_once $controllersFile;
                }

                if (isset($handler[2]) && $handler[2] === 'auth') {
                    if (!auth()->check()) ApiResponse::unauthorized('Authentication required');
                }

                if ($handler[0] === 'file' && isset($handler[1]) && is_file($handler[1])) {
                    require_once $handler[1];
                    return;
                }

                if (class_exists($handler[0]) && method_exists($handler[0], $handler[1])) {
                    $instance = new $handler[0]();
                    call_user_func([$instance, $handler[1]], $params);
                    return;
                }

                ApiResponse::notFound('Controller or action not found');
            }
        }

        ApiResponse::notFound('Endpoint not found');
    }
}

// Handle OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Dispatch with top-level error handling
try {
    $router = new APIRouter();
    $router->dispatch();
} catch (Throwable $e) {
    ApiResponse::error('Internal Server Error', 500, ['error' => $e->getMessage()]);
}
