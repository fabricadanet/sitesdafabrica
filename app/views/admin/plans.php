<!-- app/views/admin/plans.php -->
<?php $pageTitle = 'Planos'; ?>
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

    .plan-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .plan-card h5 {
        color: #1e40af;
        margin-bottom: 10px;
    }

    .plan-price {
        font-size: 24px;
        font-weight: 700;
        color: #0ea5e9;
        margin-bottom: 10px;
    }

    .plan-features {
        font-size: 14px;
        margin-bottom: 15px;
        line-height: 1.8;
    }
    </style>
</head>

<body>

    <div class="sidebar">
        <h4>⚙️ Admin</h4>
        <a href="/admin">📊 Dashboard</a>
        <a href="/admin/templates">📋 Templates</a>
        <a href="/admin/plans" class="active">💰 Planos</a>
        <a href="/admin/users">👥 Usuários</a>
        <a href="/admin/projects">📁 Projetos</a>
        <a href="/admin/subscriptions">🔄 Assinaturas</a>
        <hr style="border-color: rgba(255,255,255,0.2); margin: 20px 0;">
        <a href="/projects">← Voltar ao app</a>
        <a href="/logout">🚪 Sair</a>
    </div>

    <div class="main-content">
        <div class="topbar">
            <h2>💰 Planos de Assinatura</h2>
            <button class="btn btn-primary" onclick="openPlanModal()">➕ Novo Plano</button>
        </div>

        <div class="content-area">
            <div class="row">
                <?php foreach ($plans as $plan): ?>
                <div class="col-md-4">
                    <div class="plan-card">
                        <h5><?= htmlspecialchars($plan['name']) ?></h5>
                        <p class="text-muted" style="font-size: 13px;">
                            <?= htmlspecialchars($plan['description'] ?? '') ?></p>

                        <div class="plan-price">
                            R$ <?= number_format($plan['price'], 2, ',', '.') ?>
                            <?php if ($plan['price'] > 0): ?><span style="font-size: 14px;">/mês</span><?php endif; ?>
                        </div>

                        <div class="plan-features">
                            <div>📁 <?= $plan['max_projects'] ?> projetos</div>
                            <div>💾 <?= $plan['max_storage_mb'] ?> MB de armazenamento</div>
                        </div>

                        <span class="badge"
                            style="background: <?= $plan['status'] === 'active' ? '#16a34a' : '#dc2626' ?>; margin-bottom: 10px;">
                            <?= ucfirst($plan['status']) ?>
                        </span>

                        <div class="mt-3" style="display: flex; gap: 8px;">
                            <button class="btn btn-sm btn-warning" onclick="editPlan(<?= $plan['id'] ?>)">✏️</button>
                            <button class="btn btn-sm btn-danger" onclick="deletePlan(<?= $plan['id'] ?>)">🗑️</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- MODAL NOVO/EDITAR PLANO -->
    <div class="modal fade" id="planModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Plano de Assinatura</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="planForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="planId">

                        <div class="mb-3">
                            <label class="form-label">Nome do Plano *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descrição</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Preço (R$) *</label>
                            <input type="number" name="price" class="form-control" step="0.01" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Máximo de Projetos *</label>
                            <input type="number" name="max_projects" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Armazenamento Máximo (MB) *</label>
                            <input type="number" name="max_storage_mb" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="active">Ativo</option>
                                <option value="inactive">Inativo</option>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const planModal = new bootstrap.Modal(document.getElementById('planModal'));

    function openPlanModal() {
        document.getElementById('planForm').reset();
        document.getElementById('planId').value = '';
        planModal.show();
    }

    function editPlan(id) {
        // Implementar fetch para carregar dados do plano
        openPlanModal();
    }

    function deletePlan(id) {
        if (!confirm('Tem certeza que deseja deletar este plano?')) return;

        fetch(`/admin/plan/delete?id=${id}`)
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    location.reload();
                } else {
                    alert('Erro: ' + (d.message || 'Falha ao deletar'));
                }
            });
    }

    document.getElementById('planForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        const res = await fetch('/admin/plan/save', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            alert('✅ Plano salvo!');
            planModal.hide();
            location.reload();
        } else {
            alert('Erro: ' + (data.message || 'Falha ao salvar'));
        }
    });
    </script>
</body>

</html>