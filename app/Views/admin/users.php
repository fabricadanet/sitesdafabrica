<!-- app/views/admin/users.php -->
<?php $pageTitle = 'Usuários'; ?>
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
        <a href="/admin/users" class="active">
            <i class="fa-solid fa-users text-lg"></i>
            <span>Usuários</span>
        </a>
        <a href="/admin/projects">
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
                                    <a href="/admin/user/impersonate?id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">🕵️ Aceder Conta</a>
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