<?php
/**
 * NEXAR - Navbar Component
 * Responsive navigation with mobile menu
 */

$nav_items = [
    ['label' => 'Início', 'href' => '/NEXAR/'],
    ['label' => 'Fornecedores', 'href' => '#features'],
    ['label' => 'Categorias', 'href' => '#categories'],
    ['label' => 'Planos', 'href' => '#pricing'],
    ['label' => 'Contato', 'href' => '#contact'],
];

$loggedIn = false;
$user = null;

$authFile = __DIR__ . '/../php/auth.php';
if (file_exists($authFile)) {
    require_once $authFile;
    $auth = Auth::getInstance();
    if ($auth->check()) {
        $loggedIn = true;
        $user = $auth->user();
        $nav_items = [
            ['label' => 'Início', 'href' => '/NEXAR/'],
            ['label' => 'Meus Fornecedores', 'href' => '/NEXAR/my-suppliers'],
            ['label' => 'Meus Pedidos', 'href' => '/NEXAR/my-orders'],
            ['label' => 'Meu Perfil', 'href' => '/NEXAR/profile'],
        ];
    }
}
?>

<nav class="navbar" role="navigation" aria-label="Main navigation">
    <div class="navbar-container">
        <!-- Logo -->
        <a href="/NEXAR/" class="navbar-logo">
            <svg class="navbar-logo-icon" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M18 2L32 10V26L18 34L4 26V10L18 2Z" stroke="currentColor" stroke-width="2" fill="none"/>
                <path d="M18 8L26 13V23L18 28L10 23V13L18 8Z" fill="currentColor"/>
                <circle cx="18" cy="18" r="4" fill="#0A0A0A"/>
            </svg>
            <span>NEXAR</span>
        </a>

        <!-- Desktop Menu -->
        <div class="navbar-menu">
            <?php foreach ($nav_items as $item): ?>
                <a href="<?= htmlspecialchars($item['href']) ?>" class="navbar-link">
                    <?= htmlspecialchars($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Actions -->
        <div class="navbar-actions">
            <?php if ($loggedIn): ?>
                <a href="/NEXAR/my-orders" class="btn btn-secondary btn-sm" title="Notificações" aria-label="Notificações">
                    🔔
                </a>
                <a href="/NEXAR/my-suppliers" class="btn btn-secondary btn-sm" title="Mensagens" aria-label="Mensagens">
                    💬
                </a>
                <div class="navbar-user-dropdown">
                    <button class="user-button" type="button" aria-expanded="false">
                        <span class="user-avatar"><?= htmlspecialchars(substr(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')), 0, 2), ENT_QUOTES) ?></span>
                        <span class="user-name"><?= htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')), ENT_QUOTES) ?></span>
                    </button>
                    <div class="dropdown-menu">
                        <a href="/NEXAR/profile">Meu Perfil</a>
                        <a href="/NEXAR/profile#settings">Configurações</a>
                        <a href="/NEXAR/logout">Sair</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/NEXAR/login" class="btn btn-secondary btn-sm">Entrar</a>
                <a href="/NEXAR/register" class="btn btn-primary btn-sm">Cadastrar</a>
            <?php endif; ?>
        </div>

        <!-- Mobile Toggle -->
        <button class="navbar-toggle" aria-label="Abrir menu" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
    </div>

    <!-- Mobile Menu -->
    <div class="navbar-menu-mobile">
        <?php foreach ($nav_items as $item): ?>
            <a href="<?= htmlspecialchars($item['href']) ?>" class="navbar-link">
                <?= htmlspecialchars($item['label']) ?>
            </a>
        <?php endforeach; ?>
        <div class="navbar-actions mobile-navbar-actions">
            <?php if ($loggedIn): ?>
                <a href="/NEXAR/my-suppliers" class="btn btn-secondary btn-sm w-full">Meus Fornecedores</a>
                <a href="/NEXAR/my-orders" class="btn btn-secondary btn-sm w-full">Meus Pedidos</a>
                <a href="/NEXAR/profile" class="btn btn-primary btn-sm w-full">Meu Perfil</a>
                <a href="/NEXAR/logout" class="btn btn-secondary btn-sm w-full">Sair</a>
            <?php else: ?>
                <a href="/NEXAR/login" class="btn btn-secondary btn-sm w-full">Entrar</a>
                <a href="/NEXAR/register" class="btn btn-primary btn-sm w-full">Cadastrar</a>
            <?php endif; ?>
        </div>
    </div>
</nav>