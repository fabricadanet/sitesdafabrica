<!-- app/views/admin/dashboard.php -->
<?php $pageTitle = 'Dashboard'; ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> — Sites da Fábrica Admin</title>
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

    .content-area {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
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

    table {
        background: white;
        border-radius: 8px;
        overflow: hidden;
    }

    thead {
        background: #f9fafb;
    }

    .btn-primary {
        background: #0ea5e9;
        border: none;
    }

    .btn-primary:hover {
        background: #0284c7;
    }
    </style>
</head>

<body>

    <div class="sidebar">
        <h4>⚙️ Admin</h4>
        <a href="/admin" class="active">📊 Dashboard</a>
        <a href="/admin/templates">📋 Templates</a>
        <a href="/admin/plans">💰 Planos</a>
        <a href="/admin/users">👥 Usuários</a>
        <a href="/admin/projects">📁 Projetos</a>
        <a href="/admin/subscriptions">🔄 Assinaturas</a>
        <hr style="border-color: rgba(255,255,255,0.2); margin: 20px 0;">
        <a href="/projects">← Voltar ao app</a>
        <a href="/logout">🚪 Sair</a>
    </div>

    <div class="main-content">
        <div class="topbar">
            <h2>📊 Dashboard</h2>
            <div class="user-menu">
                <span><?= $_SESSION['user_name'] ?? 'Admin' ?></span>
            </div>
        </div>

        <div class="content-area">
            <!-- STATS -->
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card">
                        <h6>Total de Usuários</h6>
                        <div class="number"><?= $totalUsers ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <h6>Projetos Criados</h6>
                        <div class="number"><?= $totalProjects ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <h6>Templates</h6>
                        <div class="number"><?= $totalTemplates ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <h6>Assinaturas Ativas</h6>
                        <div class="number"><?= $totalSubscriptions ?></div>
                    </div>
                </div>
            </div>

            <!-- RECEITA -->
            <div class="row">
                <div class="col-md-12">
                    <div class="stat-card">
                        <h6>Receita Mensal (Planos Ativos)</h6>
                        <div class="number" style="color: #16a34a;">R$ <?= number_format($revenue, 2, ',', '.') ?></div>
                    </div>
                </div>
            </div>

            <!-- USUÁRIOS RECENTES -->
            <div class="card">
                <div class="card-header">
                    👥 Usuários Recentes
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Função</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentUsers as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="badge"
                                        style="background: <?= $user['role'] === 'admin' ? '#1e40af' : '#0ea5e9' ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- LINKS RÁPIDOS -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5>📋 Gerenciar Templates</h5>
                            <p class="text-muted">Adicione novos templates à biblioteca</p>
                            <a href="/admin/templates" class="btn btn-primary btn-sm">Acessar</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5>💰 Configurar Planos</h5>
                            <p class="text-muted">Defina preços e recursos dos planos</p>
                            <a href="/admin/plans" class="btn btn-primary btn-sm">Acessar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>