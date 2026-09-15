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
    <link rel="stylesheet" href="/NEXAR/public/css/account-pages.css">
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
