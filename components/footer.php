<?php
/**
 * NEXAR - Footer Component
 * Site-wide footer with navigation and links
 */

$footer_links = [
    'Produto' => [
        ['label' => 'Recursos', 'href' => '#features'],
        ['label' => 'Preços', 'href' => '#pricing'],
        ['label' => 'Integrações', 'href' => '/integrations'],
        ['label' => 'API', 'href' => '/developers'],
        ['label' => 'Registro de Mudanças', 'href' => '/changelog'],
    ],
    'Empresa' => [
        ['label' => 'Sobre', 'href' => '/about'],
        ['label' => 'Blog', 'href' => '/blog'],
        ['label' => 'Carreiras', 'href' => '/careers'],
        ['label' => 'Kit de Imprensa', 'href' => '/press'],
        ['label' => 'Contato', 'href' => '/contact'],
    ],
    'Recursos' => [
        ['label' => 'Documentação', 'href' => '/docs'],
        ['label' => 'Central de Ajuda', 'href' => '/help'],
        ['label' => 'Comunidade', 'href' => '/community'],
        ['label' => 'Modelos', 'href' => '/templates'],
        ['label' => 'Status', 'href' => '/status'],
    ],
];

$social_links = [
    [
        'name' => 'Twitter',
        'href' => 'https://twitter.com/nexar',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path></svg>',
    ],
    [
        'name' => 'GitHub',
        'href' => 'https://github.com/nexar',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path></svg>',
    ],
    [
        'name' => 'LinkedIn',
        'href' => 'https://linkedin.com/company/nexar',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>',
    ],
    [
        'name' => 'Discord',
        'href' => 'https://discord.gg/nexar',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"></circle><circle cx="15" cy="12" r="1"></circle><path d="M7.5 7.5c3.5-1 5.5-1 9 0 1.5 2 2.5 3.5 2.5 5.5a6 6 0 0 1-6 6c-1.5 0-2.5-.5-3-1-.5.5-1.5 1-3 1a6 6 0 0 1-6-6c0-2 1-3.5 2.5-5.5z"></path></svg>',
    ],
];
?>

<footer class="footer">
    <div class="footer-container">
        <div class="footer-grid">
            <!-- Brand Column -->
            <div class="footer-brand">
                <a href="/" class="footer-logo">
                    <svg class="footer-logo-icon" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 2L32 10V26L18 34L4 26V10L18 2Z" stroke="currentColor" stroke-width="2" fill="none"/>
                        <path d="M18 8L26 13V23L18 28L10 23V13L18 8Z" fill="currentColor"/>
                        <circle cx="18" cy="18" r="4" fill="#0A0A0A"/>
                    </svg>
                    <span>NEXAR</span>
                </a>
                <p class="footer-description">
                    A plataforma moderna que conecta clientes a prestadores de serviços 
                    verificados no mundo todo. Construa projetos incríveis com talentos de confiança.
                </p>
                <div class="footer-social">
                    <?php foreach ($social_links as $social): ?>
                        <a href="<?= htmlspecialchars($social['href']) ?>" 
                           class="footer-social-link" 
                           aria-label="<?= htmlspecialchars($social['name']) ?>"
                           target="_blank"
                           rel="noopener noreferrer">
                            <?= $social['icon'] ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Produto Links -->
            <div>
                <h4 class="footer-title">Produto</h4>
                <nav class="footer-links">
                    <?php foreach ($footer_links['Produto'] as $link): ?>
                        <a href="<?= htmlspecialchars($link['href']) ?>" class="footer-link">
                            <?= htmlspecialchars($link['label']) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>

            <!-- Empresa Links -->
            <div>
                <h4 class="footer-title">Empresa</h4>
                <nav class="footer-links">
                    <?php foreach ($footer_links['Empresa'] as $link): ?>
                        <a href="<?= htmlspecialchars($link['href']) ?>" class="footer-link">
                            <?= htmlspecialchars($link['label']) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>

            <!-- Recursos Links -->
            <div>
                <h4 class="footer-title">Recursos</h4>
                <nav class="footer-links">
                    <?php foreach ($footer_links['Recursos'] as $link): ?>
                        <a href="<?= htmlspecialchars($link['href']) ?>" class="footer-link">
                            <?= htmlspecialchars($link['label']) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="footer-bottom">
            <p class="footer-copyright">
                &copy; <?= date('Y') ?> NEXAR. All rights reserved.
            </p>
            <nav class="footer-legal">
                <a href="/privacy" class="footer-legal-link">Política de Privacidade</a>
                <a href="/terms" class="footer-legal-link">Termos de Serviço</a>
                <a href="/cookies" class="footer-legal-link">Política de Cookies</a>
            </nav>
        </div>
    </div>
</footer>