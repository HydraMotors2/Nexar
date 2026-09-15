<?php
$companyId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$company = null;
$services = [];

if ($companyId) {
    require_once __DIR__ . '/../php/database.php';
    $db = Database::getInstance();
    $db->query('SELECT * FROM companies WHERE id = :id LIMIT 1', ['id' => $companyId]);
    $company = $db->fetch();

    if ($company) {
        $db->query('SELECT id, title, description, price_min, price_max FROM services WHERE company_id = :id AND is_active = 1 LIMIT 8', ['id' => $companyId]);
        $services = $db->fetchAll();
    }
}

if (!$company) {
    $company = [
        'name' => 'TechVision Studios',
        'tagline' => 'Agência digital premiada especializada em desenvolvimento web e mobile',
        'city' => 'San Francisco, CA',
        'response_time' => 'Responde em até 2 horas',
        'rating' => '4.9',
        'rating_count' => '127 avaliações',
        'completed' => '245 Clientes Atendidos',
        'about' => 'TechVision Studios é um fornecedor B2B premiado com mais de 10 anos de experiência em soluções empresariais de alto impacto. Nossa equipe ajuda pequenas e médias empresas a encontrar as melhores opções de fornecedores e serviços.'
    ];
}

$pageTitle = $company['name'] . ' - Perfil do Fornecedor';
$metaDescription = $company['tagline'] ?? 'Perfil profissional de fornecedor de serviços';
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include __DIR__ . '/../components/favicon.php'; ?>
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="/NEXAR/public/css/variables.css">
    <link rel="stylesheet" href="/NEXAR/public/css/global.css">
    <link rel="stylesheet" href="/NEXAR/public/css/components.css">
    <link rel="stylesheet" href="/NEXAR/public/css/animations.css">
    
    <style>
        /* Provider Profile Specific Styles */
        .provider-profile {
            min-height: 100vh;
            background: var(--color-black-matte);
        }
        
        /* Banner */
        .provider-banner {
            position: relative;
            height: 320px;
            overflow: hidden;
        }
        
        .provider-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .provider-banner-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, var(--color-black-matte) 0%, transparent 60%);
        }
        
        /* Profile Header */
        .profile-header {
            position: relative;
            margin-top: -120px;
            padding: 0 var(--space-8);
            margin-bottom: var(--space-8);
        }
        
        .profile-header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: flex-end;
            gap: var(--space-6);
        }
        
        .profile-avatar-wrapper {
            position: relative;
            flex-shrink: 0;
        }
        
        .profile-avatar {
            width: 200px;
            height: 200px;
            border-radius: var(--radius-2xl);
            border: 4px solid var(--color-black-matte);
            background: var(--color-gray-900);
            object-fit: cover;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
        }
        
        .profile-verified-badge {
            position: absolute;
            bottom: 8px;
            right: 8px;
            width: 40px;
            height: 40px;
            background: var(--color-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid var(--color-black-matte);
        }
        
        .profile-info {
            flex: 1;
            padding-bottom: var(--space-2);
        }
        
        .profile-name {
            font-size: var(--text-4xl);
            font-weight: var(--font-bold);
            color: var(--color-white);
            margin-bottom: var(--space-2);
            display: flex;
            align-items: center;
            gap: var(--space-3);
        }
        
        .profile-tagline {
            font-size: var(--text-lg);
            color: var(--color-gray-400);
            margin-bottom: var(--space-3);
        }
        
        .profile-meta {
            display: flex;
            align-items: center;
            gap: var(--space-6);
            flex-wrap: wrap;
        }
        
        .profile-meta-item {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            font-size: var(--text-sm);
            color: var(--color-gray-400);
        }
        
        .profile-meta-item svg {
            width: 16px;
            height: 16px;
            color: var(--color-gray-500);
        }
        
        .profile-rating {
            display: flex;
            align-items: center;
            gap: var(--space-1);
        }
        
        .profile-rating .star {
            color: #FFB800;
            width: 18px;
            height: 18px;
        }
        
        .profile-rating-value {
            font-weight: var(--font-semibold);
            color: var(--color-white);
            margin-left: var(--space-2);
        }
        
        .profile-rating-count {
            color: var(--color-gray-500);
            margin-left: var(--space-1);
        }
        
        .profile-actions {
            display: flex;
            gap: var(--space-3);
            flex-shrink: 0;
        }
        
        /* Main Content Layout */
        .profile-main {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 var(--space-8);
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: var(--space-8);
        }
        
        /* Content Sections */
        .profile-section {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-2xl);
            padding: var(--space-8);
            margin-bottom: var(--space-6);
        }
        
        .section-title {
            font-size: var(--text-xl);
            font-weight: var(--font-bold);
            color: var(--color-white);
            margin-bottom: var(--space-6);
            display: flex;
            align-items: center;
            gap: var(--space-3);
        }
        
        .section-title svg {
            width: 24px;
            height: 24px;
            color: var(--color-primary);
        }
        
        /* About Section */
        .profile-description {
            font-size: var(--text-base);
            line-height: 1.7;
            color: var(--color-gray-300);
        }
        
        /* Services Grid */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: var(--space-4);
        }
        
        .service-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            padding: var(--space-5);
            transition: all var(--transition-base);
            cursor: pointer;
        }
        
        .service-card:hover {
            background: rgba(255, 255, 255, 0.04);
            border-color: var(--color-primary-subtle);
            transform: translateY(-2px);
        }
        
        .service-icon {
            width: 48px;
            height: 48px;
            background: var(--color-primary-subtle);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: var(--space-4);
        }
        
        .service-icon svg {
            width: 24px;
            height: 24px;
            color: var(--color-primary);
        }
        
        .service-name {
            font-size: var(--text-lg);
            font-weight: var(--font-semibold);
            color: var(--color-white);
            margin-bottom: var(--space-2);
        }
        
        .service-description {
            font-size: var(--text-sm);
            color: var(--color-gray-400);
            margin-bottom: var(--space-3);
        }
        
        .service-price {
            font-size: var(--text-base);
            font-weight: var(--font-semibold);
            color: var(--color-primary);
        }
        
        /* Gallery */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--space-3);
        }
        
        .gallery-item {
            position: relative;
            aspect-ratio: 4/3;
            border-radius: var(--radius-lg);
            overflow: hidden;
            cursor: pointer;
        }
        
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform var(--transition-base);
        }
        
        .gallery-item:hover img {
            transform: scale(1.05);
        }
        
        .gallery-item-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity var(--transition-base);
        }
        
        .gallery-item:hover .gallery-item-overlay {
            opacity: 1;
        }
        
        .gallery-item-overlay svg {
            width: 32px;
            height: 32px;
            color: var(--color-white);
        }
        
        /* Portfolio */
        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--space-4);
        }
        
        .portfolio-item {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            overflow: hidden;
            transition: all var(--transition-base);
            cursor: pointer;
        }
        
        .portfolio-item:hover {
            transform: translateY(-4px);
            border-color: var(--color-primary-subtle);
            box-shadow: 0 12px 40px rgba(255, 107, 53, 0.1);
        }
        
        .portfolio-image {
            aspect-ratio: 16/10;
            overflow: hidden;
        }
        
        .portfolio-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform var(--transition-base);
        }
        
        .portfolio-item:hover .portfolio-image img {
            transform: scale(1.05);
        }
        
        .portfolio-content {
            padding: var(--space-5);
        }
        
        .portfolio-title {
            font-size: var(--text-lg);
            font-weight: var(--font-semibold);
            color: var(--color-white);
            margin-bottom: var(--space-2);
        }
        
        .portfolio-description {
            font-size: var(--text-sm);
            color: var(--color-gray-400);
            margin-bottom: var(--space-3);
        }
        
        .portfolio-tags {
            display: flex;
            gap: var(--space-2);
            flex-wrap: wrap;
        }
        
        .portfolio-tag {
            font-size: var(--text-xs);
            padding: var(--space-1) var(--space-3);
            background: var(--color-primary-subtle);
            color: var(--color-primary);
            border-radius: var(--radius-full);
        }
        
        /* Reviews */
        .reviews-list {
            display: flex;
            flex-direction: column;
            gap: var(--space-4);
        }
        
        .review-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            padding: var(--space-5);
        }
        
        .review-header {
            display: flex;
            align-items: center;
            gap: var(--space-4);
            margin-bottom: var(--space-4);
        }
        
        .reviewer-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .reviewer-info {
            flex: 1;
        }
        
        .reviewer-name {
            font-weight: var(--font-semibold);
            color: var(--color-white);
        }
        
        .review-date {
            font-size: var(--text-sm);
            color: var(--color-gray-500);
        }
        
        .review-rating {
            display: flex;
            gap: 2px;
        }
        
        .review-rating .star {
            width: 16px;
            height: 16px;
            color: #FFB800;
        }
        
        .review-rating .star.empty {
            color: var(--color-gray-700);
        }
        
        .review-text {
            font-size: var(--text-sm);
            line-height: 1.6;
            color: var(--color-gray-300);
        }
        
        /* Sidebar */
        .profile-sidebar {
            display: flex;
            flex-direction: column;
            gap: var(--space-6);
        }
        
        /* Contact Card */
        .contact-card {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-2xl);
            padding: var(--space-6);
        }
        
        .contact-price {
            text-align: center;
            margin-bottom: var(--space-6);
        }
        
        .price-label {
            font-size: var(--text-sm);
            color: var(--color-gray-500);
            margin-bottom: var(--space-1);
        }
        
        .price-value {
            font-size: var(--text-3xl);
            font-weight: var(--font-bold);
            color: var(--color-white);
        }
        
        .price-period {
            font-size: var(--text-sm);
            color: var(--color-gray-500);
        }
        
        .contact-buttons {
            display: flex;
            flex-direction: column;
            gap: var(--space-3);
            margin-bottom: var(--space-6);
        }
        
        /* Info List */
        .info-list {
            display: flex;
            flex-direction: column;
            gap: var(--space-4);
        }
        
        .info-item {
            display: flex;
            align-items: flex-start;
            gap: var(--space-3);
        }
        
        .info-icon {
            width: 20px;
            height: 20px;
            color: var(--color-primary);
            flex-shrink: 0;
            margin-top: 2px;
        }
        
        .info-content {
            flex: 1;
        }
        
        .info-label {
            font-size: var(--text-xs);
            color: var(--color-gray-500);
            text-transform: uppercase;
            letter-spacing: var(--tracking-wide);
            margin-bottom: var(--space-1);
        }
        
        .info-value {
            font-size: var(--text-sm);
            color: var(--color-gray-300);
        }
        
        /* Social Links */
        .social-links {
            display: flex;
            gap: var(--space-2);
        }
        
        .social-link {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg);
            color: var(--color-gray-400);
            transition: all var(--transition-base);
        }
        
        .social-link:hover {
            background: var(--color-primary-subtle);
            border-color: var(--color-primary);
            color: var(--color-primary);
        }
        
        .social-link svg {
            width: 20px;
            height: 20px;
        }
        
        /* Skills Tags */
        .skills-tags {
            display: flex;
            flex-wrap: wrap;
            gap: var(--space-2);
        }
        
        .skill-tag {
            padding: var(--space-2) var(--space-4);
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-full);
            font-size: var(--text-sm);
            color: var(--color-gray-300);
            transition: all var(--transition-base);
        }
        
        .skill-tag:hover {
            background: var(--color-primary-subtle);
            border-color: var(--color-primary);
            color: var(--color-primary);
        }
        
        /* Lazy Loading */
        .lazy-load {
            opacity: 0;
            transition: opacity 0.6s ease;
        }
        
        .lazy-load.loaded {
            opacity: 1;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .profile-main {
                grid-template-columns: 1fr;
            }
            
            .profile-header-content {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .profile-actions {
                width: 100%;
                margin-top: var(--space-4);
            }
        }
        
        @media (max-width: 640px) {
            .profile-banner {
                height: 200px;
            }
            
            .profile-avatar {
                width: 120px;
                height: 120px;
            }
            
            .profile-name {
                font-size: var(--text-2xl);
            }
            
            .gallery-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .portfolio-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="bg-matte">
    <!-- Navbar -->
    <?php include __DIR__ . '/../components/navbar.php'; ?>
    
    <main class="provider-profile">
        <!-- Banner -->
        <div class="provider-banner" id="providerBanner">
            <img src="https://images.unsplash.com/photo-1518770660439-4636190af475?w=1920&h=480&fit=crop" alt="Banner da Empresa" loading="lazy">
            <div class="provider-banner-overlay"></div>
        </div>
        
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-header-content">
                <div class="profile-avatar-wrapper">
                    <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400&h=400&fit=crop" alt="Logo da Empresa" class="profile-avatar" loading="lazy">
                    <div class="profile-verified-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                </div>
                
                <div class="profile-info">
                    <h1 class="profile-name">
                        <?= htmlspecialchars($company['name']) ?>
                        <span class="profile-verified-text" style="font-size: var(--text-sm); color: var(--color-primary);">Verificado</span>
                    </h1>
                    <p class="profile-tagline"><?= htmlspecialchars($company['tagline']) ?></p>
                    
                    <div class="profile-meta">
                        <div class="profile-meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <?= htmlspecialchars($company['city']) ?>
                        </div>
                        
                        <div class="profile-meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            Responde em até 2 horas
                        </div>
                        
                        <div class="profile-rating">
                            <svg class="star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <svg class="star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <svg class="star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <svg class="star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <svg class="star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <span class="profile-rating-value">4.9</span>
                            <span class="profile-rating-count">(127 avaliações)</span>
                        </div>
                        
                        <div class="profile-meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            <?= htmlspecialchars($company['completed']) ?>
                        </div>
                    </div>
                </div>
                
                <div class="profile-actions">
                    <button class="btn btn-secondary" id="favoriteBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        Salvar
                    </button>
                    <button class="btn btn-secondary" id="shareBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="18" cy="5" r="3"></circle>
                            <circle cx="6" cy="12" r="3"></circle>
                            <circle cx="18" cy="19" r="3"></circle>
                            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                            <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                        </svg>
                        Compartilhar
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="profile-main">
            <!-- Left Column -->
            <div class="profile-content">
                <!-- About -->
                <section class="profile-section lazy-load">
                    <h2 class="section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        Sobre
                    </h2>
                    <div class="profile-description">
                        <p><?= nl2br(htmlspecialchars($company['about'])) ?></p>
                        <br>
                        <p>Especializamos em criar produtos digitais belos, funcionais e escaláveis que impulsionam o crescimento dos negócios. Nossa abordagem combina tecnologia de ponta com design centrado no usuário para entregar experiências que realmente importam.</p>
                    </div>
                    
                    <div style="margin-top: var(--space-6);">
                        <h3 style="font-size: var(--text-base); font-weight: var(--font-semibold); color: var(--color-white); margin-bottom: var(--space-4);">Habilidades e Especialidades</h3>
                        <div class="skills-tags">
                            <span class="skill-tag">Desenvolvimento Web</span>
                            <span class="skill-tag">Apps Móveis</span>
                            <span class="skill-tag">Design UI/UX</span>
                            <span class="skill-tag">React</span>
                            <span class="skill-tag">Node.js</span>
                            <span class="skill-tag">Python</span>
                            <span class="skill-tag">AWS</span>
                            <span class="skill-tag">DevOps</span>
                            <span class="skill-tag">Machine Learning</span>
                            <span class="skill-tag">Blockchain</span>
                        </div>
                    </div>
                </section>
                
                <!-- Services -->
                <section class="profile-section lazy-load">
                    <h2 class="section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                        Serviços
                    </h2>
                    <div class="services-grid">
                        <?php if (!empty($services)): ?>
                            <?php foreach ($services as $service): ?>
                                <div class="service-card">
                                    <div class="service-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="16 18 22 12 16 6"></polyline>
                                            <polyline points="8 6 2 12 8 18"></polyline>
                                        </svg>
                                    </div>
                                    <h3 class="service-name"><?= htmlspecialchars($service['title']) ?></h3>
                                    <p class="service-description"><?= htmlspecialchars($service['description']) ?></p>
                                    <span class="service-price">
                                        <?php if (!empty($service['price_min'])): ?>
                                            <?= 'A partir de R$ ' . number_format($service['price_min'], 2, ',', '.') ?>
                                        <?php elseif (!empty($service['price_max'])): ?>
                                            <?= 'Até R$ ' . number_format($service['price_max'], 2, ',', '.') ?>
                                        <?php else: ?>
                                            Preço sob consulta
                                        <?php endif; ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="service-card">
                                <div class="service-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="16 18 22 12 16 6"></polyline>
                                        <polyline points="8 6 2 12 8 18"></polyline>
                                    </svg>
                                </div>
                                <h3 class="service-name">Desenvolvimento Web Personalizado</h3>
                                <p class="service-description">Sites e aplicações web sob medida construídos com tecnologias modernas.</p>
                                <span class="service-price">A partir de R$ 5.000</span>
                            </div>
                            
                            <div class="service-card">
                                <div class="service-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                                        <line x1="12" y1="18" x2="12.01" y2="18"></line>
                                    </svg>
                                </div>
                                <h3 class="service-name">Consultoria Estratégica de TI</h3>
                                <p class="service-description">Análises e recomendações de tecnologia para acelerar sua transformação digital.</p>
                                <span class="service-price">A partir de R$ 3.500</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
                
                <!-- Gallery -->
                <section class="profile-section lazy-load">
                    <h2 class="section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                        Galeria do Escritório
                    </h2>
                    <div class="gallery-grid">
                        <div class="gallery-item">
                            <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?w=600&h=450&fit=crop" alt="Office Space" loading="lazy">
                            <div class="gallery-item-overlay">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </div>
                        </div>
                        <div class="gallery-item">
                            <img src="https://images.unsplash.com/photo-1531403009284-440f080d1e12?w=600&h=450&fit=crop" alt="Team Meeting" loading="lazy">
                            <div class="gallery-item-overlay">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </div>
                        </div>
                        <div class="gallery-item">
                            <img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=600&h=450&fit=crop" alt="Workspace" loading="lazy">
                            <div class="gallery-item-overlay">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </div>
                        </div>
                        <div class="gallery-item">
                            <img src="https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=600&h=450&fit=crop" alt="Team Collaboration" loading="lazy">
                            <div class="gallery-item-overlay">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </div>
                        </div>
                        <div class="gallery-item">
                            <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=600&h=450&fit=crop" alt="Development Team" loading="lazy">
                            <div class="gallery-item-overlay">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </div>
                        </div>
                        <div class="gallery-item">
                            <img src="https://images.unsplash.com/photo-1551836022-d5d88e9218df?w=600&h=450&fit=crop" alt="Modern Office" loading="lazy">
                            <div class="gallery-item-overlay">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </div>
                        </div>
                    </div>
                </section>
                
                <!-- Portfolio -->
                <section class="profile-section lazy-load">
                    <h2 class="section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                        </svg>
                        Portfólio
                    </h2>
                    <div class="portfolio-grid">
                        <div class="portfolio-item">
                            <div class="portfolio-image">
                                <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&h=500&fit=crop" alt="Plataforma de E-commerce" loading="lazy">
                            </div>
                            <div class="portfolio-content">
                                <h3 class="portfolio-title">ShopFlow Plataforma de E-commerce</h3>
                                <p class="portfolio-description">Uma solução completa de comércio eletrônico com gerenciamento avançado de estoque e recomendações com IA.</p>
                                <div class="portfolio-tags">
                                    <span class="portfolio-tag">React</span>
                                    <span class="portfolio-tag">Node.js</span>
                                    <span class="portfolio-tag">MongoDB</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="portfolio-item">
                            <div class="portfolio-image">
                                <img src="https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=800&h=500&fit=crop" alt="App Bancário Móvel" loading="lazy">
                            </div>
                            <div class="portfolio-content">
                                <h3 class="portfolio-title">FinanceHub App Móvel</h3>
                                <p class="portfolio-description">Aplicativo bancário seguro com autenticação biométrica e transações em tempo real.</p>
                                <div class="portfolio-tags">
                                    <span class="portfolio-tag">React Native</span>
                                    <span class="portfolio-tag">TypeScript</span>
                                    <span class="portfolio-tag">Firebase</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="portfolio-item">
                            <div class="portfolio-image">
                                <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&h=500&fit=crop" alt="Painel Analítico" loading="lazy">
                            </div>
                            <div class="portfolio-content">
                                <h3 class="portfolio-title">DataViz Painel Analítico</h3>
                                <p class="portfolio-description">Painel de análise em tempo real com visualizações interativas e relatórios automatizados.</p>
                                <div class="portfolio-tags">
                                    <span class="portfolio-tag">Vue.js</span>
                                    <span class="portfolio-tag">D3.js</span>
                                    <span class="portfolio-tag">Python</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="portfolio-item">
                            <div class="portfolio-image">
                                <img src="https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&h=500&fit=crop" alt="Plataforma de Saúde" loading="lazy">
                            </div>
                            <div class="portfolio-content">
                                <h3 class="portfolio-title">MedConnect Plataforma de Saúde</h3>
                                <p class="portfolio-description">Plataforma de telemedicina compatível com normas de privacidade, conectando pacientes a profissionais de saúde.</p>
                                <div class="portfolio-tags">
                                    <span class="portfolio-tag">Angular</span>
                                    <span class="portfolio-tag">.NET Core</span>
                                    <span class="portfolio-tag">Azure</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                
                <!-- Reviews -->
                <section class="profile-section lazy-load">
                    <h2 class="section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        Avaliações (127)
                    </h2>
                    <div class="reviews-list">
                        <div class="review-card">
                            <div class="review-header">
                                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop" alt="Sarah Johnson" class="reviewer-avatar" loading="lazy">
                                <div class="reviewer-info">
                                    <h4 class="reviewer-name">Sarah Johnson</h4>
                                    <span class="review-date">Dezembro de 2024</span>
                                </div>
                                <div class="review-rating">
                                    <svg class="star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <svg class="star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <svg class="star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <svg class="star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <svg class="star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                </div>
                            </div>
                            <p class="review-text">"A <?= htmlspecialchars($company['name']) ?> superou nossas expectativas. Entregaram uma plataforma de e-commerce impressionante que aumentou nossas vendas em 40%. A equipe é profissional, ágil e realmente se importa com o sucesso dos clientes."</p>
                        </div>
                        
                        <div class="review-card">
                            <div class="review-header">
                                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop" alt="Michael Chen" class="reviewer-avatar" loading="lazy">
                                <div class="reviewer-info">
                                    <h4 class="reviewer-name">Michael Chen</h4>
                                    <span class="review-date">Novembro de 2024</span>
                                </div>
                                <div class="review-rating">
                                    <svg class="star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <svg class="star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <svg class="star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <svg class="star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <svg class="star empty" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                </div>
                            </div>
                            <p class="review-text">"Ótima experiência com a TechVision. Construíram nosso aplicativo móvel do zero e o resultado foi fantástico. Houve pequenos atrasos, mas a qualidade compensou. Recomendamos!"</p>
                        </div>
                        
                        <div class="review-card">
                            <div class="review-header">
                                <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop" alt="Emily Rodriguez" class="reviewer-avatar" loading="lazy">
                                <div class="reviewer-info">
                                    <h4 class="reviewer-name">Emily Rodriguez</h4>
                                    <span class="review-date">Outubro de 2024</span>
                                </div>
                                <div class="review-rating">
                                    <svg class="star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <svg class="star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <svg class="star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <svg class="star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <svg class="star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                </div>
                            </div>
                            <p class="review-text">"Trabalho excepcional na nossa migração para a nuvem. A expertise em AWS e DevOps ajudou a reduzir os custos de infraestrutura em 35% e melhorar a performance. Muito profissional!"</p>
                        </div>
                    </div>
                    
                    <button class="btn btn-secondary w-full" style="margin-top: var(--space-6);">Carregar Mais Avaliações</button>
                </section>
            </div>
            
            <!-- Right Sidebar -->
            <aside class="profile-sidebar">
                <!-- Contact Card -->
                <div class="contact-card sticky" style="position: sticky; top: var(--space-8);">
                    <div class="contact-price">
                        <span class="price-label">Taxa por Hora</span>
                        <div class="price-value">R$ 120<span class="price-period">/hora</span></div>
                    </div>
                    
                    <div class="contact-buttons">
                        <button class="btn btn-primary btn-lg w-full">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                            Enviar Mensagem
                        </button>
                        <button class="btn btn-secondary btn-lg w-full">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            Agendar Chamada
                        </button>
                        <button class="btn btn-outline btn-lg w-full">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            Solicitar Orçamento
                        </button>
                    </div>
                    
                    <div class="info-list">
                        <div class="info-item">
                            <svg class="info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            <div class="info-content">
                                <span class="info-label">Telefone</span>
                                <span class="info-value">+1 (555) 123-4567</span>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <svg class="info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <div class="info-content">
                                <span class="info-label">E-mail</span>
                                <span class="info-value">hello@techvision.studio</span>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <svg class="info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <div class="info-content">
                                <span class="info-label">Tempo de Resposta</span>
                                <span class="info-value">Em até 2 horas</span>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <svg class="info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <div class="info-content">
                                <span class="info-label">Localização</span>
                                <span class="info-value">San Francisco, CA</span>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-top: var(--space-6);">
                        <span class="info-label" style="margin-bottom: var(--space-3); display: block;">Conectar</span>
                        <div class="social-links">
                            <a href="#" class="social-link" aria-label="Website">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="2" y1="12" x2="22" y2="12"></line>
                                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                                </svg>
                            </a>
                            <a href="#" class="social-link" aria-label="LinkedIn">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
                                    <rect x="2" y="9" width="4" height="12"></rect>
                                    <circle cx="4" cy="4" r="2"></circle>
                                </svg>
                            </a>
                            <a href="#" class="social-link" aria-label="Twitter">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path>
                                </svg>
                            </a>
                            <a href="#" class="social-link" aria-label="GitHub">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path>
                                </svg>
                            </a>
                            <a href="#" class="social-link" aria-label="Dribbble">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M8.56 2.75c4.37 6.03 6.02 9.42 8.03 17.72m2.54-15.38c-3.72 4.35-8.94 5.66-16.88 5.85m19.5 1.9c-3.5-.93-6.63-.82-8.94 0-2.58.92-5.01 2.86-7.44 6.32"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </main>
    
    <!-- Footer -->
    <?php include __DIR__ . '/../components/footer.php'; ?>
    
    <script>
        // Lazy loading with Intersection Observer
        const lazyElements = document.querySelectorAll('.lazy-load');
        
        const lazyObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('loaded');
                    lazyObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '50px'
        });
        
        lazyElements.forEach(el => lazyObserver.observe(el));
        
        // Lazy load images
        const lazyImages = document.querySelectorAll('img[loading="lazy"]');
        lazyImages.forEach(img => {
            img.addEventListener('load', () => {
                img.classList.add('loaded');
            });
        });
        
        // Favorite button toggle
        const favoriteBtn = document.getElementById('favoriteBtn');
        let isFavorited = false;
        
        favoriteBtn.addEventListener('click', () => {
            isFavorited = !isFavorited;
            if (isFavorited) {
                favoriteBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                    Salvo
                `;
                favoriteBtn.classList.add('btn-primary');
                favoriteBtn.classList.remove('btn-secondary');
            } else {
                favoriteBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                    Salvar
                `;
                favoriteBtn.classList.remove('btn-primary');
                favoriteBtn.classList.add('btn-secondary');
            }
        });
        
        // Share button
        const shareBtn = document.getElementById('shareBtn');
        shareBtn.addEventListener('click', () => {
            if (navigator.share) {
                navigator.share({
                    title: '<?= addslashes($company['name']) ?> - NEXAR',
                    text: 'Confira esta agência digital incrível no NEXAR!',
                    url: window.location.href
                });
            } else {
                navigator.clipboard.writeText(window.location.href);
                alert('Link copiado para a área de transferência!');
            }
        });
        
        // Gallery lightbox (simplified)
        document.querySelectorAll('.gallery-item').forEach(item => {
            item.addEventListener('click', () => {
                const img = item.querySelector('img');
                const src = img.src.replace('w=600', 'w=1200').replace('h=450', 'h=900');
                // In a real implementation, this would open a lightbox
                window.open(src, '_blank');
            });
        });
    </script>
</body>
</html>