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
    <title>My Orders - NEXAR</title>
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
                    <h1 class="page-title">Meus Pedidos</h1>
                    <p class="page-description">Acompanhe os pedidos, o status das solicitações e os histórico de cotações em um painel claro e organizado.</p>
                </div>
                <a class="order-action" href="/NEXAR/my-suppliers">Fazer nova cotação</a>
            </div>
            <div class="order-sections">
                <section class="order-panel">
                    <div class="order-header">
                        <h2>Solicitações</h2>
                        <span class="status-pill">Aguardando</span>
                    </div>
                    <div class="order-item">
                        <div class="order-meta">
                            <span>Sem solicitações de orçamento</span>
                            <span>Atualize seu fluxo</span>
                        </div>
                        <p class="order-description">Envie uma solicitação para receber propostas dos fornecedores e comparar opções de forma rápida.</p>
                    </div>
                </section>
                <section class="order-panel">
                    <div class="order-header">
                        <h2>Histórico</h2>
                        <span class="status-pill">Pronto</span>
                    </div>
                    <div class="order-item">
                        <div class="order-meta">
                            <span>Sem pedidos registrados</span>
                            <span>Comece agora</span>
                        </div>
                        <p class="order-description">Os pedidos feitos aparecerão aqui assim que você iniciar parcerias com fornecedores.</p>
                    </div>
                </section>
                <section class="order-panel">
                    <div class="order-header">
                        <h2>Status</h2>
                        <span class="status-pill">Inativo</span>
                    </div>
                    <div class="order-item">
                        <div class="order-meta">
                            <span>Sem solicitações ativas</span>
                            <span>Atualize o status</span>
                        </div>
                        <p class="order-description">Acompanhe o andamento de respostas, cotações e atualizações dos fornecedores em um só lugar.</p>
                    </div>
                </section>
            </div>
        </div>
    </main>
</body>
</html>
