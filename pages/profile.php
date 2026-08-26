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
    <style>
        .page-shell {
            min-height: 100vh;
            background: radial-gradient(circle at top center, rgba(255, 107, 53, 0.12), transparent 35%), var(--color-black);
            padding: 120px 24px 64px;
        }
        .content-card {
            max-width: 1120px;
            margin: 0 auto;
            background: rgba(18, 18, 18, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 32px;
            padding: 36px;
            box-shadow: 0 32px 80px rgba(0, 0, 0, 0.28);
        }
        .page-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 18px;
            align-items: flex-start;
            margin-bottom: 32px;
        }
        .page-title {
            font-size: var(--text-4xl);
            margin: 0 0 10px;
            color: var(--color-white);
            letter-spacing: -0.02em;
        }
        .page-description {
            color: var(--color-gray-400);
            font-size: var(--text-base);
            line-height: 1.75;
            max-width: 720px;
            margin: 0;
        }
        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }
        .profile-item {
            padding: 24px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 28px;
            transition: transform var(--transition-base), border-color var(--transition-base), background var(--transition-base);
        }
        .profile-item:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 107, 53, 0.2);
            background: rgba(255, 255, 255, 0.06);
        }
        .profile-label {
            display: block;
            margin-bottom: 10px;
            font-size: var(--text-xs);
            text-transform: uppercase;
            letter-spacing: .12em;
            color: var(--color-gray-500);
        }
        .profile-value {
            font-size: var(--text-base);
            color: var(--color-white);
            line-height: 1.75;
            word-break: break-word;
        }
        .profile-actions {
            margin-top: 32px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        .profile-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
            margin-top: 8px;
        }
        .profile-meta span {
            display: inline-flex;
            padding: 0.4rem 0.85rem;
            border-radius: 999px;
            background: rgba(255, 107, 53, 0.12);
            color: var(--color-white);
            font-size: var(--text-xs);
            font-weight: var(--font-semibold);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        @media (max-width: 700px) {
            .page-header {
                flex-direction: column;
            }
            .profile-actions {
                width: 100%;
            }
        }
    </style>
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
