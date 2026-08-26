<?php
/**
 * NEXAR - Hero Section Component
 * Main hero with stats and CTA
 */

$stats = [
    ['value' => '50K+', 'label' => 'Usuários Ativos'],
    ['value' => '120+', 'label' => 'Países'],
    ['value' => '99.9%', 'label' => 'Disponibilidade'],
];
?>

<section class="hero" id="home">
    <div class="hero-container">
        <!-- Hero Content -->
        <div class="hero-content">
            <div class="hero-badge reveal">
                <span class="hero-badge-dot"></span>
                <span>Agora em Beta Público</span>
            </div>

            <h1 class="hero-title reveal">
                Conecte-se com <br>
                <span class="hero-title-gradient">Talentos Premium</span> <br>
                no Mundo Todo
            </h1>

            <p class="hero-description reveal">
                A NEXAR é a plataforma moderna que conecta clientes a prestadores de serviços 
                verificados. Encontre profissionais de confiança para qualquer projeto, 
                de desenvolvimento web a marketing e muito mais.
            </p>

            <div class="hero-actions reveal">
                <a href="/register" class="btn btn-primary btn-lg magnetic">
                    Começar Gratuitamente
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
                <a href="#how-it-works" class="btn btn-secondary btn-lg magnetic">
                    Veja Como Funciona
                </a>
            </div>

            <div class="hero-stats reveal">
                <?php foreach ($stats as $stat): ?>
                    <div class="hero-stat">
                        <div class="hero-stat-value" data-counter="<?= str_replace(',', '', preg_replace('/[^0-9]/', '', $stat['value'])) ?>" data-suffix="<?= preg_replace('/[0-9]/', '', $stat['value']) ?>">0</div>
                        <div class="hero-stat-label"><?= htmlspecialchars($stat['label']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Hero Visual -->
        <div class="hero-visual reveal-left">
            <div class="hero-image glass-heavy" style="padding: var(--space-8);">
                <div class="hero-visual-content" style="position: relative;">
                    <!-- Abstract Dashboard Preview -->
                    <div class="dashboard-preview">
                        <div class="dashboard-header" style="display: flex; align-items: center; gap: var(--space-3); margin-bottom: var(--space-6);">
                            <div style="width: 12px; height: 12px; border-radius: 50%; background: #FF4757;"></div>
                            <div style="width: 12px; height: 12px; border-radius: 50%; background: #FFB800;"></div>
                            <div style="width: 12px; height: 12px; border-radius: 50%; background: #00D4AA;"></div>
                        </div>
                        
                        <div class="dashboard-content">
                            <div class="dashboard-sidebar" style="width: 80px; height: 200px; background: var(--glass-bg); border-radius: var(--radius-lg); margin-right: var(--space-4);"></div>
                            <div class="dashboard-main" style="flex: 1;">
                                <div class="dashboard-chart" style="height: 120px; background: linear-gradient(135deg, var(--color-primary-subtle), var(--color-secondary-glow)); border-radius: var(--radius-lg); margin-bottom: var(--space-4); display: flex; align-items: flex-end; padding: var(--space-4); gap: var(--space-2);">
                                    <div style="width: 20%; height: 40%; background: var(--color-primary); border-radius: var(--radius-sm);"></div>
                                    <div style="width: 20%; height: 60%; background: var(--color-primary); border-radius: var(--radius-sm);"></div>
                                    <div style="width: 20%; height: 80%; background: var(--color-primary); border-radius: var(--radius-sm);"></div>
                                    <div style="width: 20%; height: 50%; background: var(--color-secondary); border-radius: var(--radius-sm);"></div>
                                    <div style="width: 20%; height: 100%; background: var(--color-secondary); border-radius: var(--radius-sm);"></div>
                                </div>
                                <div class="dashboard-stats" style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-3);">
                                    <div style="height: 60px; background: var(--glass-bg); border-radius: var(--radius-lg);"></div>
                                    <div style="height: 60px; background: var(--glass-bg); border-radius: var(--radius-lg);"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Floating Elements -->
            <div class="floating-element" style="position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; background: var(--color-primary-subtle); border: 1px solid var(--color-primary); border-radius: var(--radius-xl); display: flex; align-items: center; justify-content: center; animation: float 6s ease-in-out infinite;">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            
            <div class="floating-element" style="position: absolute; bottom: -30px; left: -30px; width: 100px; height: 60px; background: var(--color-secondary-subtle); border: 1px solid var(--color-secondary); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; gap: var(--space-2); animation: float 8s ease-in-out infinite reverse;">
                <div style="width: 8px; height: 8px; background: var(--color-secondary); border-radius: 50%;"></div>
                <div style="width: 8px; height: 8px; background: var(--color-secondary); border-radius: 50%; opacity: 0.6;"></div>
                <div style="width: 8px; height: 8px; background: var(--color-secondary); border-radius: 50%; opacity: 0.3;"></div>
            </div>
        </div>
    </div>
</section>