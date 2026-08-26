<?php
// Wrapper da página inicial que exibe um menu rápido para usuários autenticados.
$loggedIn = false;
$user = null;
if (file_exists(__DIR__ . '/../php/auth.php')) {
    require_once __DIR__ . '/../php/auth.php';
    $auth = Auth::getInstance();
    if ($auth->check()) {
        $loggedIn = true;
        $user = $auth->user();
    }
}

$indexPath = __DIR__ . '/../index.html';
$html = file_exists($indexPath) ? file_get_contents($indexPath) : '';
if ($html === false || $html === '') {
    http_response_code(500);
    echo 'Unable to load homepage content.';
    exit;
}

if ($loggedIn) {
    $displayName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
    $companyName = $user['legal_name'] ?? $user['company_name'] ?? $user['trade_name'] ?? $user['full_name'] ?? null;
    if ($displayName === '' && $companyName) {
        $displayName = $companyName;
    }
    if ($displayName === '') {
        $displayName = $user['email'] ?? 'Usuário';
    }

    if (!empty($user['id'])) {
        $db = Database::getInstance();
        $accountType = $user['account_type'] ?? null;
        if (!$accountType) {
            $accountType = ($user['role'] ?? '') === 'provider' ? 'supplier' : 'entrepreneur';
        }
        $accountType = in_array($accountType, ['supplier', 'entrepreneur'], true) ? $accountType : 'entrepreneur';
        $tableName = $accountType === 'supplier' ? 'suppliers' : 'entrepreneurs';

        if ($db->tableExists($tableName)) {
            $rows = $db->select($tableName, '*', 'user_id = :id', ['id' => $user['id']]);
            if (!empty($rows)) {
                $profile = $rows[0];
                $displayName = $profile['legal_name'] ?? $profile['company_name'] ?? $profile['trade_name'] ?? $displayName;
            }
        }
    }

    $displayName = htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8');
    $menuHtml = <<<HTML
    <style>
        .logged-in-menu {
            width: min(1120px, 100%);
            margin: 0 auto;
            padding: 24px 24px 0;
        }
        .logged-in-menu-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 28px;
            padding: 28px 32px;
            margin-bottom: 32px;
            box-shadow: 0 24px 60px rgba(0,0,0,0.12);
        }
        .logged-in-menu-card h2 {
            font-size: 1.9rem;
            margin-bottom: 10px;
            color: var(--color-white);
        }
        .logged-in-menu-card p {
            color: var(--color-gray-400);
            margin-bottom: 18px;
        }
        .home-menu-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
        }
        .home-menu-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 18px;
            border-radius: 999px;
            background: rgba(255,255,255,0.06);
            color: var(--color-white);
            text-decoration: none;
            font-weight: 600;
            border: 1px solid transparent;
            transition: all 180ms ease;
        }
        .home-menu-button:hover {
            background: rgba(255,255,255,0.1);
            border-color: rgba(255,255,255,0.12);
        }
        .navbar-actions.logged-in {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }
    </style>
    <div class="logged-in-menu">
        <div class="logged-in-menu-card">
            <h2>Bem-vindo de volta, {$displayName}!</h2>
            <p>Acesse rapidamente suas principais áreas do NEXAR:</p>
            <div class="home-menu-buttons">
                <a class="home-menu-button" href="/NEXAR/my-suppliers">Meus Fornecedores</a>
                <a class="home-menu-button" href="/NEXAR/my-orders">Meus Pedidos</a>
                <a class="home-menu-button" href="/NEXAR/profile">Meu Perfil</a>
                <a class="home-menu-button" href="/NEXAR/logout">Sair</a>
            </div>
        </div>
    </div>
HTML;

    $html = preg_replace('~(<body[^>]*>)~i', '\$1' . $menuHtml, $html, 1);

    // Substitui o menu da navbar desktop e as ações na página inicial.
    $html = preg_replace(
        '~<div class="navbar-menu">.*?</div>\s*<div class="navbar-actions">~is',
        '<div class="navbar-menu">'
        . '<a href="/NEXAR/" class="navbar-link">Início</a>'
        . '<a href="/NEXAR/my-suppliers" class="navbar-link">Meus Fornecedores</a>'
        . '<a href="/NEXAR/my-orders" class="navbar-link">Meus Pedidos</a>'
        . '<a href="/NEXAR/profile" class="navbar-link">Meu Perfil</a>'
        . '</div><div class="navbar-actions">',
        $html,
        1
    );

    $html = preg_replace(
        '~<div class="navbar-actions">\s*<a href="/NEXAR/login"[^<]*>(?:Entrar|Login)</a>\s*<a href="/NEXAR/register"[^<]*>(?:Começar|Cadastrar|Create Account)</a>\s*</div>~i',
        '<div class="navbar-actions logged-in">'
        . '<a href="/NEXAR/my-suppliers" class="btn btn-secondary btn-sm">Meus Fornecedores</a>'
        . '<a href="/NEXAR/my-orders" class="btn btn-secondary btn-sm">Meus Pedidos</a>'
        . '<a href="/NEXAR/profile" class="btn btn-primary btn-sm">Meu Perfil</a>'
        . '</div>',
        $html,
        1
    );

    // Substitui também os botões de ação da navbar móvel.
    $html = preg_replace(
        '~<div class="navbar-actions" style="margin-top: var\(--space-4\);">\s*<a href="/NEXAR/login"[^<]*>(?:Entrar|Login)</a>\s*<a href="/NEXAR/register"[^<]*>(?:Começar|Cadastrar|Create Account)</a>\s*</div>~i',
        '<div class="navbar-actions logged-in" style="margin-top: var(--space-4);">'
        . '<a href="/NEXAR/my-suppliers" class="btn btn-secondary btn-sm w-full">Meus Fornecedores</a>'
        . '<a href="/NEXAR/my-orders" class="btn btn-primary btn-sm w-full">Meus Pedidos</a>'
        . '<a href="/NEXAR/profile" class="btn btn-secondary btn-sm w-full">Meu Perfil</a>'
        . '<a href="/NEXAR/logout" class="btn btn-secondary btn-sm w-full">Sair</a>'
        . '</div>',
        $html,
        1
    );

    // Remove qualquer link de registro quando o usuário estiver logado.
    $html = preg_replace(
        '~<a\s+href="/NEXAR/register"[^>]*>.*?</a>~is',
        '',
        $html
    );

    // Desativa o botão "Fale com Vendas" quando o usuário estiver logado.
    $html = preg_replace(
        '~<a\s+href="/contact"[^>]*>\s*Fale com Vendas\s*</a>~is',
        '<button type="button" class="btn-cta btn-cta-secondary" disabled>Fale com Vendas</button>',
        $html,
        1
    );
}

echo $html;
