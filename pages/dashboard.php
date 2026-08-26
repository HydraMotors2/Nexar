<?php require_once __DIR__ . '/../php/auth.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include __DIR__ . '/../components/favicon.php'; ?>
    <title>Painel - NEXAR</title>
    <meta name="description" content="Seu painel NEXAR">
    <meta name="csrf-token" content="<?php echo Auth::csrfToken(); ?>">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="/NEXAR/public/css/variables.css">
    <link rel="stylesheet" href="/NEXAR/public/css/global.css">
    <link rel="stylesheet" href="/NEXAR/public/css/components.css">
    <link rel="stylesheet" href="/NEXAR/public/css/animations.css">
    
    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed: 80px;
            --header-height: 70px;
        }
        
        .dashboard {
            min-height: 100vh;
            background: var(--color-black-matte);
            display: flex;
        }
        
        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: rgba(15, 15, 15, 0.95);
            backdrop-filter: var(--glass-blur);
            border-right: 1px solid var(--glass-border);
            z-index: 100;
            transition: width var(--transition-base);
            display: flex;
            flex-direction: column;
        }
        
        .sidebar.collapsed {
            width: var(--sidebar-collapsed);
        }
        
        .sidebar-header {
            padding: var(--space-6);
            border-bottom: 1px solid var(--glass-border);
        }
        
        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            font-size: var(--text-xl);
            font-weight: var(--font-bold);
            color: var(--color-white);
            text-decoration: none;
        }
        
        .sidebar-logo svg {
            width: 32px;
            height: 32px;
            color: var(--color-primary);
        }
        
        .sidebar-logo span {
            transition: opacity var(--transition-base);
        }
        
        .sidebar.collapsed .sidebar-logo span {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        
        .sidebar-nav {
            flex: 1;
            padding: var(--space-4);
            overflow-y: auto;
        }
        
        .nav-section {
            margin-bottom: var(--space-6);
        }
        
        .nav-section-title {
            font-size: var(--text-xs);
            text-transform: uppercase;
            letter-spacing: var(--tracking-wider);
            color: var(--color-gray-500);
            padding: var(--space-2) var(--space-3);
            margin-bottom: var(--space-2);
        }
        
        .sidebar.collapsed .nav-section-title {
            display: none;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-3);
            border-radius: var(--radius-lg);
            color: var(--color-gray-400);
            text-decoration: none;
            transition: all var(--transition-fast);
            margin-bottom: var(--space-1);
        }
        
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--color-white);
        }
        
        .nav-item.active {
            background: var(--color-primary-subtle);
            color: var(--color-primary);
        }
        
        .nav-item svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        
        .nav-item span {
            font-size: var(--text-sm);
            font-weight: var(--font-medium);
            white-space: nowrap;
            transition: opacity var(--transition-base);
        }
        
        .sidebar.collapsed .nav-item span {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        
        .nav-badge {
            margin-left: auto;
            background: var(--color-error);
            color: white;
            font-size: var(--text-xs);
            padding: 2px 8px;
            border-radius: var(--radius-full);
            font-weight: var(--font-semibold);
        }
        
        .sidebar.collapsed .nav-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            padding: 2px 6px;
            font-size: 10px;
        }
        
        .sidebar-footer {
            padding: var(--space-4);
            border-top: 1px solid var(--glass-border);
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-3);
            border-radius: var(--radius-lg);
            cursor: pointer;
            transition: background var(--transition-fast);
        }
        
        .user-menu:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-lg);
            object-fit: cover;
        }
        
        .user-info {
            flex: 1;
            min-width: 0;
        }
        
        .user-name {
            font-size: var(--text-sm);
            font-weight: var(--font-semibold);
            color: var(--color-white);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .user-email {
            font-size: var(--text-xs);
            color: var(--color-gray-500);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .sidebar.collapsed .user-info {
            display: none;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left var(--transition-base);
        }
        
        .sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-collapsed);
        }
        
        /* Header */
        .dashboard-header {
            height: var(--header-height);
            padding: 0 var(--space-8);
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--glass-border);
            background: rgba(15, 15, 15, 0.8);
            backdrop-filter: var(--glass-blur);
            position: sticky;
            top: 0;
            z-index: 50;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }
        
        .sidebar-toggle {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg);
            color: var(--color-gray-400);
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        
        .sidebar-toggle:hover {
            background: rgba(255, 255, 255, 0.06);
            color: var(--color-white);
        }
        
        .page-title {
            font-size: var(--text-xl);
            font-weight: var(--font-semibold);
            color: var(--color-white);
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }
        
        .search-box {
            position: relative;
            width: 300px;
        }
        
        .search-box input {
            width: 100%;
            padding: var(--space-2) var(--space-4);
            padding-left: var(--space-10);
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg);
            color: var(--color-white);
            font-size: var(--text-sm);
        }
        
        .search-box input:focus {
            outline: none;
            border-color: var(--color-primary);
        }
        
        .search-box svg {
            position: absolute;
            left: var(--space-3);
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            color: var(--color-gray-500);
        }
        
        .notification-btn {
            position: relative;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg);
            color: var(--color-gray-400);
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        
        .notification-btn:hover {
            background: rgba(255, 255, 255, 0.06);
            color: var(--color-white);
        }
        
        .notification-dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            background: var(--color-error);
            border-radius: 50%;
        }
        
        /* Dashboard Content */
        .dashboard-content {
            padding: var(--space-8);
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: var(--space-4);
            margin-bottom: var(--space-8);
        }
        
        .stat-card {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            padding: var(--space-6);
            transition: all var(--transition-base);
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            border-color: var(--color-primary-subtle);
            box-shadow: 0 8px 32px rgba(255, 107, 53, 0.1);
        }
        
        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--space-4);
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .stat-icon.primary { background: var(--color-primary-subtle); color: var(--color-primary); }
        .stat-icon.secondary { background: rgba(0, 212, 170, 0.1); color: var(--color-secondary); }
        .stat-icon.warning { background: rgba(255, 184, 0, 0.1); color: #FFB800; }
        .stat-icon.error { background: rgba(239, 68, 68, 0.1); color: var(--color-error); }
        
        .stat-trend {
            display: flex;
            align-items: center;
            gap: var(--space-1);
            font-size: var(--text-sm);
            font-weight: var(--font-medium);
        }
        
        .stat-trend.up { color: var(--color-secondary); }
        .stat-trend.down { color: var(--color-error); }
        
        .stat-value {
            font-size: var(--text-3xl);
            font-weight: var(--font-bold);
            color: var(--color-white);
            margin-bottom: var(--space-1);
        }
        
        .stat-label {
            font-size: var(--text-sm);
            color: var(--color-gray-500);
        }
        
        /* Charts Section */
        .charts-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: var(--space-6);
            margin-bottom: var(--space-8);
        }
        
        .chart-card {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            padding: var(--space-6);
        }
        
        .chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--space-6);
        }
        
        .chart-title {
            font-size: var(--text-lg);
            font-weight: var(--font-semibold);
            color: var(--color-white);
        }
        
        .chart-filter {
            display: flex;
            gap: var(--space-2);
        }
        
        .filter-btn {
            padding: var(--space-1) var(--space-3);
            font-size: var(--text-xs);
            font-weight: var(--font-medium);
            color: var(--color-gray-400);
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: var(--color-primary-subtle);
            border-color: var(--color-primary);
            color: var(--color-primary);
        }
        
        .chart-placeholder {
            height: 300px;
            background: rgba(255, 255, 255, 0.02);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-gray-600);
        }
        
        /* Recent Activity */
        .activity-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: var(--space-6);
        }
        
        .activity-card {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            padding: var(--space-6);
        }
        
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--space-6);
        }
        
        .section-title {
            font-size: var(--text-lg);
            font-weight: var(--font-semibold);
            color: var(--color-white);
        }
        
        .view-all-btn {
            font-size: var(--text-sm);
            color: var(--color-primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: var(--space-1);
        }
        
        .view-all-btn:hover {
            color: var(--color-primary-light);
        }
        
        /* Activity List */
        .activity-list {
            display: flex;
            flex-direction: column;
            gap: var(--space-4);
        }
        
        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: var(--space-4);
            padding: var(--space-4);
            background: rgba(255, 255, 255, 0.02);
            border-radius: var(--radius-lg);
            transition: background var(--transition-fast);
        }
        
        .activity-item:hover {
            background: rgba(255, 255, 255, 0.04);
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-title {
            font-size: var(--text-sm);
            font-weight: var(--font-medium);
            color: var(--color-white);
            margin-bottom: var(--space-1);
        }
        
        .activity-description {
            font-size: var(--text-sm);
            color: var(--color-gray-500);
        }
        
        .activity-time {
            font-size: var(--text-xs);
            color: var(--color-gray-600);
            white-space: nowrap;
        }
        
        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--space-3);
        }
        
        .quick-action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-4);
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg);
            color: var(--color-gray-400);
            text-decoration: none;
            transition: all var(--transition-fast);
        }
        
        .quick-action-btn:hover {
            background: var(--color-primary-subtle);
            border-color: var(--color-primary);
            color: var(--color-primary);
            transform: translateY(-2px);
        }
        
        .quick-action-btn svg {
            width: 24px;
            height: 24px;
        }
        
        .quick-action-btn span {
            font-size: var(--text-xs);
            font-weight: var(--font-medium);
        }
        
        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: var(--space-6);
            right: var(--space-6);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: var(--space-3);
        }
        
        .toast {
            background: var(--color-black-light);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg);
            padding: var(--space-4) var(--space-5);
            display: flex;
            align-items: center;
            gap: var(--space-3);
            min-width: 320px;
            animation: slideInRight 0.3s ease;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
        }
        
        .toast.success { border-left: 4px solid var(--color-secondary); }
        .toast.error { border-left: 4px solid var(--color-error); }
        .toast.warning { border-left: 4px solid #FFB800; }
        .toast.info { border-left: 4px solid var(--color-primary); }
        
        .toast-icon {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
        }
        
        .toast.success .toast-icon { color: var(--color-secondary); }
        .toast.error .toast-icon { color: var(--color-error); }
        .toast.warning .toast-icon { color: #FFB800; }
        .toast.info .toast-icon { color: var(--color-primary); }
        
        .toast-content {
            flex: 1;
        }
        
        .toast-title {
            font-size: var(--text-sm);
            font-weight: var(--font-semibold);
            color: var(--color-white);
        }
        
        .toast-message {
            font-size: var(--text-xs);
            color: var(--color-gray-400);
        }
        
        .toast-close {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-gray-500);
            cursor: pointer;
            transition: color var(--transition-fast);
        }
        
        .toast-close:hover {
            color: var(--color-white);
        }
        
        /* Skeleton Loading */
        .skeleton {
            background: linear-gradient(90deg, 
                rgba(255, 255, 255, 0.03) 25%, 
                rgba(255, 255, 255, 0.06) 50%, 
                rgba(255, 255, 255, 0.03) 75%
            );
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: var(--radius-md);
        }
        
        .skeleton-text {
            height: 16px;
            margin-bottom: var(--space-2);
        }
        
        .skeleton-text.short {
            width: 60%;
        }
        
        .skeleton-card {
            height: 120px;
        }
        
        /* Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
            z-index: 200;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all var(--transition-base);
        }
        
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .modal {
            background: #1A1A1A;
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-2xl);
            padding: var(--space-8);
            max-width: 500px;
            width: 90%;
            transform: scale(0.95);
            transition: transform var(--transition-base);
        }
        
        .modal-overlay.active .modal {
            transform: scale(1);
        }
        
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--space-6);
        }
        
        .modal-title {
            font-size: var(--text-xl);
            font-weight: var(--font-bold);
            color: var(--color-white);
        }
        
        .modal-close {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-gray-500);
            cursor: pointer;
            transition: color var(--transition-fast);
        }
        
        .modal-close:hover {
            color: var(--color-white);
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .charts-section, .activity-section {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .search-box {
                display: none;
            }
            
            .dashboard-content {
                padding: var(--space-4);
            }
        }
    </style>
</head>
<body class="bg-matte">
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>
    
    <!-- Modal Overlay -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">Criar Novo Projeto</h2>
                <button class="modal-close" onclick="closeModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <form id="createForm">
                <div class="form-group" style="margin-bottom: var(--space-4);">
                    <label style="display: block; font-size: var(--text-sm); color: var(--color-gray-300); margin-bottom: var(--space-2);">Título do Projeto</label>
                    <input type="text" class="form-input" placeholder="Insira o título do projeto" style="width: 100%; padding: var(--space-3); background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); color: var(--color-white);">
                </div>
                <div class="form-group" style="margin-bottom: var(--space-4);">
                    <label style="display: block; font-size: var(--text-sm); color: var(--color-gray-300); margin-bottom: var(--space-2);">Description</label>
                    <textarea class="form-input" placeholder="Descreva seu projeto" rows="3" style="width: 100%; padding: var(--space-3); background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); color: var(--color-white); resize: vertical;"></textarea>
                </div>
                <div class="form-group" style="margin-bottom: var(--space-6);">
                    <label style="display: block; font-size: var(--text-sm); color: var(--color-gray-300); margin-bottom: var(--space-2);">Budget</label>
                    <select style="width: 100%; padding: var(--space-3); background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); color: var(--color-white);">
                        <option value="">Select budget range</option>
                        <option value="1000-5000">$1,000 - $5,000</option>
                        <option value="5000-10000">$5,000 - $10,000</option>
                        <option value="10000+">$10,000+</option>
                    </select>
                </div>
                <div style="display: flex; gap: var(--space-3); justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Criar Projeto</button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal-overlay" id="messageOverlay">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">Mensagens Recentes</h2>
                <button class="modal-close" onclick="closeMessageModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <div id="messageList" style="display:flex;flex-direction:column;gap:var(--space-3);"></div>
        </div>
    </div>
    <div class="modal-overlay" id="notificationsOverlay">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">Notificações</h2>
                <button class="modal-close" onclick="closeNotificationsModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <div id="notificationsList" style="display:flex;flex-direction:column;gap:var(--space-3);"></div>
            <button class="btn btn-secondary" style="margin-top:var(--space-4);" onclick="markNotificationRead()">Marcar todas como lidas</button>
        </div>
    </div>
    
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="/NEXAR/" class="sidebar-logo">
                    <svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 2L32 10V26L18 34L4 26V10L18 2Z" stroke="currentColor" stroke-width="2" fill="none"/>
                        <path d="M18 8L26 13V23L18 28L10 23V13L18 8Z" fill="currentColor"/>
                        <circle cx="18" cy="18" r="4" fill="#0A0A0A"/>
                    </svg>
                    <span>NEXAR</span>
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Visão Geral</div>
                    <a href="#" class="nav-item active">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        <span>Painel</span>
                    </a>
                    <a href="#" class="nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
                        <span>Análise</span>
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Projetos</div>
                    <a href="#" class="nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                        <span>Meus Projetos</span>
                        <span class="nav-badge">5</span>
                    </a>
                    <a href="#" class="nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span>Tarefas Ativas</span>
                    </a>
                    <a href="#" class="nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                        <span>Propostas</span>
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Mensagens</div>
                    <a href="#" class="nav-item" onclick="openMessageModal(); return false;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        <span>Caixa de Entrada</span>
                        <span class="nav-badge">3</span>
                    </a>
                    <a href="#" class="nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path></svg>
                        <span>Discussões</span>
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Conta</div>
                    <a href="#" class="nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <span>Perfil</span>
                    </a>
                    <a href="#" class="nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        <span>Configurações</span>
                    </a>
                    <a href="/NEXAR/logout" class="nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        <span>Sair</span>
                    </a>
                </div>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-menu">
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop" alt="User" class="user-avatar">
                    <div class="user-info">
                        <div class="user-name">John Doe</div>
                        <div class="user-email">john@example.com</div>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="dashboard-header">
                <div class="header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <h1 class="page-title">Painel</h1>
                </div>
                <div class="header-right">
                    <div class="search-box">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <input id="globalSearch" type="text" placeholder="Buscar projetos, tarefas...">
                    </div>
                    <button class="notification-btn" id="messagesBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        <span class="notification-dot" id="messageDot"></span>
                    </button>
                    <button class="notification-btn" id="notificationBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        <span class="notification-dot" id="notificationDot"></span>
                    </button>
                </div>
            </header>
            
            <!-- Content -->
            <div class="dashboard-content">
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                            </div>
                            <span class="stat-trend up">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                                +12%
                            </span>
                        </div>
                            <div class="stat-value" id="activeProjectsTotal">0</div>
                            <div class="stat-label">Projetos Ativos <span id="activeProjectsGrowth" style="margin-left:8px;font-weight:600;color:var(--color-secondary);">+0%</span></div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                            </div>
                            <span class="stat-trend up">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                                +8.3%
                            </span>
                        </div>
                        <div class="stat-value" id="totalRevenueValue">R$ 0,00</div>
                        <div class="stat-label">Receita Total <span id="totalRevenueGrowth" style="margin-left:8px;font-weight:600;color:var(--color-secondary);">+0%</span></div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon warning">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            </div>
                            <span class="stat-trend up">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                                +24%
                            </span>
                        </div>
                        <div class="stat-value" id="newClientsValue">0</div>
                        <div class="stat-label">Novos Clientes <span id="newClientsGrowth" style="margin-left:8px;font-weight:600;color:var(--color-secondary);">+0%</span></div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon error">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            </div>
                            <span class="stat-trend down">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>
                                -3%
                            </span>
                        </div>
                        <div class="stat-value" id="completionRateValue">0%</div>
                        <div class="stat-label">Taxa de Conclusão</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                            </div>
                            <span class="stat-trend up" id="messageTrend">
                                +0%
                            </span>
                        </div>
                        <div class="stat-value" id="messageInboxCount">0</div>
                        <div class="stat-label">Mensagens na Caixa de Entrada <span id="messageUnreadCount" style="margin-left:8px;font-weight:600;color:var(--color-secondary);">0 não lidas</span></div>
                    </div>
                </div>
                
                <!-- Charts Section -->
                <div class="charts-section">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">Visão Geral de Receita</h3>
                            <div class="chart-filter">
                                <button class="filter-btn active">Semana</button>
                                <button class="filter-btn">Mês</button>
                                <button class="filter-btn">Ano</button>
                            </div>
                        </div>
                        <div class="chart-placeholder">
                            <canvas id="revenueChart" style="width:100%;height:320px"></canvas>
                        </div>
                    </div>
                    
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">Distribuição de Projetos</h3>
                        </div>
                        <div class="chart-placeholder">
                            <canvas id="projectsChart" style="width:100%;height:320px"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Activity Section -->
                <div class="activity-section">
                    <div class="activity-card">
                        <div class="section-header">
                            <h3 class="section-title">Atividade Recente</h3>
                            <a href="#" class="view-all-btn">
                                Ver Todas
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                            </a>
                        </div>
                        <div class="activity-list" id="activityList"></div>
                    </div>
                    
                    <div class="activity-card">
                        <div class="section-header">
                            <h3 class="section-title">Ações Rápidas</h3>
                        </div>
                        <div class="quick-actions">
                            <a href="#" class="quick-action-btn" onclick="openModal(); return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                <span>Novo Projeto</span>
                            </a>
                            <a href="#" class="quick-action-btn" onclick="openMessageModal(); return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                <span>Enviar Mensagem</span>
                            </a>
                            <a href="#" class="quick-action-btn" onclick="openNotificationsModal(); return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                <span>Ver Notificações</span>
                            </a>
                            <a href="#" class="quick-action-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                                <span>Configurações</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });
        
        // Mobile sidebar
        if (window.innerWidth <= 768) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('mobile-open');
            });
        }
        
        // Modal functions
        function openModal() {
            document.getElementById('modalOverlay').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('modalOverlay').classList.remove('active');
        }
        
        document.getElementById('modalOverlay').addEventListener('click', (e) => {
            if (e.target === document.getElementById('modalOverlay')) {
                closeModal();
            }
        });
        
        // Form submission
        document.getElementById('createForm').addEventListener('submit', (e) => {
            e.preventDefault();
            closeModal();
            showToast('success', 'Projeto Criado', 'Seu novo projeto foi criado com sucesso.');
        });
        
        // Toast notification system
        function showToast(type, title, message) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icons = {
                success: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>',
                error: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
                warning: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
                info: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
            };
            
            toast.innerHTML = `
                <span class="toast-icon">${icons[type]}</span>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <span class="toast-close" onclick="this.parentElement.remove()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </span>
            `;
            
            container.appendChild(toast);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                toast.style.animation = 'slideOutRight 0.3s ease forwards';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }
        
        // Filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                btn.parentElement.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });
        
        // Demo: Show welcome toast on load
        setTimeout(() => {
            showToast('info', 'Bem-vindo de volta!', 'Você tem 3 novas mensagens e 2 propostas pendentes.');
        }, 1000);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/NEXAR/public/js/dashboard.js"></script>
</body>
</html>