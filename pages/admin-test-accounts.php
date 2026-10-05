<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/database.php';

$auth = Auth::getInstance();
if (!$auth->isAdmin()) {
    http_response_code(403);
    echo 'Acesso restrito a administradores.';
    exit;
}

$db = Database::getInstance();
$message = '';
$messageType = 'success';

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($requestMethod === 'POST') {
    $csrfToken = (string)($_POST[CSRF_TOKEN_NAME] ?? '');
    $userId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);

    if (!Auth::verifyCsrf($csrfToken)) {
        http_response_code(403);
        $message = 'A solicitação expirou. Atualize a página e tente novamente.';
        $messageType = 'error';
    } elseif (($_POST['action'] ?? '') !== 'delete_test_account' || !$userId) {
        http_response_code(400);
        $message = 'Solicitação inválida.';
        $messageType = 'error';
    } else {
        try {
            $db->beginTransaction();
            $deleted = $db->delete(
                'users',
                'id = :id AND is_test_account = 1 AND role <> :admin_role AND id <> :current_admin_id',
                [
                    'id' => $userId,
                    'admin_role' => 'admin',
                    'current_admin_id' => $auth->id(),
                ]
            );

            if ($deleted === 1) {
                $db->commit();
                $message = 'Conta de teste e dados associados removidos.';
            } else {
                $db->rollback();
                $message = 'A conta não existe ou não está marcada como conta de teste.';
                $messageType = 'error';
            }
        } catch (Throwable $error) {
            if ($db->getConnection()?->inTransaction()) {
                $db->rollback();
            }
            error_log('Test account deletion failed: ' . $error->getMessage());
            http_response_code(500);
            $message = 'Não foi possível remover esta conta.';
            $messageType = 'error';
        }
    }
}

$db->query(
    "SELECT u.id, u.email, u.first_name, u.last_name, u.created_at,
        COALESCE(NULLIF(s.trade_name, ''), NULLIF(s.legal_name, ''), NULLIF(e.trade_name, ''), NULLIF(e.legal_name, ''), 'Perfil sem empresa') AS company_name,
        COALESCE(s.category, s.business_segment, e.business_segment, 'Segmento não informado') AS segment,
        COALESCE(s.plan, e.plan, '-') AS plan
        FROM users u
        LEFT JOIN suppliers s ON s.user_id = u.id
        LEFT JOIN entrepreneurs e ON e.user_id = u.id
        WHERE u.is_test_account = 1 AND u.role <> :admin_role
        ORDER BY u.created_at DESC",
    ['admin_role' => 'admin']
);
$testAccounts = $db->fetchAll();
$csrfToken = Auth::csrfToken();

function adminTestAccountEscape(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contas de teste - NEXAR</title>
    <link rel="stylesheet" href="/NEXAR/public/css/variables.css">
    <link rel="stylesheet" href="/NEXAR/public/css/global.css">
    <style>
        .admin-page { min-height: 100vh; padding: 36px 20px; color: var(--color-white); }
        .admin-content { width: min(1120px, 100%); margin: 0 auto; }
        .admin-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 24px; }
        .admin-header h1 { margin: 0; font-size: 28px; }
        .admin-header p { margin: 6px 0 0; color: var(--color-gray-400); }
        .admin-back { color: var(--color-primary); text-decoration: none; }
        .admin-notice { margin: 18px 0; padding: 12px 14px; border-radius: 8px; background: rgba(0, 212, 170, .1); }
        .admin-notice.error { background: rgba(255, 90, 90, .12); color: #ffb7b7; }
        .admin-table-wrap { overflow-x: auto; border: 1px solid var(--glass-border); border-radius: 8px; }
        .admin-table { width: 100%; border-collapse: collapse; min-width: 760px; }
        .admin-table th, .admin-table td { padding: 13px 14px; border-bottom: 1px solid var(--glass-border); text-align: left; vertical-align: middle; }
        .admin-table th { color: var(--color-gray-300); font-size: 12px; text-transform: uppercase; }
        .admin-table td { color: var(--color-gray-100); font-size: 14px; }
        .admin-table tr:last-child td { border-bottom: 0; }
        .admin-delete { padding: 8px 11px; border: 1px solid rgba(255, 100, 100, .45); border-radius: 6px; background: transparent; color: #ffaaaa; cursor: pointer; }
        .admin-delete:hover { background: rgba(255, 100, 100, .12); }
        .admin-empty { padding: 28px; border: 1px dashed var(--glass-border); color: var(--color-gray-400); text-align: center; }
        @media (max-width: 640px) { .admin-page { padding: 24px 14px; } .admin-header { align-items: flex-start; flex-direction: column; } }
    </style>
</head>
<body class="bg-matte">
    <main class="admin-page">
        <div class="admin-content">
            <header class="admin-header">
                <div>
                    <h1>Contas de teste</h1>
                    <p><?= count($testAccounts) ?> contas marcadas para limpeza</p>
                </div>
                <a class="admin-back" href="/NEXAR/dashboard">Voltar ao painel</a>
            </header>

            <?php if ($message !== ''): ?>
                <div class="admin-notice <?= $messageType === 'error' ? 'error' : '' ?>" role="status">
                    <?= adminTestAccountEscape($message) ?>
                </div>
            <?php endif; ?>

            <?php if (empty($testAccounts)): ?>
                <div class="admin-empty">Nenhuma conta de teste marcada.</div>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Empresa / usuário</th>
                                <th>E-mail</th>
                                <th>Segmento</th>
                                <th>Plano</th>
                                <th>Criada em</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($testAccounts as $account): ?>
                                <tr>
                                    <td><?= adminTestAccountEscape((string)$account['company_name']) ?><br><small><?= adminTestAccountEscape(trim($account['first_name'] . ' ' . $account['last_name'])) ?></small></td>
                                    <td><?= adminTestAccountEscape((string)$account['email']) ?></td>
                                    <td><?= adminTestAccountEscape((string)$account['segment']) ?></td>
                                    <td><?= adminTestAccountEscape((string)$account['plan']) ?></td>
                                    <td><?= adminTestAccountEscape((string)$account['created_at']) ?></td>
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Apagar esta conta de teste e os dados associados? Esta ação não pode ser desfeita.');">
                                            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= adminTestAccountEscape($csrfToken) ?>">
                                            <input type="hidden" name="action" value="delete_test_account">
                                            <input type="hidden" name="user_id" value="<?= (int)$account['id'] ?>">
                                            <button class="admin-delete" type="submit">Apagar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>