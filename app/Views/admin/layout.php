<!-- app/views/admin/layout.php -->
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin' ?> — Sites da Fábrica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap');
    
    body {
        font-family: 'Outfit', 'Inter', sans-serif;
        display: flex;
        height: 100vh;
        background: #f8fafc;
        margin: 0;
        -webkit-font-smoothing: antialiased;
    }

    .sidebar {
        width: 260px;
        background: #0f172a;
        color: #94a3b8;
        padding: 24px 16px;
        overflow-y: auto;
        position: fixed;
        height: 100vh;
        left: 0;
        top: 0;
        display: flex;
        flex-direction: column;
        border-right: 1px solid #1e293b;
        z-index: 1000;
    }

    .sidebar-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 24px;
        border-bottom: 1px solid #1e293b;
        margin-bottom: 24px;
    }

    .sidebar-logo {
        height: 36px;
        width: 36px;
        background: #2563eb;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
    }

    .sidebar-brand h4 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: white;
        line-height: 1.2;
    }

    .sidebar-brand p {
        margin: 0;
        font-size: 11px;
        color: #64748b;
    }

    .sidebar a {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #94a3b8;
        text-decoration: none;
        padding: 12px 16px;
        border-radius: 12px;
        margin-bottom: 6px;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s;
    }

    .sidebar a:hover {
        background: #1e293b;
        color: white;
    }

    .sidebar a.active {
        background: #2563eb;
        color: white;
        box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
    }

    .sidebar hr {
        border-color: #1e293b;
        margin: 20px 0;
        opacity: 1;
    }

    .main-content {
        margin-left: 260px;
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .topbar {
        background: white;
        padding: 20px 32px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 80px;
    }

    .topbar h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        color: #0f172a;
    }

    .user-menu a {
        margin-left: 10px;
        color: #0ea5e9;
        text-decoration: none;
    }

    .content-area {
        flex: 1;
        overflow-y: auto;
        padding: 32px;
    }

    .card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
    }

    .card-header {
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        font-weight: 600;
    }

    .btn-primary {
        background: #0ea5e9;
        border: none;
    }

    .btn-primary:hover {
        background: #0284c7;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .stat-card h6 {
        color: #6b7280;
        margin-bottom: 10px;
        font-size: 12px;
        text-transform: uppercase;
    }

    .stat-card .number {
        font-size: 28px;
        font-weight: 700;
        color: #1e40af;
    }

    table {
        background: white;
        border-radius: 8px;
        overflow: hidden;
    }

    thead {
        background: #f9fafb;
    }

    @media (max-width: 768px) {
        .sidebar {
            width: 200px;
            font-size: 14px;
        }

        .main-content {
            margin-left: 200px;
        }
    }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fa-solid fa-screwdriver-wrench text-base text-white"></i>
            </div>
            <div class="sidebar-brand">
                <h4>Fábrica Admin</h4>
                <p>Painel Geral</p>
            </div>
        </div>
        
        <a href="/admin" class="<?= basename($_SERVER['REQUEST_URI'], '?') === '/admin' ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-pie text-lg"></i>
            <span>Dashboard</span>
        </a>
        <a href="/admin/templates" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/templates') === 0 ? 'active' : '' ?>">
            <i class="fa-solid fa-cubes text-lg"></i>
            <span>Templates</span>
        </a>
        <a href="/admin/plans" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/plans') === 0 ? 'active' : '' ?>">
            <i class="fa-solid fa-wallet text-lg"></i>
            <span>Planos</span>
        </a>
        <a href="/admin/users" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/users') === 0 ? 'active' : '' ?>">
            <i class="fa-solid fa-users text-lg"></i>
            <span>Usuários</span>
        </a>
        <a href="/admin/projects" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/projects') === 0 ? 'active' : '' ?>">
            <i class="fa-solid fa-folder-open text-lg"></i>
            <span>Projetos</span>
        </a>
        <a href="/admin/subscriptions" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/subscriptions') === 0 ? 'active' : '' ?>">
            <i class="fa-solid fa-rotate text-lg"></i>
            <span>Assinaturas</span>
        </a>
        
        <hr>
        
        <a href="/projects">
            <i class="fa-solid fa-arrow-left-long text-lg"></i>
            <span>Voltar ao App</span>
        </a>
        <a href="/logout" style="color: #f87171;" onmouseover="this.style.background='rgba(248, 113, 113, 0.1)'" onmouseout="this.style.background='transparent'">
            <i class="fa-solid fa-door-open text-lg"></i>
            <span>Sair</span>
        </a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="topbar">
            <h2><?= $pageTitle ?? 'Admin' ?></h2>
            <div class="user-menu">
                <span><?= $_SESSION['user_name'] ?? 'Admin' ?></span>
            </div>
        </div>

        <div class="content-area">
            <?php if (isset($content)) echo $content; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>