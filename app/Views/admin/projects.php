<!-- app/views/admin/projects.php -->
<?php $pageTitle = 'Projetos'; ?>
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
        <h4>⚙️ Admin</h4>
        <a href="/admin">📊 Dashboard</a>
        <a href="/admin/templates">📋 Templates</a>
        <a href="/admin/plans">💰 Planos</a>
        <a href="/admin/users">👥 Usuários</a>
        <a href="/admin/projects" class="active">📁 Projetos</a>
        <a href="/admin/subscriptions">🔄 Assinaturas</a>
        <hr style="border-color: rgba(255,255,255,0.2); margin: 20px 0;">
        <a href="/projects">← Voltar ao app</a>
        <a href="/logout">🚪 Sair</a>
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
                                <td><?= htmlspecialchars($proj['title']) ?></td>
                                <td><?= htmlspecialchars($proj['user_name']) ?></td>
                                <td><?= htmlspecialchars($proj['email']) ?></td>
                                <td><code><?= htmlspecialchars($proj['template']) ?></code></td>
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