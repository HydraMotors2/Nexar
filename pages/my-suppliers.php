<?php
require_once __DIR__ . '/../php/auth.php';

$auth = Auth::getInstance();
if (!$auth->check()) {
    header('Location: /NEXAR/login');
    exit;
}

$user = $auth->user();
function safe(string $value = ''): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include __DIR__ . '/../components/favicon.php'; ?>
    <title>My Suppliers - NEXAR</title>
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
            max-width: 1140px;
            margin: 0 auto;
            background: rgba(18, 18, 18, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 32px;
            padding: 36px;
            box-shadow: 0 32px 80px rgba(0, 0, 0, 0.3);
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
            margin: 0 0 12px;
            font-weight: var(--font-bold);
            letter-spacing: -0.02em;
            color: var(--color-white);
        }
        .page-description {
            color: var(--color-gray-400);
            font-size: var(--text-base);
            line-height: 1.75;
            max-width: 720px;
            margin: 0;
        }
        .supplier-sections {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
        }
        .supplier-panel {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 28px;
            padding: 28px;
            backdrop-filter: blur(18px);
            transition: transform var(--transition-base), border-color var(--transition-base), background var(--transition-base);
        }
        .supplier-panel:hover {
            transform: translateY(-3px);
            border-color: rgba(255, 107, 53, 0.22);
            background: rgba(255, 255, 255, 0.05);
        }
        .supplier-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 18px;
        }
        .supplier-panel h2 {
            margin: 0;
            font-size: var(--text-2xl);
            color: var(--color-white);
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 0.85rem;
            border-radius: 999px;
            font-size: var(--text-xs);
            font-weight: var(--font-semibold);
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--color-white);
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.2), rgba(255, 107, 53, 0.08));
            border: 1px solid rgba(255, 107, 53, 0.16);
        }
        .supplier-list {
            display: grid;
            gap: 14px;
        }
        .supplier-card {
            padding: 22px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .supplier-card-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .supplier-card-title h3 {
            margin: 0;
            font-size: var(--text-base);
            color: var(--color-white);
        }
        .supplier-card-meta {
            color: var(--color-gray-400);
            font-size: var(--text-sm);
            line-height: 1.75;
        }
        .supplier-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.9rem 1.2rem;
            border-radius: 999px;
            border: 1px solid rgba(255, 107, 53, 0.2);
            background: rgba(255, 107, 53, 0.12);
            color: var(--color-white);
            text-decoration: none;
            font-weight: var(--font-semibold);
            transition: background var(--transition-base), transform var(--transition-base);
        }
        .supplier-action:hover {
            background: rgba(255, 107, 53, 0.2);
            transform: translateY(-1px);
        }
        @media (max-width: 700px) {
            .page-header {
                flex-direction: column;
                align-items: stretch;
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
                    <h1 class="page-title">Meus Fornecedores</h1>
                    <p class="page-description">Fornecedores favoritos, contatos recentes e fornecedores salvos em um só lugar.</p>
                </div>
                <a class="supplier-action" href="/NEXAR/my-orders">Ver pedidos</a>
            </div>
            <div class="supplier-sections">
                <section class="supplier-panel">
                    <div class="supplier-header">
                        <h2>Favoritos</h2>
                        <span class="status-pill">Nenhum</span>
                    </div>
                    <div class="supplier-list">
                        <div class="supplier-card">
                            <div class="supplier-card-title">
                                <h3>Sem favoritos salvos</h3>
                            </div>
                            <div class="supplier-card-meta supplier-placeholder">Adicione fornecedores aos favoritos para acessá-los rapidamente.</div>
                        </div>
                    </div>
                </section>
                <section class="supplier-panel">
                    <div class="supplier-header">
                        <h2>Contatos recentes</h2>
                        <span class="status-pill">Vazio</span>
                    </div>
                    <div class="supplier-list">
                        <div class="supplier-card">
                            <div class="supplier-card-title">
                                <h3>Sem contatos recentes</h3>
                            </div>
                            <div class="supplier-card-meta supplier-placeholder">As últimas conversas com fornecedores aparecerão aqui.</div>
                        </div>
                    </div>
                </section>
                <section class="supplier-panel">
                    <div class="supplier-header">
                        <h2>Fornecedores salvos</h2>
                        <span class="status-pill">0</span>
                    </div>
                    <div class="supplier-list">
                        <div class="supplier-card">
                            <div class="supplier-card-title">
                                <h3>Nenhum fornecedor salvo</h3>
                            </div>
                            <div class="supplier-card-meta supplier-placeholder">Salve fornecedores para comparar propostas e gerenciar sua sourcing.</div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>
</body>
</html>
