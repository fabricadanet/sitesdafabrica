<!-- app/views/admin/plans.php -->
<?php $pageTitle = 'Planos'; ?>
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
    <a href="/admin/plans" class="active">
        <i class="fa-solid fa-wallet text-lg"></i>
        <span>Planos</span>
    </a>
    <a href="/admin/users">
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
                            <div>💾 <?= $plan['max_storage_mb'] ?> MB</div>
                            <div>📥 <?= $plan['max_downloads'] ?? 0 ?> downloads/mês</div>
                            <div>🌐 <?= $plan['max_domains'] ?? 0 ?> domínios</div>
                            <div>🔗 <?= $plan['max_subdomains'] ?? 0 ?> subdomínios</div>
                        </div>

                        <span class="badge"
                              style="background: <?= $plan['status'] === 'active' ? '#16a34a' : '#dc2626' ?>; margin-bottom: 10px;">
                            <?= ucfirst($plan['status']) ?>
                        </span>
                        <?php if ($plan['is_featured']): ?>
                            <span class="badge" style="background: #f59e0b; margin-left: 5px;">⭐ Destaque</span>
                        <?php endif; ?>

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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Plano de Assinatura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="planForm">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <input type="hidden" name="id" id="planId">

                    <!-- SEÇÃO: Informações Básicas -->
                    <h6 class="text-muted mb-3">📋 Informações Básicas</h6>

                    <div class="mb-3">
                        <label class="form-label">Nome do Plano *</label>
                        <input type="text" name="name" id="planName" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="description" id="planDescription" class="form-control" rows="2"></textarea>
                    </div>

                    <!-- Preço e Status -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Preço (R$) *</label>
                            <input type="number" name="price" id="planPrice" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="planStatus" class="form-control">
                                <option value="active">Ativo</option>
                                <option value="inactive">Inativo</option>
                                <option value="deprecated">Descontinuado</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="text-muted mb-3">📊 Limites do Plano</h6>

                    <!-- Limites Principais -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Máximo de Projetos *</label>
                            <input type="number" name="max_projects" id="planMaxProjects" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Armazenamento Máximo (MB) *</label>
                            <input type="number" name="max_storage_mb" id="planMaxStorage" class="form-control" required>
                        </div>
                    </div>

                    <!-- Downloads e Domínios -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Downloads por Mês</label>
                            <input type="number" name="max_downloads" id="planMaxDownloads" class="form-control" value="1000">
                            <small class="text-muted">Limite de downloads de sites/projetos por mês</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Domínios Personalizados</label>
                            <input type="number" name="max_domains" id="planMaxDomains" class="form-control" value="1">
                            <small class="text-muted">Quantos domínios pode usar neste plano</small>
                        </div>
                    </div>

                    <!-- Subdomínios e Domínios por Projeto -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Subdomínios</label>
                            <input type="number" name="max_subdomains" id="planMaxSubdomains" class="form-control" value="3">
                            <small class="text-muted">Ex: site1.seudominio.com</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Domínios por Projeto</label>
                            <input type="number" name="max_domains_per_project" id="planMaxDomainsPerProject" class="form-control" placeholder="Deixe vazio = ilimitado">
                            <small class="text-muted">Domínios adicionais por projeto</small>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="text-muted mb-3">🎯 Visibilidade e Apresentação</h6>

                    <!-- Visibilidade e Destaque -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_featured" id="planIsFeatured" class="form-check-input" value="1">
                                <label class="form-check-label" for="planIsFeatured">
                                    <strong>⭐ Plano em Destaque</strong><br>
                                    <small class="text-muted">Aparecer destacado na página de preços</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_visible" id="planIsVisible" class="form-check-input" value="1" checked>
                                <label class="form-check-label" for="planIsVisible">
                                    <strong>👁️ Visível na Página</strong><br>
                                    <small class="text-muted">Mostrar este plano publicamente</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Ordem de Exibição -->
                    <div class="mb-3">
                        <label class="form-label">Ordem de Exibição (Display Order)</label>
                        <input type="number" name="display_order" id="planDisplayOrder" class="form-control" value="0">
                        <small class="text-muted">Planos com número menor aparecem primeiro (0 = padrão)</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Plano</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const planModal = new bootstrap.Modal(document.getElementById('planModal'));

    function openPlanModal() {
        document.getElementById('modalTitle').textContent = 'Novo Plano';
        document.getElementById('planForm').reset();
        document.getElementById('planId').value = '';
        // Resetar checkboxes
        document.getElementById('planIsFeatured').checked = false;
        document.getElementById('planIsVisible').checked = true;
        planModal.show();
    }

    async function editPlan(id) {
        try {
            const res = await fetch(`/admin/plan/get?id=${id}`);
            const data = await res.json();

            if (data.success) {
                const plan = data.data;
                document.getElementById('modalTitle').textContent = 'Editar Plano';
                document.getElementById('planId').value = plan.id;
                document.getElementById('planName').value = plan.name;
                document.getElementById('planDescription').value = plan.description || '';
                document.getElementById('planPrice').value = plan.price;
                document.getElementById('planMaxProjects').value = plan.max_projects;
                document.getElementById('planMaxStorage').value = plan.max_storage_mb;
                document.getElementById('planMaxDownloads').value = plan.max_downloads || 1000;
                document.getElementById('planMaxDomains').value = plan.max_domains || 1;
                document.getElementById('planMaxSubdomains').value = plan.max_subdomains || 3;
                document.getElementById('planMaxDomainsPerProject').value = plan.max_domains_per_project || '';
                document.getElementById('planStatus').value = plan.status || 'active';
                document.getElementById('planIsFeatured').checked = plan.is_featured == 1;
                document.getElementById('planIsVisible').checked = plan.is_visible == 1;
                document.getElementById('planDisplayOrder').value = plan.display_order || 0;
                planModal.show();
            } else {
                alert('Erro ao carregar plano');
            }
        } catch (error) {
            alert('Erro ao carregar plano: ' + error.message);
        }
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

        // Converter checkboxes para 0 ou 1
        formData.set('is_featured', document.getElementById('planIsFeatured').checked ? 1 : 0);
        formData.set('is_visible', document.getElementById('planIsVisible').checked ? 1 : 0);

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