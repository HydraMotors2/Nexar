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
    
    <link rel="stylesheet" href="/NEXAR/public/css/auth.css">
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
                        <span id="submitSpinner" class="spinner spinner-sm spinner-hidden"></span>
                    </button>
                </form>

                <div id="debugMessage" class="debug-message"></div>
                <?php if (is_email_verification_enabled()): ?>
                    <div class="email-resend">
                        <button type="button" id="resendVerificationBtn" class="auth-link">Não recebeu o e-mail de confirmação?</button>
                        <p id="resendVerificationStatus" class="email-resend-status" role="status" aria-live="polite"></p>
                    </div>
                <?php endif; ?>
                
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
        document.getElementById('identifier').addEventListener('input', () => clearError('identifier'));
        document.getElementById('password').addEventListener('input', () => clearError('password'));

        const resendVerificationBtn = document.getElementById('resendVerificationBtn');
        if (resendVerificationBtn) {
            resendVerificationBtn.addEventListener('click', async () => {
                const email = document.getElementById('identifier').value.trim();
                const status = document.getElementById('resendVerificationStatus');

                if (!validateEmail(email)) {
                    status.textContent = 'Digite seu e-mail no campo acima para solicitar o reenvio.';
                    document.getElementById('identifier').focus();
                    return;
                }

                resendVerificationBtn.disabled = true;
                status.textContent = 'Solicitando novo link...';
                try {
                    const response = await fetch('<?php echo API_PREFIX; ?>/auth/resend-verification', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                        body: JSON.stringify({ email })
                    });
                    const result = await response.json();
                    status.textContent = response.ok
                        ? result.message
                        : (result.message || 'Não foi possível solicitar o reenvio agora. Tente novamente em um minuto.');
                } catch (error) {
                    status.textContent = 'Não foi possível conectar ao servidor. Tente novamente.';
                } finally {
                    resendVerificationBtn.disabled = false;
                }
            });
        }
    </script>
</body>
</html>