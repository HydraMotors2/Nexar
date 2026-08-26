<?php require_once __DIR__ . '/../config/config.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include __DIR__ . '/../components/favicon.php'; ?>
    <title>Entrar - NEXAR</title>
    <meta name="description" content="Acesse sua conta NEXAR">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="/NEXAR/public/css/variables.css">
    <link rel="stylesheet" href="/NEXAR/public/css/global.css">
    <link rel="stylesheet" href="/NEXAR/public/css/components.css">
    <link rel="stylesheet" href="/NEXAR/public/css/animations.css">
    
    <style>
        .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-8);
        }
        
        .auth-container {
            width: 100%;
            max-width: 440px;
        }
        
        .auth-card {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-2xl);
            padding: var(--space-10);
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: var(--space-8);
        }
        
        .auth-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
            margin-bottom: var(--space-6);
            font-size: var(--text-2xl);
            font-weight: var(--font-bold);
            color: var(--color-white);
            text-decoration: none;
        }
        
        .auth-logo svg {
            width: 40px;
            height: 40px;
            color: var(--color-primary);
        }
        
        .auth-title {
            font-size: var(--text-2xl);
            font-weight: var(--font-bold);
            color: var(--color-white);
            margin-bottom: var(--space-2);
        }
        
        .auth-subtitle {
            font-size: var(--text-sm);
            color: var(--color-gray-400);
        }
        
        .auth-form {
            display: flex;
            flex-direction: column;
            gap: var(--space-4);
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: var(--space-2);
        }
        
        .form-label {
            font-size: var(--text-sm);
            font-weight: var(--font-medium);
            color: var(--color-gray-300);
        }
        
        .form-input-wrapper {
            position: relative;
        }
        
        .form-input-icon {
            position: absolute;
            left: var(--space-4);
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            color: var(--color-gray-500);
            pointer-events: none;
        }
        
        .form-input {
            width: 100%;
            padding: var(--space-3) var(--space-4);
            padding-left: var(--space-12);
            font-family: var(--font-primary);
            font-size: var(--text-base);
            color: var(--color-white);
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg);
            transition: all var(--transition-base);
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px var(--color-primary-subtle);
        }
        
        .form-input.error {
            border-color: var(--color-error);
        }
        
        .form-error {
            font-size: var(--text-xs);
            color: var(--color-error);
            margin-top: var(--space-1);
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: var(--text-sm);
        }
        
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            cursor: pointer;
        }
        
        .checkbox-wrapper input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--color-primary);
            cursor: pointer;
        }
        
        .checkbox-label {
            color: var(--color-gray-400);
            user-select: none;
        }
        
        .auth-link {
            color: var(--color-primary);
            text-decoration: none;
            transition: color var(--transition-fast);
        }
        
        .auth-link:hover {
            color: var(--color-primary-light);
        }
        
        .auth-divider {
            display: flex;
            align-items: center;
            gap: var(--space-4);
            margin: var(--space-6) 0;
        }
        
        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--glass-border);
        }
        
        .auth-divider span {
            font-size: var(--text-xs);
            color: var(--color-gray-500);
            text-transform: uppercase;
            letter-spacing: var(--tracking-wide);
        }

        .debug-message {
            display: none;
            margin-top: var(--space-4);
            padding: var(--space-4);
            border-radius: var(--radius-lg);
            background: rgba(255, 69, 58, 0.12);
            color: var(--color-error);
            font-size: var(--text-sm);
            line-height: 1.5;
            white-space: pre-wrap;
            word-break: break-word;
        }
        
        .auth-footer {
            text-align: center;
            margin-top: var(--space-6);
            font-size: var(--text-sm);
            color: var(--color-gray-400);
        }
        
        .password-toggle {
            position: absolute;
            right: var(--space-4);
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--color-gray-500);
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .password-toggle:hover {
            color: var(--color-gray-300);
        }
        
        .notification {
            position: fixed;
            top: var(--space-6);
            right: var(--space-6);
            padding: var(--space-4) var(--space-5);
            background: var(--color-black-light);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            z-index: var(--z-toast);
            transform: translateX(120%);
            transition: transform var(--transition-base);
            max-width: 400px;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.success {
            border-color: var(--color-secondary);
        }
        
        .notification.error {
            border-color: var(--color-error);
        }
        
        .notification-title {
            font-weight: var(--font-semibold);
            color: var(--color-white);
            margin-bottom: var(--space-1);
        }
        
        .notification-message {
            font-size: var(--text-sm);
            color: var(--color-gray-400);
        }
    </style>
</head>
<body class="bg-matte">
    <!-- Animated Background -->
    <div class="bg-animation" aria-hidden="true">
        <div class="bg-gradient-orb orb-1"></div>
        <div class="bg-gradient-orb orb-2"></div>
    </div>
    
    <!-- Notification -->
    <div id="notification" class="notification" role="alert" aria-live="polite">
        <div class="notification-title" id="notification-title"></div>
        <div class="notification-message" id="notification-message"></div>
    </div>
    
    <main class="auth-page">
        <div class="auth-container">
            <div class="auth-card">
                <!-- Header -->
                <div class="auth-header">
                    <a href="/NEXAR/" class="auth-logo">
                        <svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 2L32 10V26L18 34L4 26V10L18 2Z" stroke="currentColor" stroke-width="2" fill="none"/>
                            <path d="M18 8L26 13V23L18 28L10 23V13L18 8Z" fill="currentColor"/>
                            <circle cx="18" cy="18" r="4" fill="#0A0A0A"/>
                        </svg>
                        NEXAR
                    </a>
                    <h1 class="auth-title">Bem-vindo de volta</h1>
                    <p class="auth-subtitle">Faça login para continuar na sua conta</p>
                </div>
                
                <!-- Login Form -->
                <form id="loginForm" class="auth-form" novalidate>
                    <input type="hidden" name="csrf_token" id="csrf_token" value="">
                    
                    <div class="form-group">
                        <label for="identifier" class="form-label">E-mail ou UUID</label>
                        <div class="form-input-wrapper">
                            <svg class="form-input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <input type="text" id="identifier" name="identifier" class="form-input" placeholder="voce@exemplo.com ou UUID" required autocomplete="username">
                        </div>
                        <span class="form-error" id="identifier-error"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Senha</label>
                        <div class="form-input-wrapper">
                            <svg class="form-input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <input type="password" id="password" name="password" class="form-input" placeholder="Digite sua senha" required autocomplete="current-password">
                            <button type="button" class="password-toggle" id="passwordToggle" aria-label="Toggle password visibility">
                                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                        <span class="form-error" id="password-error"></span>
                    </div>
                    
                    <div class="form-options">
                        <label class="checkbox-wrapper">
                            <input type="checkbox" name="remember" id="remember">
                            <span class="checkbox-label">Lembrar-me</span>
                        </label>
                        <a href="/forgot-password" class="auth-link">Esqueceu a senha?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg w-full" id="submitBtn">
                        <span id="submitText">Entrar</span>
                        <span id="submitSpinner" class="spinner spinner-sm" style="display: none;"></span>
                    </button>
                </form>

                <div id="debugMessage" class="debug-message" style="display: none;"></div>
                
                <!-- Footer -->
                <div class="auth-footer">
                    Ainda não tem uma conta? <a href="/NEXAR/register" class="auth-link">Cadastre-se</a>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        // CSRF Token
        document.getElementById('csrf_token').value = getCookie('csrf_token') || '';
        
        // Password toggle
        const passwordToggle = document.getElementById('passwordToggle');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        
        passwordToggle.addEventListener('click', () => {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            
            if (type === 'text') {
                eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        });
        
        // Form validation
        const loginForm = document.getElementById('loginForm');
        
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        function validateUuid(value) {
            const re = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
            return re.test(value);
        }
        
        function showError(field, message) {
            const input = document.getElementById(field);
            const error = document.getElementById(field + '-error');
            input.classList.add('error');
            error.textContent = message;
        }
        
        function clearError(field) {
            const input = document.getElementById(field);
            const error = document.getElementById(field + '-error');
            input.classList.remove('error');
            error.textContent = '';
        }
        
        function showNotification(type, title, message) {
            const notification = document.getElementById('notification');
            const titleEl = document.getElementById('notification-title');
            const messageEl = document.getElementById('notification-message');
            
            notification.className = 'notification ' + type;
            titleEl.textContent = title;
            messageEl.textContent = message;
            
            setTimeout(() => notification.classList.add('show'), 10);
            setTimeout(() => notification.classList.remove('show'), 5000);
        }
        
        function getCookie(name) {
            const value = '; ' + document.cookie;
            const parts = value.split('; ' + name + '=');
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }
        
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const identifier = document.getElementById('identifier').value.trim();
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;
            const csrfToken = document.getElementById('csrf_token').value;
            
            // Clear previous errors
            clearError('identifier');
            clearError('password');
            
            // Validate
            let hasError = false;
            
            if (!identifier) {
                showError('identifier', 'E-mail ou UUID é obrigatório');
                hasError = true;
            } else if (!validateEmail(identifier) && !validateUuid(identifier)) {
                showError('identifier', 'Por favor, insira um e-mail ou UUID válido');
                hasError = true;
            }
            
            if (!password) {
                showError('password', 'Senha é obrigatória');
                hasError = true;
            } else if (password.length < 8) {
                showError('password', 'A senha deve ter no mínimo 8 caracteres');
                hasError = true;
            }
            
            if (hasError) return;
            
            // Submit
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitSpinner = document.getElementById('submitSpinner');
            
            submitBtn.disabled = true;
            submitText.style.display = 'none';
            submitSpinner.style.display = 'inline-block';
            
            try {
                const API_BASE = '<?php echo API_PREFIX; ?>';
                const APP_BASE = API_BASE.replace(/\/api\/?$/, '');
                const response = await fetch(API_BASE + '/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify({ identifier, password, remember })
                });
                
                const data = await response.json();
                console.log('Login response:', response.status, response.statusText, data);
                
                if (response.ok && data.success) {
                    showNotification('success', 'Bem-vindo de volta!', 'Redirecionando para a página inicial...');
                    setTimeout(() => {
                        const redirect = '/';
                        if (redirect.startsWith('/')) {
                            window.location.href = APP_BASE + redirect;
                        } else {
                            window.location.href = redirect;
                        }
                    }, 1000);
                } else {
                    const debugEl = document.getElementById('debugMessage');
                    const errorDetails = data.errors ? ` | errors: ${JSON.stringify(data.errors)}` : '';
                    const debugText = data.message ? `Erro de login: ${data.message}${errorDetails}` : `Erro de login: resposta inválida (status ${response.status} ${response.statusText})`;
                    debugEl.textContent = debugText;
                    debugEl.style.display = 'block';
                    console.error('Login falhou:', response.status, response.statusText, data);

                    showNotification('error', 'Falha no login', data.message || 'Credenciais inválidas');
                    submitBtn.disabled = false;
                    submitText.style.display = 'inline';
                    submitSpinner.style.display = 'none';
                }
            } catch (error) {
                const debugEl = document.getElementById('debugMessage');
                debugEl.textContent = `Erro de rede ou exceção: ${error.message || error}`;
                debugEl.style.display = 'block';
                console.error('Erro de login:', error);

                showNotification('error', 'Erro', 'Ocorreu um erro. Tente novamente.');
                submitBtn.disabled = false;
                submitText.style.display = 'inline';
                submitSpinner.style.display = 'none';
            }
        });
        
        // Clear errors on input
        document.getElementById('email').addEventListener('input', () => clearError('email'));
        document.getElementById('password').addEventListener('input', () => clearError('password'));
    </script>
</body>
</html>