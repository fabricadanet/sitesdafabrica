<!-- app/views/admin/templates.php -->
<?php $pageTitle = 'Templates'; ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
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

    .template-thumb {
        max-width: 80px;
        border-radius: 4px;
    }

    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }

    .template-preview {
        max-width: 150px;
        border-radius: 6px;
        border: 1px solid #ddd;
        margin-bottom: 10px;
    }

    .badge-premium {
        background: #8b5cf6;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
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
        <a href="/admin/templates" class="active">
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
        <h2>📋 Templates</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" onclick="openTemplateModal()">➕ Novo Template</button>
            <button class="btn btn-success" onclick="openImportZipModal()">📦 Importar ZIP</button>
        </div>
    </div>

    <div class="content-area">
        <div class="card">
            <div class="card-header">
                Biblioteca de Templates
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Título</th>
                        <th>Categoria</th>
                        <th>Premium</th>
                        <th>Thumbnail</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($templates as $tpl): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($tpl['name']) ?></code></td>
                            <td><?= htmlspecialchars($tpl['title']) ?></td>
                            <td><?= htmlspecialchars($tpl['category']) ?></td>
                            <td>
                                <?php if ($tpl['is_premium']): ?>
                                    <span class="badge-premium">✨ Premium</span>
                                <?php else: ?>
                                    <span style="color: #9ca3af; font-size: 12px;">Padrão</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($tpl['thumb_file']): ?>
                                    <img src="/templates/thumbs/<?= htmlspecialchars($tpl['thumb_file']) ?>"
                                         class="template-thumb" alt="">
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                    <span class="badge"
                                          style="background: <?= $tpl['status'] === 'active' ? '#16a34a' : '#dc2626' ?>">
                                        <?= ucfirst($tpl['status']) ?>
                                    </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning"
                                        onclick="editTemplate(<?= $tpl['id'] ?>)">✏️</button>
                                <button class="btn btn-sm btn-danger"
                                        onclick="deleteTemplate(<?= $tpl['id'] ?>)">🗑️</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL NOVO/EDITAR TEMPLATE -->
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="templateForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="templateId">

                    <div class="mb-3">
                        <label class="form-label">Nome (para identificação interna) *</label>
                        <input type="text" name="name" id="templateName" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Título (exibido no seletor) *</label>
                        <input type="text" name="title" id="templateTitle" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="description" id="templateDescription" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Categoria</label>
                        <select name="category" id="templateCategory" class="form-control">
                            <option value="geral">Geral</option>
                            <option value="restaurante">Restaurante</option>
                            <option value="loja">Loja</option>
                            <option value="servicos">Serviços</option>
                            <option value="portfolio">Portfolio</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_premium" id="templatePremium" class="form-check-input" value="1">
                            <label class="form-check-label" for="templatePremium">
                                ✨ Template Premium (apenas para planos pagos)
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Arquivo HTML *</label>
                        <input type="file" name="html_file" id="templateHtmlFile" class="form-control" accept=".html">
                        <small class="text-muted">Deixe vazio para manter o arquivo atual</small>
                        <div id="currentHtmlFile" class="text-muted small mt-2"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Thumbnail (imagem de pré-visualização)</label>
                        <input type="file" name="thumb_file" id="templateThumbFile" class="form-control" accept="image/*">
                        <small class="text-muted">Deixe vazio para manter a imagem atual</small>
                        <div id="currentThumbPreview" class="mt-2"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="templateStatus" class="form-control">
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

<!-- MODAL IMPORTAR ZIP -->
<div class="modal fade" id="importZipModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📦 Importar Template via ZIP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="importZipForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nome do Template *</label>
                        <input type="text" name="name" id="importTemplateName" class="form-control" placeholder="Ex: Portfólio Moderno" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Categoria</label>
                        <select name="category" id="importTemplateCategory" class="form-control">
                            <option value="geral">Geral</option>
                            <option value="restaurante">Restaurante</option>
                            <option value="loja">Loja</option>
                            <option value="servicos">Serviços</option>
                            <option value="portfolio">Portfolio</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ficheiro ZIP *</label>
                        <input type="file" name="zip_file" id="importTemplateZipFile" class="form-control" accept=".zip" required>
                        <small class="text-muted">O arquivo ZIP deve conter um ficheiro <code>index.html</code> na raiz e uma pasta opcional <code>assets/</code>.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnSubmitImport">Importar Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const templateModal = new bootstrap.Modal(document.getElementById('templateModal'));
    const importZipModal = new bootstrap.Modal(document.getElementById('importZipModal'));

    function openTemplateModal() {
        document.getElementById('modalTitle').textContent = 'Novo Template';
        document.getElementById('templateForm').reset();
        document.getElementById('templateId').value = '';
        document.getElementById('templatePremium').checked = false;
        document.getElementById('currentHtmlFile').innerHTML = '';
        document.getElementById('currentThumbPreview').innerHTML = '';
        templateModal.show();
    }

    function openImportZipModal() {
        document.getElementById('importZipForm').reset();
        importZipModal.show();
    }

    async function editTemplate(id) {
        try {
            const res = await fetch(`/admin/template/get?id=${id}`);
            const data = await res.json();

            if (data.success) {
                const tpl = data.data;
                document.getElementById('modalTitle').textContent = 'Editar Template';
                document.getElementById('templateId').value = tpl.id;
                document.getElementById('templateName').value = tpl.name;
                document.getElementById('templateTitle').value = tpl.title;
                document.getElementById('templateDescription').value = tpl.description || '';
                document.getElementById('templateCategory').value = tpl.category || 'geral';
                document.getElementById('templateStatus').value = tpl.status || 'active';
                document.getElementById('templatePremium').checked = tpl.is_premium ? true : false;

                // Mostrar arquivo HTML atual
                if (tpl.html_file) {
                    document.getElementById('currentHtmlFile').innerHTML =
                        `<strong>Arquivo atual:</strong> ${tpl.html_file}`;
                }

                // Mostrar thumbnail atual
                if (tpl.thumb_file) {
                    document.getElementById('currentThumbPreview').innerHTML =
                        `<strong>Thumbnail atual:</strong><br><img src="/templates/thumbs/${tpl.thumb_file}" class="template-preview" alt="">`;
                }

                templateModal.show();
            } else {
                alert('Erro ao carregar template');
            }
        } catch (error) {
            alert('Erro ao carregar template: ' + error.message);
        }
    }

    function deleteTemplate(id) {
        if (!confirm('Tem certeza que deseja deletar este template?')) return;

        fetch(`/admin/template/delete?id=${id}`)
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    location.reload();
                } else {
                    alert('Erro: ' + (d.message || 'Falha ao deletar'));
                }
            });
    }

    document.getElementById('templateForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        const res = await fetch('/admin/template/save', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            alert('✅ Template salvo!');
            templateModal.hide();
            location.reload();
        } else {
            alert('Erro: ' + (data.message || 'Falha ao salvar'));
        }
    });

    document.getElementById('importZipForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const btnSubmit = document.getElementById('btnSubmitImport');
        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Importando...';

        const formData = new FormData(e.target);
        
        // CSRF Token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        try {
            const res = await fetch('/admin/template/upload-zip', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                alert('✅ ' + (data.message || 'Template importado com sucesso!'));
                importZipModal.hide();
                location.reload();
            } else {
                alert('Erro: ' + (data.message || 'Falha ao importar template.'));
            }
        } catch (error) {
            alert('Erro de rede ao importar template: ' + error.message);
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Importar Template';
        }
    });
</script>
</body>
</html>