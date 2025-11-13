<!-- app/views/admin/users.php -->
<?php $pageTitle = 'Usuários'; ?>
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

        .btn-primary {
            background: #0ea5e9;
            border: none;
        }

        .btn-primary:hover {
            background: #0284c7;
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

        .btn-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .btn-sm {
            padding: 6px 10px;
            font-size: 12px;
        }
    </style>
</head>

<body>

<div class="sidebar">
    <h4>⚙️ Admin</h4>
    <a href="/admin">📊 Dashboard</a>
    <a href="/admin/templates">📋 Templates</a>
    <a href="/admin/plans">💰 Planos</a>
    <a href="/admin/users" class="active">👥 Usuários</a>
    <a href="/admin/projects">📁 Projetos</a>
    <a href="/admin/subscriptions">🔄 Assinaturas</a>
    <hr style="border-color: rgba(255,255,255,0.2); margin: 20px 0;">
    <a href="/projects">← Voltar ao app</a>
    <a href="/logout">🚪 Sair</a>
</div>

<div class="main-content">
    <div class="topbar">
        <h2>👥 Usuários</h2>
        <span class="text-muted">Total: <?= count($users) ?> de <?= ceil($total / 20) * 20 ?></span>
    </div>

    <div class="content-area">
        <div class="card">
            <div class="card-header">
                Lista de Usuários
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Função</th>
                        <th>Data de Cadastro</th>
                        <th>Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <select class="form-select form-select-sm" style="width: 120px;"
                                        onchange="changeRole(<?= $user['id'] ?>, this.value)">
                                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Usuário
                                    </option>
                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin
                                    </option>
                                </select>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                            <td>
                                <div class="btn-actions">
                                    <button class="btn btn-sm btn-info" onclick="editUser(<?= $user['id'] ?>)">✏️ Editar</button>
                                    <button class="btn btn-sm btn-success" onclick="createSubscription(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name']) ?>')">➕ Assinatura</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(<?= $user['id'] ?>)">🗑️ Deletar</button>
                                </div>
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

<!-- MODAL EDITAR USUÁRIO -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="userForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="userId">

                    <div class="mb-3">
                        <label class="form-label">Nome *</label>
                        <input type="text" name="name" id="userName" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">E-mail *</label>
                        <input type="email" name="email" id="userEmail" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Função</label>
                        <select name="role" id="userRole" class="form-control">
                            <option value="user">Usuário</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL CRIAR ASSINATURA -->
<div class="modal fade" id="subscriptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Criar Assinatura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="subscriptionForm">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="subscriptionUserId">

                    <div class="mb-3">
                        <label class="form-label">Usuário</label>
                        <input type="text" id="subscriptionUserName" class="form-control" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Plano *</label>
                        <select name="plan_id" id="subscriptionPlanId" class="form-control" required>
                            <option value="">Selecione um plano...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Data de Início *</label>
                        <input type="date" name="started_at" id="subscriptionStartedAt" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Data de Renovação</label>
                        <input type="date" name="renews_at" id="subscriptionRenewsAt" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Método de Pagamento</label>
                        <input type="text" name="payment_method" id="subscriptionPaymentMethod" class="form-control" placeholder="Ex: cartão_crédito">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="subscriptionStatus" class="form-control">
                            <option value="active">Ativo</option>
                            <option value="inactive">Inativo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Criar Assinatura</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const userModal = new bootstrap.Modal(document.getElementById('userModal'));
    const subscriptionModal = new bootstrap.Modal(document.getElementById('subscriptionModal'));

    // ===== EDITAR USUÁRIO =====
    async function editUser(id) {
        try {
            const res = await fetch(`/admin/user/get?id=${id}`);
            const data = await res.json();

            if (data.success) {
                const user = data.data;
                document.getElementById('userId').value = user.id;
                document.getElementById('userName').value = user.name;
                document.getElementById('userEmail').value = user.email;
                document.getElementById('userRole').value = user.role || 'user';
                userModal.show();
            } else {
                alert('Erro ao carregar usuário');
            }
        } catch (error) {
            alert('Erro ao carregar usuário: ' + error.message);
        }
    }

    // ===== SALVAR USUÁRIO =====
    document.getElementById('userForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        const res = await fetch('/admin/user/save', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            alert('✅ Usuário atualizado!');
            userModal.hide();
            location.reload();
        } else {
            alert('Erro: ' + (data.message || 'Falha ao salvar'));
        }
    });

    // ===== CRIAR ASSINATURA =====
    async function createSubscription(userId, userName) {
        try {
            // Carregar planos
            const plansRes = await fetch('/admin/plans/list');
            const plansData = await plansRes.json();

            if (plansData.success && plansData.data) {
                const planSelect = document.getElementById('subscriptionPlanId');
                planSelect.innerHTML = '<option value="">Selecione um plano...</option>';

                plansData.data.forEach(plan => {
                    const option = document.createElement('option');
                    option.value = plan.id;
                    option.textContent = `${plan.name} - R$ ${parseFloat(plan.price).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;
                    planSelect.appendChild(option);
                });
            }

            document.getElementById('subscriptionUserId').value = userId;
            document.getElementById('subscriptionUserName').value = userName;

            // Definir data de início como hoje
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('subscriptionStartedAt').value = today;

            // Definir data de renovação como 30 dias depois
            const renewDate = new Date();
            renewDate.setDate(renewDate.getDate() + 30);
            document.getElementById('subscriptionRenewsAt').value = renewDate.toISOString().split('T')[0];

            subscriptionModal.show();
        } catch (error) {
            alert('Erro ao carregar planos: ' + error.message);
        }
    }

    // ===== SALVAR ASSINATURA =====
    document.getElementById('subscriptionForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        const res = await fetch('/admin/subscription/create', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            alert('✅ Assinatura criada com sucesso!');
            subscriptionModal.hide();
            location.reload();
        } else {
            alert('Erro: ' + (data.message || 'Falha ao criar assinatura'));
        }
    });

    // ===== MUDAR FUNÇÃO DO USUÁRIO =====
    async function changeRole(userId, role) {
        const formData = new FormData();
        formData.append('user_id', userId);
        formData.append('role', role);

        const res = await fetch('/admin/user/role', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (!data.success) {
            alert('Erro ao atualizar função');
            location.reload();
        }
    }

    // ===== DELETAR USUÁRIO =====
    function deleteUser(userId) {
        if (!confirm('Tem certeza que deseja deletar este usuário?')) return;

        fetch(`/admin/user/delete?id=${userId}`)
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