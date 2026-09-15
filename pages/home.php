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
        '<div class="navbar-actions logged-in mobile-navbar-actions">'
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
