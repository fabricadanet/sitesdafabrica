<!-- app/views/admin/projects.php -->
<?php $pageTitle = 'Projetos'; ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> — Sites da Fábrica Admin</title>
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

    table {
        background: white;
    }

    thead {
        background: #f9fafb;
    }

    .pagination {
        justify-content: center;
    }

    .btn-danger {
        background: #dc2626;
        border: none;
    }

    .btn-danger:hover {
        background: #991b1b;
    }
    </style>
</head>

<body>

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
        
        <a href="/admin">
            <i class="fa-solid fa-chart-pie text-lg"></i>
            <span>Dashboard</span>
        </a>
        <a href="/admin/templates">
            <i class="fa-solid fa-cubes text-lg"></i>
            <span>Templates</span>
        </a>
        <a href="/admin/plans">
            <i class="fa-solid fa-wallet text-lg"></i>
            <span>Planos</span>
        </a>
        <a href="/admin/users">
            <i class="fa-solid fa-users text-lg"></i>
            <span>Usuários</span>
        </a>
        <a href="/admin/projects" class="active">
            <i class="fa-solid fa-folder-open text-lg"></i>
            <span>Projetos</span>
        </a>
        <a href="/admin/subscriptions">
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

    <div class="main-content">
        <div class="topbar">
            <h2>📁 Projetos dos Usuários</h2>
            <span class="text-muted">Total: <?= count($projects) ?> de <?= ceil($total / 20) * 20 ?></span>
        </div>

        <div class="content-area">
            <div class="card">
                <div class="card-header">
                    Todos os Projetos
                </div>
                <div class="card-body">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Usuário</th>
                                <th>E-mail</th>
                                <th>Template</th>
                                <th>Atualizado</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $proj): ?>
                            <tr>
                                <td><?= htmlspecialchars($proj['name'] ?? $proj['title'] ?? 'Sem Nome') ?></td>
                                <td><?= htmlspecialchars($proj['user_name'] ?? 'Desconhecido') ?></td>
                                <td><?= htmlspecialchars($proj['email'] ?? '—') ?></td>
                                <td><code>Template ID: <?= htmlspecialchars($proj['template_id'] ?? $proj['template'] ?? 'Nenhum') ?></code></td>
                                <td><?= date('d/m/Y H:i', strtotime($proj['updated_at'])) ?></td>
                                <td>
                                    <a href="/editor?id=<?= $proj['id'] ?>" class="btn btn-sm btn-info"
                                        target="_blank">👁️</a>
                                    <button class="btn btn-sm btn-danger"
                                        onclick="deleteProject(<?= $proj['id'] ?>)">🗑️</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- PAGINAÇÃO -->
                    <?php if ($totalPages > 1): ?>
                    <nav>
                        <ul class="pagination">
                            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function deleteProject(id) {
        if (!confirm('Tem certeza que deseja deletar este projeto?')) return;

        fetch(`/admin/project/delete?id=${id}`)
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    location.reload();
                } else {
                    alert('Erro: ' + (d.message || 'Falha ao deletar'));
                }
            });
    }
    </script>
</body>

</html>