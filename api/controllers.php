<?php
if (!class_exists('AuthController')) {
    class AuthController {
        public function register(array $params): void {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) ApiResponse::validationError(['body' => 'Invalid JSON body']);

            $result = auth()->register($input);
            if ($result['success']) {
                ApiResponse::success(['user' => auth()->user(), 'token' => $_SESSION[CSRF_TOKEN_NAME] ?? null], 'Registration successful', 201);
            }
            ApiResponse::validationError($result['errors'] ?? ['unknown' => 'Registration failed']);
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
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['token'])) ApiResponse::validationError(['token' => 'Verification token is required']);

            $result = auth()->verifyEmail($input['token']);
            if ($result['success']) ApiResponse::success(null, 'Email verified successfully');
            ApiResponse::error($result['errors']['general'] ?? 'Verification failed');
        }
    }
}
