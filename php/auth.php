<?php
/**
 * NEXAR - Authentication Class
 * User authentication and authorization
 */

defined('NEXAR_APP') or define('NEXAR_APP', true);

if (file_exists(__DIR__ . '/../config/config.php')) {
    require_once __DIR__ . '/../config/config.php';
}

require_once __DIR__ . '/database.php';

class Auth {
    private static ?Auth $instance = null;
    private ?array $user = null;
    private bool $checked = false;

    /**
     * Private constructor for singleton pattern
     */
    private function __construct() {
        $this->startSession();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): Auth {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Start or resume session
     */
    private function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        }
    }

    /**
     * Register a new user
     */
    public function register(array $data): array {
        $errors = $this->validateRegistration($data);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Check if email already exists
        $db = Database::getInstance();
        $existing = $db->select('users', 'id', 'email = :email', ['email' => $data['email']]);
        
        if (!empty($existing)) {
            return ['success' => false, 'errors' => ['email' => 'Email already registered']];
        }

        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => HASH_COST]);

        // Parse full name into first/last pieces
        $fullName = trim($data['full_name'] ?? ($data['first_name'] . ' ' . $data['last_name'] ?? ''));
        $nameParts = preg_split('/\s+/', $fullName);
        $firstName = $nameParts[0] ?? '';
        $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : $firstName;

        $accountType = in_array($data['account_type'] ?? '', ['entrepreneur', 'supplier'], true) ? $data['account_type'] : 'entrepreneur';
        $role = $accountType === 'supplier' ? 'provider' : 'client';

        // Insert user
        $userId = $db->insert('users', [
            'uuid' => $this->generateUuid(),
            'email' => $data['email'],
            'password' => $hashedPassword,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'account_type' => $accountType,
            'role' => $role,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Generate email verification token
        $token = bin2hex(random_bytes(32));
        $db->insert('email_verifications', [
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours')),
        ]);

        // Send verification email
        $this->sendVerificationEmail($userId, $token);

        // Auto login
        $this->login($data['email'], $data['password']);

        return ['success' => true, 'user_id' => $userId];
    }

    /**
     * Login user
     */
    public function login(string $identifier, string $password, bool $remember = false): array {
        // Check for lockout
        if ($this->isLockedOut($identifier)) {
            return ['success' => false, 'errors' => ['general' => 'Too many failed attempts. Please try again later.']];
        }

        $db = Database::getInstance();
        $user = $db->select('users', '*', '(email = :identifier OR uuid = :identifier)', ['identifier' => $identifier]);

        if (empty($user)) {
            $this->recordFailedAttempt($identifier);
            return ['success' => false, 'errors' => ['general' => 'Invalid credentials']];
        }

        $user = $user[0];

        $passwordInfo = password_get_info($user['password']);
        $isHash = $passwordInfo['algo'] !== 0;
        $passwordMatches = false;

        if ($isHash) {
            $passwordMatches = password_verify($password, $user['password']);
        } else {
            $passwordMatches = hash_equals($user['password'], $password);
        }

        if (!$passwordMatches) {
            $this->recordFailedAttempt($user['email']);
            return ['success' => false, 'errors' => ['general' => 'Invalid credentials']];
        }

        if (!$isHash || password_needs_rehash($user['password'], PASSWORD_BCRYPT, ['cost' => HASH_COST])) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
            $db->update('users', [
                'password' => $hashedPassword,
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'id = :id', ['id' => $user['id']]);
            $user['password'] = $hashedPassword;
        }

        if ($user['status'] === 'banned') {
            return ['success' => false, 'errors' => ['general' => 'Account has been suspended']];
        }

        // Clear failed attempts
        $this->clearFailedAttempts($user['email']);

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_uuid'] = $user['uuid'];
        $_SESSION['user_name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['account_type'] = $user['account_type'] ?? 'entrepreneur';
        $_SESSION['logged_in'] = true;

        // Update last login
        $db->update('users', [
            'last_login_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $user['id']]);

        // Remember me functionality
        if ($remember) {
            $this->setRememberToken($user['id']);
        }

        $this->user = $user;
        $this->checked = true;

        return ['success' => true, 'user' => $user];
    }

    /**
     * Logout user
     */
    public function logout(): void {
        // Clear remember token
        if (isset($_COOKIE['remember_token'])) {
            $this->clearRememberToken();
        }

        // Destroy session
        $_SESSION = [];
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();

        $this->user = null;
        $this->checked = false;
    }

    /**
     * Check if user is authenticated
     */
    public function check(): bool {
        if ($this->checked) {
            return $this->user !== null;
        }

        $this->checked = true;

        // Check session
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            $db = Database::getInstance();
            $user = $db->select('users', '*', 'id = :id', ['id' => $_SESSION['user_id']]);
            
            if (!empty($user)) {
                $this->user = $user[0];
                $_SESSION['user_id'] = $this->user['id'];
                $_SESSION['user_name'] = trim(($this->user['first_name'] ?? '') . ' ' . ($this->user['last_name'] ?? ''));
                $_SESSION['account_type'] = $this->user['account_type'] ?? 'entrepreneur';
                return true;
            }
        }

        // Check remember token
        if (isset($_COOKIE['remember_token'])) {
            $user = $this->validateRememberToken($_COOKIE['remember_token']);
            if ($user) {
                $this->user = $user;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['account_type'] = $user['account_type'] ?? 'entrepreneur';
                $_SESSION['logged_in'] = true;
                return true;
            }
        }

        return false;
    }

    /**
     * Get authenticated user
     */
    public function user(): ?array {
        if (!$this->check()) {
            return null;
        }
        return $this->user;
    }

    /**
     * Get user ID
     */
    public function id(): ?int {
        $user = $this->user();
        return $user['id'] ?? null;
    }

    /**
     * Check if user has role
     */
    public function hasRole(string $role): bool {
        $user = $this->user();
        return $user && $user['role'] === $role;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool {
        return $this->hasRole('admin');
    }

    /**
     * Validate registration data
     */
    private function validateRegistration(array $data): array {
        $errors = [];

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email is required';
        }

        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        $fullName = trim($data['full_name'] ?? (($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')));
        if (empty($fullName)) {
            $errors['full_name'] = 'Full name is required';
        }

        if (!empty($data['account_type']) && !in_array($data['account_type'], ['entrepreneur', 'supplier'], true)) {
            $errors['account_type'] = 'Account type must be entrepreneur or supplier';
        }

        return $errors;
    }

    /**
     * Generate UUID v4 string
     */
    private function generateUuid(): string {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Check if IP is locked out
     */
    private function isLockedOut(string $email): bool {
        $key = 'login_attempts_' . md5($email);
        $attempts = $_SESSION[$key] ?? 0;
        $lockoutTime = $_SESSION[$key . '_time'] ?? 0;

        if ($attempts >= MAX_LOGIN_ATTEMPTS && (time() - $lockoutTime) < LOCKOUT_TIME) {
            return true;
        }

        // Reset if lockout time expired
        if ($attempts >= MAX_LOGIN_ATTEMPTS && (time() - $lockoutTime) >= LOCKOUT_TIME) {
            unset($_SESSION[$key], $_SESSION[$key . '_time']);
        }

        return false;
    }

    /**
     * Record failed login attempt
     */
    private function recordFailedAttempt(string $email): void {
        $key = 'login_attempts_' . md5($email);
        $_SESSION[$key] = ($_SESSION[$key] ?? 0) + 1;
        $_SESSION[$key . '_time'] = time();
    }

    /**
     * Clear failed login attempts
     */
    private function clearFailedAttempts(string $email): void {
        $key = 'login_attempts_' . md5($email);
        unset($_SESSION[$key], $_SESSION[$key . '_time']);
    }

    /**
     * Set remember me token
     */
    private function setRememberToken(int $userId): void {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        
        $db = Database::getInstance();
        $db->insert('remember_tokens', [
            'user_id' => $userId,
            'token' => $hashedToken,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        setcookie('remember_token', $token, time() + (30 * 24 * 3600), '/', '', SESSION_SECURE, true);
    }

    /**
     * Validate remember token
     */
    private function validateRememberToken(string $token): ?array {
        $hashedToken = hash('sha256', $token);
        $db = Database::getInstance();
        
        $record = $db->select('remember_tokens r', 'r.*', 
            'r.token = :token AND r.expires_at > CURRENT_TIMESTAMP', 
            ['token' => $hashedToken]
        );

        if (empty($record)) {
            return null;
        }

        $record = $record[0];
        $user = $db->select('users', '*', 'id = :id', ['id' => $record['user_id']]);

        return !empty($user) ? $user[0] : null;
    }

    /**
     * Clear remember token
     */
    private function clearRememberToken(): void {
        if (isset($_COOKIE['remember_token'])) {
            $hashedToken = hash('sha256', $_COOKIE['remember_token']);
            $db = Database::getInstance();
            $db->delete('remember_tokens', 'token = :token', ['token' => $hashedToken]);
        }
        
        setcookie('remember_token', '', time() - 3600, '/', '', SESSION_SECURE, true);
    }

    /**
     * Send verification email
     */
    private function sendVerificationEmail(int $userId, string $token): void {
        // Implementation for sending verification email
        // This would use a mail service like SendGrid, Mailgun, etc.
        $verificationUrl = base_url("verify-email?token=$token");
        
        // Log for development
        error_log("Verification URL for user $userId: $verificationUrl");
    }

    /**
     * Verify email
     */
    public function verifyEmail(string $token): array {
        $db = Database::getInstance();
        
        $verification = $db->select('email_verifications', '*', 
            'token = :token AND expires_at > CURRENT_TIMESTAMP', 
            ['token' => $token]
        );

        if (empty($verification)) {
            return ['success' => false, 'errors' => ['general' => 'Invalid or expired token']];
        }

        $verification = $verification[0];
        
        // Update user
        $db->update('users', [
            'email_verified' => 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $verification['user_id']]);

        // Delete verification record
        $db->delete('email_verifications', 'id = :id', ['id' => $verification['id']]);

        return ['success' => true];
    }

    /**
     * Generate CSRF token
     */
    public static function csrfToken(): string {
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrf(string $token): bool {
        return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new Exception('Cannot unserialize singleton');
    }
}

// Helper function
function auth(): Auth {
    return Auth::getInstance();
}