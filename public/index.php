<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <!-- SEO Meta Tags -->
    <title>NEXAR - Conecte-se com Prestadores de Serviços Premium</title>
    <meta name="description" content="A NEXAR é uma plataforma SaaS moderna que conecta clientes a prestadores de serviços verificados. Encontre profissionais confiáveis para qualquer projeto.">
    <meta name="keywords" content="SaaS, prestadores de serviços, clientes, plataforma, negócios, serviços profissionais">
    <meta name="author" content="Equipe NEXAR">
    
    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="NEXAR - Conecte-se com Prestadores de Serviços Premium">
    <meta property="og:description" content="Conecte-se com prestadores de serviços verificados para qualquer projeto.">
    <meta property="og:image" content="/assets/images/og-image.png">
    <meta property="og:url" content="https://nexar.com">
    <meta name="twitter:card" content="summary_large_image">
    
    <!-- Favicon -->
    <?php include __DIR__ . '/../components/favicon.php'; ?>
    
    <!-- Preload Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/NEXAR/public/css/variables.css">
    <link rel="stylesheet" href="/NEXAR/public/css/global.css">
    <link rel="stylesheet" href="/NEXAR/public/css/components.css">
    <link rel="stylesheet" href="/NEXAR/public/css/animations.css">
    <link rel="stylesheet" href="/NEXAR/public/css/responsive.css">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="https://nexar.com">
</head>
<?php
$loggedIn = false;
$user = null;
$authFile = __DIR__ . '/../php/auth.php';
if (file_exists($authFile)) {
    require_once $authFile;
    $auth = Auth::getInstance();
    if ($auth->check()) {
        $loggedIn = true;
        $user = $auth->user();
    }
}
?>
<body class="bg-matte text-white overflow-x-hidden">
    <!-- Animated Background -->
    <div class="bg-animation" aria-hidden="true">
        <div class="bg-gradient-orb orb-1"></div>
        <div class="bg-gradient-orb orb-2"></div>
        <div class="bg-gradient-orb orb-3"></div>
    </div>
    
    <!-- Navigation -->
    <?php include_once __DIR__ . '/../components/navbar.php'; ?>
    
    <?php if ($loggedIn): ?>
        <section class="home-user-menu" style="padding: 24px 24px 0; width: min(1120px, 100%); margin: 0 auto;">
            <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 28px; padding: 24px 28px; box-shadow: 0 24px 60px rgba(0,0,0,0.12); margin-bottom: 20px;">
                <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; align-items: center;">
                    <div>
                        <h2 style="margin: 0 0 8px; font-size: 1.8rem; color: white;">Bem-vindo de volta, <?= htmlspecialchars($user['first_name'] ?? ($user['email'] ?? 'Usuário'), ENT_QUOTES, 'UTF-8') ?>!</h2>
                        <p style="margin: 0; color: rgba(255,255,255,0.75);">Use os atalhos abaixo para acessar suas páginas rápidas.</p>
                    </div>
                    <div style="display: grid; gap: 10px; grid-template-columns: repeat(auto-fit, minmax(120px, auto)); width: min(480px, 100%);">
                        <a href="/NEXAR/dashboard" style="padding: 12px 16px; border-radius: 999px; text-align: center; background: rgba(255,255,255,0.08); color: white; text-decoration: none; font-weight: 600;">Painel</a>
                        <a href="/NEXAR/profile" style="padding: 12px 16px; border-radius: 999px; text-align: center; background: rgba(255,255,255,0.08); color: white; text-decoration: none; font-weight: 600;">Perfil</a>
                        <a href="/NEXAR/messages" style="padding: 12px 16px; border-radius: 999px; text-align: center; background: rgba(255,255,255,0.08); color: white; text-decoration: none; font-weight: 600;">Mensagens</a>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main id="main-content">
        <!-- Hero Section -->
        <?php include_once __DIR__ . '/../components/hero.php'; ?>
        
        <!-- Features Section -->
        <?php include_once __DIR__ . '/../components/features.php'; ?>
        
        <!-- How It Works -->
        <?php include_once __DIR__ . '/../components/how-it-works.php'; ?>
        
        <!-- Testimonials -->
        <?php include_once __DIR__ . '/../components/testimonials.php'; ?>
        
        <!-- Pricing -->
        <?php include_once __DIR__ . '/../components/pricing.php'; ?>
        
        <!-- CTA Section -->
        <?php include_once __DIR__ . '/../components/cta.php'; ?>
    </main>
    
    <!-- Footer -->
    <?php include_once __DIR__ . '/../components/footer.php'; ?>
    
    <!-- Scripts -->
    <script src="/js/app.js" type="module"></script>
    <script src="/js/animations.js" type="module"></script>
    <script src="/js/navbar.js" type="module"></script>
</body>
</html>