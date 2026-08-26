<?php
/**
 * NEXAR - How It Works Section
 * Step-by-step process explanation
 */

$steps = [
    [
        'number' => '01',
        'title' => 'Crie seu Perfil',
        'description' => 'Cadastre-se e construa seu perfil em minutos. Conte suas necessidades de projeto ou mostre suas habilidades como prestador.',
    ],
    [
        'number' => '02',
        'title' => 'Receba o Match',
        'description' => 'Nossa inteligência conecta você aos profissionais mais compatíveis com base em habilidades, orçamento e prazo.',
    ],
    [
        'number' => '03',
        'title' => 'Colabore e Entregue',
        'description' => 'Trabalhe junto com nossas ferramentas integradas. Acompanhe o progresso, comunique em tempo real e garanta a entrega com sucesso.',
    ],
];
?>

<section class="how-it-works" id="how-it-works">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-tag">Como Funciona</span>
            <h2 class="section-title">Simples e poderoso</h2>
            <p class="section-description">
                Começar com a NEXAR é fácil. Veja como encontrar o match ideal para seu 
                projeto em apenas três passos simples.
            </p>
        </div>

        <div class="steps-grid">
            <?php foreach ($steps as $index => $step): ?>
                <div class="step-card reveal stagger-<?= $index + 1 ?>">
                    <div class="step-number"><?= htmlspecialchars($step['number']) ?></div>
                    <h3 class="step-title"><?= htmlspecialchars($step['title']) ?></h3>
                    <p class="step-description"><?= htmlspecialchars($step['description']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>