<?php
/**
 * NEXAR - Testimonials Section
 * Customer reviews and success stories
 */

$testimonials = [
    [
        'quote' => "A NEXAR transformou a forma como contratamos talentos. Encontramos nosso desenvolvedor líder em menos de 48 horas e a qualidade do trabalho foi excepcional. O processo de verificação da plataforma nos dá total tranquilidade.",
        'author' => 'Sarah Chen',
        'role' => 'CTO na TechFlow',
        'avatar' => '/assets/images/avatar-1.jpg',
    ],
    [
        'quote' => "Como designer freelancer, a NEXAR mudou o jogo. Sou constantemente conectado a clientes de alta qualidade que valorizam meu trabalho, e a proteção de pagamento é fantástica.",
        'author' => 'Marcus Rodriguez',
        'role' => 'Designer Sênior UI/UX',
        'avatar' => '/assets/images/avatar-2.jpg',
    ],
    [
        'quote' => "Já testamos muitas plataformas, mas o matching de IA da NEXAR está em outro patamar. Ele entende nossos requisitos perfeitamente e nos conecta com profissionais que realmente se encaixam na nossa cultura.",
        'author' => 'Emily Watson',
        'role' => 'Chefe de Operações na StartupLab',
        'avatar' => '/assets/images/avatar-3.jpg',
    ],
];
?>

<section class="testimonials" id="testimonials">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-tag">Depoimentos</span>
            <h2 class="section-title">Amado por milhares</h2>
            <p class="section-description">
                Não acredite só na nossa palavra. Veja o que nossa comunidade de clientes 
                e prestadores tem a dizer sobre a experiência na NEXAR.
            </p>
        </div>

        <div class="testimonials-grid stagger-group">
            <?php foreach ($testimonials as $index => $testimonial): ?>
                <div class="card testimonial-card reveal stagger-<?= $index + 1 ?>">
                    <p class="testimonial-quote">
                        <?= htmlspecialchars($testimonial['quote']) ?>
                    </p>
                    <div class="testimonial-author">
                        <div class="avatar avatar-md">
                            <img src="<?= htmlspecialchars($testimonial['avatar']) ?>" 
                                 alt="<?= htmlspecialchars($testimonial['author']) ?>" 
                                 loading="lazy"
                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 48 48%22%3E%3Ccircle cx=%2224%22 cy=%2224%22 r=%2224%22 fill=%22%231A1A1A%22/%3E%3Ctext x=%2224%22 y=%2228%22 text-anchor=%22middle%22 fill=%22%23FF6B35%22 font-size=%2218%22 font-family=%22Inter%22%3E' . strtoupper(substr($testimonial['author'], 0, 1)) . '%3C/text%3E%3C/svg%3E'">
                        </div>
                        <div class="testimonial-info">
                            <div class="testimonial-name"><?= htmlspecialchars($testimonial['author']) ?></div>
                            <div class="testimonial-role"><?= htmlspecialchars($testimonial['role']) ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>