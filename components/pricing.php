<?php
/**
 * NEXAR - Pricing Section
 * Subscription tiers and pricing plans
 */

$plans = [
    [
        'name' => 'Básico',
        'price' => 0,
        'description' => 'Perfeito para pequenos negócios que querem testar a plataforma e encontrar fornecedores locais.',
        'features' => [
            'Visibilidade básica de perfil',
            'Até 3 propostas mensais',
            'Acesso a categorias selecionadas',
            'Suporte por e-mail',
        ],
        'highlighted' => false,
    ],
    [
        'name' => 'Profissional',
        'price' => 29,
        'description' => 'Excelente para fornecedores que desejam gerar mais leads qualificados e se destacar no marketplace.',
        'features' => [
            'Perfil em destaque',
            'Match inteligente com compradores',
            'Suporte prioritário',
            'Relatórios de desempenho',
            'Acesso a novas categorias',
            'Ferramentas de contato direto',
        ],
        'highlighted' => true,
    ],
    [
        'name' => 'Premium',
        'price' => 99,
        'description' => 'A solução completa para fornecedores com alto volume de clientes e presença premium na plataforma.',
        'features' => [
            'Tudo no plano Profissional',
            'Gerente de conta dedicado',
            'Campanhas de destaque',
            'Integrações avançadas',
            'Visibilidade prioritária em pesquisas',
            'Relatórios e métricas premium',
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