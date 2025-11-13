<!-- app/views/admin/templates.php -->
<?php $pageTitle = 'Templates'; ?>
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
    </style>
</head>

<body>

<div class="sidebar">
    <h4>⚙️ Admin</h4>
    <a href="/admin">📊 Dashboard</a>
    <a href="/admin/templates" class="active">📋 Templates</a>
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
        <h2>📋 Templates</h2>
        <button class="btn btn-primary" onclick="openTemplateModal()">➕ Novo Template</button>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const templateModal = new bootstrap.Modal(document.getElementById('templateModal'));

    function openTemplateModal() {
        document.getElementById('modalTitle').textContent = 'Novo Template';
        document.getElementById('templateForm').reset();
        document.getElementById('templateId').value = '';
        document.getElementById('currentHtmlFile').innerHTML = '';
        document.getElementById('currentThumbPreview').innerHTML = '';
        templateModal.show();
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
</script>
</body>

</html>