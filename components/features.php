<?php
/**
 * NEXAR - Features Section Component
 * Core platform features with icons
 */

$features = [
    [
        'icon' => 'shield',
        'title' => 'Profissionais Verificados',
        'description' => 'Cada prestador de serviços na NEXAR passa por um rigoroso processo de verificação, garantindo especialistas confiáveis.',
    ],
    [
        'icon' => 'zap',
        'title' => 'Conexão Instantânea',
        'description' => 'Nosso sistema inteligente conecta você ao prestador perfeito em segundos, não horas.',
    ],
    [
        'icon' => 'globe',
        'title' => 'Talentos Globais',
        'description' => 'Acesse uma rede diversa de profissionais de mais de 120 países, trazendo expertise global aos seus projetos.',
    ],
    [
        'icon' => 'lock',
        'title' => 'Pagamentos Seguros',
        'description' => 'Transações protegidas com escrow garantem que seus recursos fiquem seguros até você aprovar o trabalho.',
    ],
    [
        'icon' => 'message',
        'title' => 'Colaboração em Tempo Real',
        'description' => 'Ferramentas integradas de comunicação, compartilhamento de arquivos e gestão mantêm todos alinhados e produtivos.',
    ],
    [
        'icon' => 'chart',
        'title' => 'Analytics de Desempenho',
        'description' => 'Acompanhe o progresso do projeto, orçamento e resultados com painéis de análise completos.',
    ],
];

$icons = [
    'shield' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>',
    'zap' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>',
    'globe' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>',
    'lock' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>',
    'message' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>',
    'chart' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>',
];
?>

<section class="features" id="features">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-tag">Recursos</span>
            <h2 class="section-title">Tudo que você precisa para vencer</h2>
            <p class="section-description">
                A NEXAR oferece um conjunto completo de ferramentas projetadas para 
                simplificar seu fluxo de trabalho e maximizar sua produtividade.
            </p>
        </div>

        <div class="features-grid stagger-group">
            <?php foreach ($features as $index => $feature): ?>
                <div class="card reveal stagger-<?= $index + 1 ?>">
                    <div class="card-icon">
                        <?= $icons[$feature['icon']] ?>
                    </div>
                    <h3 class="card-title"><?= htmlspecialchars($feature['title']) ?></h3>
                    <p class="card-description"><?= htmlspecialchars($feature['description']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>