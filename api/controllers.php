<?php
if (!class_exists('CnpjController')) {
    class CnpjController {
        public function validate(array $params): void {
            if (session_status() === PHP_SESSION_NONE) {
                session_name(SESSION_NAME);
                session_start();
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (!is_array($input) || !isset($input['cnpj']) || !is_string($input['cnpj'])) {
                ApiResponse::validationError(['cnpj' => 'CNPJ is required']);
            }

            $cnpj = $input['cnpj'];
            $digits = normalizeCnpj($cnpj);
            $lookup = lookupCnpj($digits);

            if ($lookup['valid'] && $lookup['exists']) {
                $_SESSION['validated_cnpjs'][$digits] = time();
            }

            ApiResponse::success([
                'valid' => $lookup['valid'],
                'exists' => $lookup['exists'],
                'digits' => $digits,
                'formatted' => formatCnpj($cnpj),
                'company_name' => $lookup['company_name'],
                'error' => $lookup['error'],
                'api_status' => $lookup['api_status'],
                'api_response' => $lookup['api_response'],
            ], 'CNPJ validation completed');
        }
    }
}

if (!class_exists('AuthController')) {
    class AuthController {
        public function register(array $params): void {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) ApiResponse::validationError(['body' => 'Invalid JSON body']);

            $result = auth()->register($input);
            if ($result['success']) {
                if (!empty($result['verification_required'])) {
                    ApiResponse::success([
                        'verification_required' => true,
                        'verification_email_sent' => (bool)($result['verification_email_sent'] ?? false),
                    ], 'Registration created. Check your email to verify the account.', 201);
                }
                ApiResponse::success(['user' => auth()->user(), 'token' => $_SESSION[CSRF_TOKEN_NAME] ?? null], 'Registration successful', 201);
            }
            ApiResponse::validationError($result['errors'] ?? ['unknown' => 'Registration failed']);
        }

        public function resendVerificationEmail(array $params): void {
            $input = json_decode(file_get_contents('php://input'), true);
            $email = is_array($input) ? trim((string)($input['email'] ?? '')) : '';

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                ApiResponse::validationError(['email' => 'Informe um e-mail válido.']);
            }
            if (!is_email_verification_enabled()) {
                ApiResponse::error('Email verification is disabled', 409);
            }

            auth()->resendVerificationEmail($email);
            ApiResponse::success(null, 'Se a conta existir e precisar de confirmação, um novo link será enviado.', 202);
        }

        public function login(array $params): void {
            $input = json_decode(file_get_contents('php://input'), true);
            $identifier = $input['identifier'] ?? $input['email'] ?? null;

            if (!$input || empty($identifier) || empty($input['password'])) {
                ApiResponse::validationError(['identifier' => 'Email or UUID is required', 'password' => 'Password is required']);
            }

            $result = auth()->login($identifier, $input['password'], $input['remember'] ?? false);

            if ($result['success']) {
                $user = $result['user'];
                unset($user['password']);
                ApiResponse::success(['user' => $user, 'csrf_token' => Auth::csrfToken()], 'Login successful');
            }

            ApiResponse::error($result['errors']['general'] ?? 'Login failed', 401);
        }

        public function logout(array $params): void {
            auth()->logout();
            ApiResponse::success(null, 'Logged out successfully');
        }

        public function me(array $params): void {
            $user = auth()->user();
            if ($user) ApiResponse::success(['user' => $user]);
            ApiResponse::unauthorized();
        }

        public function verifyEmail(array $params): void {
            if (!is_email_verification_enabled()) {
                ApiResponse::error('Email verification is disabled', 409);
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['token'])) ApiResponse::validationError(['token' => 'Verification token is required']);

            $result = auth()->verifyEmail($input['token']);
            if ($result['success']) ApiResponse::success(null, 'Email verified successfully');
            ApiResponse::error($result['errors']['general'] ?? 'Verification failed');
        }
    }
}
