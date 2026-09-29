<?php
/**
 * NEXAR - Main Configuration
 * Application settings and constants
 */

// Prevent direct access
defined('NEXAR_APP') or define('NEXAR_APP', true);
require_once dirname(__DIR__) . '/payment_mode.php';

// Application Environment
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', getenv('APP_DEBUG') ?: true);
define('APP_NAME', getenv('APP_NAME') ?: 'NEXAR');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');
define('APP_VERSION', '1.0.0');

// Paths
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('COMPONENTS_PATH', BASE_PATH . '/components');
define('PAGES_PATH', BASE_PATH . '/pages');
define('API_PATH', BASE_PATH . '/api');
define('CONFIG_PATH', BASE_PATH . '/config');
define('LOGS_PATH', BASE_PATH . '/logs');

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'nexar');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');
define('DB_PATH', BASE_PATH . '/database.db');

// Session Configuration
define('SESSION_NAME', 'nexar_session');
define('SESSION_LIFETIME', 3600 * 24); // 24 hours
define('SESSION_SECURE', false); // Set to true in production with HTTPS
define('SESSION_HTTPONLY', true);

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('HASH_COST', 12); // Bcrypt cost
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes

// API Configuration
// Adjust API prefix to include the application subfolder so routing works when
// the app is hosted at /NEXAR on the local server.
define('API_PREFIX', '/NEXAR/api');
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 100); // Requests per minute
define('API_KEY_LENGTH', 32);

// JWT Configuration (if used)
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'change-me-in-production');
define('JWT_ALGO', 'HS256');
define('JWT_ISSUER', 'nexar');
define('JWT_AUDIENCE', 'nexar-users');
define('JWT_TTL', 3600); // 1 hour

// Upload Configuration
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'application/pdf']);

// Email Configuration
define('MAIL_HOST', getenv('MAIL_HOST') ?: 'smtp.mailtrap.io');
define('MAIL_PORT', getenv('MAIL_PORT') ?: 587);
define('MAIL_USER', getenv('MAIL_USER') ?: '');
define('MAIL_PASS', getenv('MAIL_PASS') ?: '');
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_FROM_ADDRESS', 'noreply@nexar.com');
define('MAIL_FROM_NAME', 'NEXAR');

// Pagination
define('PER_PAGE', 20);
define('MAX_PER_PAGE', 100);

// Timezone
date_default_timezone_set('UTC');

// Error Reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set default timezone
ini_set('date.timezone', 'UTC');

// Autoload composer if exists
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

/**
 * Helper function to get environment variable
 */
function env($key, $default = null) {
    $value = getenv($key);
    return $value === false ? $default : $value;
}

/**
 * Helper function to check if app is in debug mode
 */
function is_debug(): bool {
    return (bool) APP_DEBUG;
}

/**
 * Helper function to check if app is in production
 */
function is_production(): bool {
    return APP_ENV === 'production';
}

/**
 * Helper function to get base URL
 */
