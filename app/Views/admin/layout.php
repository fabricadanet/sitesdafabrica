<!-- app/views/admin/layout.php -->
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin' ?> — Sites da Fábrica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        display: flex;
        height: 100vh;
        background: #f5f5f5;
    }

    .sidebar {
        width: 240px;
        background: #1e40af;
        color: white;
        padding: 20px;
        overflow-y: auto;
        position: fixed;
        height: 100vh;
        left: 0;
        top: 0;
    }

    .sidebar h4 {
        margin-bottom: 20px;
        font-size: 18px;
        font-weight: 700;
    }

    .sidebar a {
        display: block;
        color: white;
        text-decoration: none;
        padding: 10px 12px;
        border-radius: 6px;
        margin-bottom: 6px;
        transition: background 0.3s;
    }

    .sidebar a:hover,
    .sidebar a.active {
        background: #0ea5e9;
    }

    .main-content {
        margin-left: 240px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .topbar {
        background: white;
        padding: 15px 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .topbar h2 {
        margin: 0;
        font-size: 24px;
        color: #1e40af;
    }

    .user-menu a {
        margin-left: 10px;
        color: #0ea5e9;
        text-decoration: none;
    }

    .content-area {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
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
        <h4>⚙️ Admin</h4>
        <a href="/admin" class="<?= basename($_SERVER['REQUEST_URI'], '?') === '/admin' ? 'active' : '' ?>">📊
            Dashboard</a>
        <a href="/admin/templates"
            class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/templates') === 0 ? 'active' : '' ?>">📋 Templates</a>
        <a href="/admin/plans" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/plans') === 0 ? 'active' : '' ?>">💰
            Planos</a>
        <a href="/admin/users" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/users') === 0 ? 'active' : '' ?>">👥
            Usuários</a>
        <a href="/admin/projects"
            class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/projects') === 0 ? 'active' : '' ?>">📁 Projetos</a>
        <a href="/admin/subscriptions"
            class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/subscriptions') === 0 ? 'active' : '' ?>">🔄
            Assinaturas</a>
        <hr style="border-color: rgba(255,255,255,0.2); margin: 20px 0;">
        <a href="/projects">← Voltar ao app</a>
        <a href="/logout">🚪 Sair</a>
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