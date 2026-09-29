<?php
/**
 * NEXAR - Pricing Section
 * Subscription tiers and pricing plans
 */

$plans = [
    [
        'name' => 'Gratuito',
        'price' => 0,
        'description' => 'Perfeito para micro e pequenas empresas que querem testar a plataforma sem custo.',
        'features' => [
            'Perfil básico criado em minutos',
            'Busca por fornecedores',
            'Acesso inicial ao marketplace',
            'Ideal para testar e validar demanda',
        ],
        'highlighted' => false,
    ],
    [
        'name' => 'Micro',
        'price' => 25,
        'description' => 'Mais visibilidade para empresas que querem crescer com mais oportunidades.',
        'features' => [
            'Resultado melhor posicionado em buscas',
            'Mais destaque para o perfil',
            'Acesso rápido a fornecedores qualificados',
            'Suporte por e-mail',
        ],
        'highlighted' => true,
    ],
    [
        'name' => 'Pequena Empresa',
        'price' => 50,
        'description' => 'Uma presença mais forte para captar oportunidades e aumentar o volume de contatos.',
        'features' => [
            'Tudo do plano Micro',
            'Destaque maior na plataforma',
            'Acesso prioritário a fornecedores',
            'Ajuste para crescimento de demanda',
        ],
        'highlighted' => false,
    ],
    [
        'name' => 'Conta',
        'price' => 50,
        'description' => 'Permite criar a conta do fornecedor e aparecer no marketplace com base inicial.',
        'features' => [
            'Cadastro de perfil de fornecedor',
            'Presença no marketplace',
            'Base para crescer e receber contatos',
            'Acesso ao gerenciamento do negócio',
        ],
        'highlighted' => false,
    ],
    [
        'name' => 'Destaque',
        'price' => 100,
        'description' => 'Ideal para fornecedores que querem mais visibilidade e mais oportunidades de contato.',
        'features' => [
            'Tudo do plano Conta',
            'Aparece mais vezes nas pesquisas',
            '2 aparições extras a cada 10 pesquisas',
            'Melhor posicionamento em listagens',
        ],
        'highlighted' => true,
    ],
    [
        'name' => 'Promoção Premium',
        'price' => 200,
        'description' => 'A melhor presença para empresas que desejam maior alcance e grande volume de oportunidades.',
        'features' => [
            'Tudo do plano Destaque',
            '5 aparições extras a cada 10 pesquisas',
            'Maior prioridade em listagens',
            'Exposição premium e posicionamento forte',
        ],
        'highlighted' => false,
    ],
];
?>

<section class="pricing" id="pricing">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-tag">Preços</span>
            <h2 class="section-title">Preços simples e transparentes</h2>
            <p class="section-description">
                Escolha o plano que atende suas necessidades. Todos os planos incluem acesso 
                aos recursos principais, sem taxas ocultas.
            </p>
        </div>

        <div class="pricing-grid stagger-group">
            <?php foreach ($plans as $index => $plan): ?>
                <div class="card pricing-card reveal stagger-<?= $index + 1 ?> <?= $plan['highlighted'] ? 'featured' : '' ?>">
                    <?php if ($plan['highlighted']): ?>
                        <span class="pricing-badge">Mais Popular</span>
                    <?php endif; ?>
                    
                    <div class="pricing-tier"><?= htmlspecialchars($plan['name']) ?></div>
                    
                    <div class="pricing-price">
                        $<?= number_format($plan['price']) ?>
                        <span>/mês</span>
                    </div>
                    
                    <p class="pricing-description"><?= htmlspecialchars($plan['description']) ?></p>
                    
                    <ul class="pricing-features">
                        <?php foreach ($plan['features'] as $feature): ?>
                            <li class="pricing-feature">
                                <svg class="pricing-feature-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span><?= htmlspecialchars($feature) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <a href="/register?plan=<?= strtolower($plan['name']) ?>" 
                       class="btn <?= $plan['highlighted'] ? 'btn-primary' : 'btn-secondary' ?> w-full">
                        <?php if ($plan['price'] == 0): ?>
                            Começar Gratuitamente
                        <?php else: ?>
                            Assinar Agora
                        <?php endif; ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>