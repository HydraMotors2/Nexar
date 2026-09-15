<?php
require_once __DIR__ . '/../php/auth.php';

$auth = Auth::getInstance();
if (!$auth->check()) {
    header('Location: /NEXAR/login');
    exit;
}

$user = $auth->user();
$db = Database::getInstance();
$profile = [];

$accountType = $user['account_type'] ?? null;
if (!$accountType) {
    $accountType = ($user['role'] ?? '') === 'provider' ? 'supplier' : 'entrepreneur';
}
$accountType = in_array($accountType, ['supplier', 'entrepreneur'], true) ? $accountType : 'entrepreneur';
$tableName = $accountType === 'supplier' ? 'suppliers' : 'entrepreneurs';

if (!empty($user['id']) && $db->tableExists($tableName)) {
    $rows = $db->select($tableName, '*', 'user_id = :id', ['id' => $user['id']]);
    if (!empty($rows)) {
        $profile = $rows[0];
    }
}

$profile = array_merge($user, $profile);

function safe(string $value = ''): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$fullName = trim($profile['full_name'] ?? trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? '')));
if ($fullName === '') {
    $fullName = $profile['email'] ?? 'Não disponível';
}
$companyName = $profile['company_name'] ?? $profile['legal_name'] ?? $profile['trade_name'] ?? 'Não disponível';
$companyDisplay = $profile['legal_name'] ?? $profile['company_name'] ?? $profile['trade_name'] ?? 'Não disponível';
$cnpj = $profile['cnpj'] ?? 'Não disponível';
$phone = $profile['phone'] ?? $profile['phone_number'] ?? 'Não disponível';
$email = $profile['email'] ?? 'Não disponível';
$accountTypeLabel = $accountType === 'supplier' ? 'Fornecedor' : 'Empreendedor';
$roleLabel = ($profile['role'] ?? '') === 'provider' ? 'Fornecedor' : 'Cliente';
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include __DIR__ . '/../components/favicon.php'; ?>
    <title>Meu Perfil - NEXAR</title>
    <link rel="stylesheet" href="/NEXAR/public/css/variables.css">
    <link rel="stylesheet" href="/NEXAR/public/css/global.css">
    <link rel="stylesheet" href="/NEXAR/public/css/components.css">
    <link rel="stylesheet" href="/NEXAR/public/css/animations.css">
    <link rel="stylesheet" href="/NEXAR/public/css/account-pages.css">
</head>
<body>
    <?php include __DIR__ . '/../components/navbar.php'; ?>
    <main class="page-shell">
        <div class="content-card">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Meu Perfil</h1>
                    <p class="page-description">Confira os dados da sua conta e atualize suas informações sempre que precisar.</p>
                    <div class="profile-meta">
                        <span><?= safe($companyDisplay) ?></span>
                        <span><?= safe($accountTypeLabel) ?></span>
                        <span><?= safe($roleLabel) ?></span>
                    </div>
                </div>
                <div class="profile-actions">
                    <a href="/NEXAR/profile#edit" class="btn btn-primary">Editar Perfil</a>
                    <a href="/NEXAR/logout" class="btn btn-secondary">Sair</a>
                </div>
            </div>
            <div class="profile-grid">
                <div class="profile-item">
                    <span class="profile-label">Nome</span>
                    <div class="profile-value"><?= safe($fullName) ?></div>
                </div>
                <div class="profile-item">
                    <span class="profile-label">E-mail</span>
                    <div class="profile-value"><?= safe($email) ?></div>
                </div>
                <div class="profile-item">
                    <span class="profile-label">Razão Social</span>
                    <div class="profile-value"><?= safe($companyName) ?></div>
                </div>
                <div class="profile-item">
                    <span class="profile-label">CNPJ</span>
                    <div class="profile-value"><?= safe($cnpj) ?></div>
                </div>
                <div class="profile-item">
                    <span class="profile-label">Telefone</span>
                    <div class="profile-value"><?= safe($phone) ?></div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
