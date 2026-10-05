<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../php/auth.php';

$message = '';
$verified = false;
$deliveryFailed = ($_GET['sent'] ?? '') === '0';

if (isset($_GET['token']) && is_string($_GET['token'])) {
    if (!is_email_verification_enabled()) {
        $message = 'A confirmação de e-mail está desativada no momento.';
    } else {
        $result = Auth::getInstance()->verifyEmail($_GET['token']);
        $verified = $result['success'];
        $message = $verified
            ? 'E-mail confirmado. Sua conta está pronta para entrar.'
            : 'Este link é inválido ou expirou. Solicite um novo link de confirmação.';
    }
} elseif ($deliveryFailed) {
    $message = 'Não foi possível enviar o e-mail agora. Você pode solicitar um novo link abaixo.';
} elseif (($_GET['sent'] ?? '') === '1') {
    $message = 'Cadastro concluído. Confira sua caixa de entrada e a pasta de spam para confirmar seu e-mail.';
} elseif (!is_email_verification_enabled()) {
    $message = 'A confirmação de e-mail está desativada.';
} else {
    $message = 'Use o link enviado ao seu e-mail para confirmar sua conta.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar e-mail - NEXAR</title>
    <link rel="stylesheet" href="/NEXAR/public/css/variables.css">
    <link rel="stylesheet" href="/NEXAR/public/css/global.css">
    <style>
        body { min-height: 100vh; display: grid; place-items: center; padding: 24px; }
        .verification-panel { width: min(100%, 460px); padding: 32px; border: 1px solid var(--glass-border); border-radius: 12px; background: rgba(20, 24, 22, .96); }
        .verification-panel h1 { margin: 0 0 12px; color: var(--color-white); font-size: 24px; }
        .verification-panel p { color: var(--color-gray-300); line-height: 1.6; }
        .verification-panel form { display: grid; gap: 12px; margin-top: 24px; }
        .verification-panel input { width: 100%; padding: 12px; border: 1px solid var(--glass-border); border-radius: 8px; background: rgba(255,255,255,.04); color: var(--color-white); }
        .verification-panel button, .verification-panel a { display: inline-block; margin-top: 12px; }
        .verification-panel button { padding: 12px 16px; border: 0; border-radius: 8px; background: var(--color-primary); color: #101513; font-weight: 700; cursor: pointer; }
        #resendMessage { min-height: 24px; }
    </style>
</head>
<body class="bg-matte">
    <main class="verification-panel">
        <h1><?= $verified ? 'E-mail confirmado' : 'Confirme seu e-mail' ?></h1>
        <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
        <?php if (is_email_verification_enabled() && !$verified): ?>
            <form id="resendForm">
                <label for="email">E-mail da conta</label>
                <input id="email" name="email" type="email" autocomplete="email" required>
                <button type="submit">Reenviar link</button>
                <p id="resendMessage" role="status" aria-live="polite"></p>
            </form>
        <?php endif; ?>
        <a href="/NEXAR/login">Ir para entrar</a>
    </main>
    <?php if (is_email_verification_enabled() && !$verified): ?>
        <script>
            document.getElementById('resendForm').addEventListener('submit', async (event) => {
                event.preventDefault();
                const message = document.getElementById('resendMessage');
                const email = document.getElementById('email').value;
                try {
                    const response = await fetch('/NEXAR/api/auth/resend-verification', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                        body: JSON.stringify({ email })
                    });
                    const result = await response.json();
                    message.textContent = response.ok
                        ? result.message
                        : 'Não foi possível solicitar outro link agora. Aguarde um minuto e tente novamente.';
                } catch {
                    message.textContent = 'Não foi possível conectar ao servidor. Tente novamente.';
                }
            });
        </script>
    <?php endif; ?>
</body>
</html>