function base_url(string $path = ''): string {
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Helper function to get asset URL
 */
function asset(string $path): string {
    return base_url('public/' . ltrim($path, '/'));
}

function is_payment_test_mode(): bool {
    return PAYMENT_TEST_MODE;
}

function get_plan_catalog(): array {
    return [
        'entrepreneur' => [
            'free' => ['label' => 'Gratuito', 'price' => 0, 'description' => 'Perfil essencial para testar a plataforma'],
            'starter' => ['label' => 'Micro', 'price' => 25, 'description' => 'Mais visibilidade e recursos básicos'],
            'growth' => ['label' => 'Pequena empresa', 'price' => 50, 'description' => 'Melhor presença para captar oportunidades'],
        ],
        'supplier' => [
            'account' => ['label' => 'Conta', 'price' => 50, 'description' => 'Acesso para criar a conta e aparecer no marketplace'],
            'boost' => ['label' => 'Destaque', 'price' => 100, 'description' => 'Aparece mais vezes nas pesquisas e em destaque'],
            'promoted' => ['label' => 'Promoção Premium', 'price' => 200, 'description' => 'Melhor posicionamento e ampla visibilidade'],
        ],
    ];
}

function normalize_plan_key(string $accountType, string $plan): string {
    $catalog = get_plan_catalog();
    $allowed = $catalog[$accountType] ?? [];
    $plan = strtolower(trim($plan));

    if ($plan === '' || !isset($allowed[$plan])) {
        return array_key_first($allowed) ?: 'free';
    }

    return $plan;
}

function plan_label(string $accountType, string $plan): string {
    $catalog = get_plan_catalog();
    $plan = normalize_plan_key($accountType, $plan);
    return $catalog[$accountType][$plan]['label'] ?? ucfirst($plan);
}

function plan_price(string $accountType, string $plan): int {
    $catalog = get_plan_catalog();
    $plan = normalize_plan_key($accountType, $plan);
    return (int) ($catalog[$accountType][$plan]['price'] ?? 0);
}

function can_create_profile_without_payment(string $accountType, string $plan): bool {
    if (is_payment_test_mode()) {
        return true;
    }

    $normalizedPlan = normalize_plan_key($accountType, $plan);

    return in_array($normalizedPlan, ['free', 'account'], true);
}

function normalizeCnpj(string $cnpj): string {
    return preg_replace('/\D+/', '', $cnpj) ?? '';
}

function formatCnpj(string $cnpj): string {
    $digits = normalizeCnpj($cnpj);
    if (strlen($digits) !== 14) return $cnpj;

    return substr($digits, 0, 2) . '.' . substr($digits, 2, 3) . '.'
        . substr($digits, 5, 3) . '/' . substr($digits, 8, 4) . '-'
        . substr($digits, 12, 2);
}

function isValidCnpj(string $cnpj): bool {
    $digits = normalizeCnpj($cnpj);
    if (strlen($digits) !== 14 || preg_match('/^(\d)\1{13}$/', $digits)) return false;

    $weights = [
        [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
        [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
    ];

    foreach ($weights as $round => $roundWeights) {
        $sum = 0;
        $length = 12 + $round;
        for ($index = 0; $index < $length; $index++) {
            $sum += (int)$digits[$index] * $roundWeights[$index];
        }
        $remainder = $sum % 11;
        $digit = $remainder < 2 ? 0 : 11 - $remainder;
        if ((int)$digits[$length] !== $digit) return false;
    }

    return true;
}

function lookupCnpj(string $cnpj): array {
    $digits = normalizeCnpj($cnpj);
    $result = [
        'valid' => isValidCnpj($digits),
        'exists' => false,
        'company_name' => null,
        'error' => null,
        'digits' => $digits,
        'api_status' => null,
        'api_response' => null,
    ];

    if (!$result['valid']) {
        $result['error'] = 'Informe um CNPJ válido com 14 dígitos.';
        return $result;
    }

    if (is_payment_test_mode()) {
        $result['exists'] = true;
        $result['company_name'] = 'Cadastro em modo de teste';
        $result['api_status'] = 200;
        $result['api_response'] = ['mode' => 'manual_test'];
        return $result;
    }

    $url = 'https://brasilapi.com.br/api/cnpj/v1/' . rawurlencode($digits);
    $response = false;
    $statusCode = 0;

    if (function_exists('curl_init')) {
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ]);
    $response = curl_exec($curl);
    $statusCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if ($response === false) {
        error_log('BrasilAPI cURL error: ' . curl_error($curl) . ' (errno ' . curl_errno($curl) . ')');
    }

    curl_close($curl);
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 8,
                'ignore_errors' => true,
                'header' => "Accept: application/json\r\n",
            ],
        ]);
        $response = @file_get_contents($url, false, $context);
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches)) {
            $statusCode = (int)$matches[1];
        }
    }

    $payload = is_string($response) ? json_decode($response, true) : null;
    $result['api_status'] = $statusCode;
    $result['api_response'] = is_array($payload) ? $payload : null;

  if ($statusCode >= 200 && $statusCode < 300 && is_array($payload)) {
    $result['exists'] = true;
    $result['company_name'] = $payload['razao_social'] ?? $payload['nome_fantasia'] ?? null;
    return $result;
}

if ($statusCode === 404) {
    $result['error'] = 'CNPJ não encontrado na BrasilAPI.';
} elseif ($statusCode === 429) {
    $result['error'] = 'A BrasilAPI limitou as consultas. Aguarde alguns minutos e tente novamente.';
} elseif ($statusCode === 0) {
    $result['error'] = 'Não foi possível conectar à BrasilAPI. Verifique a conexão do servidor.';
} else {
    $result['error'] = 'Não foi possível consultar o CNPJ agora (status ' . $statusCode . '). Tente novamente.';
}
return $result;
}