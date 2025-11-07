<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Meus Projetos — Sites da Fábrica</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  .template-card {
    border: 1px solid #ddd;
    border-radius: 10px;
    overflow: hidden;
    transition: transform 0.2s;
    cursor: pointer;
  }
  .template-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  }
  .template-thumb {
    width: 100%;
    height: 160px;
    object-fit: cover;
    display: block;
  }
  .template-title {
    padding: 10px;
    font-weight: 600;
    text-align: center;
  }
  .modal-lg {
    max-width: 900px;
  }
</style>
</head>
<body class="bg-light">
<div class="container py-5">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Meus Projetos</h2>
    <button class="btn btn-primary" onclick="openTemplateModal()">➕ Novo Projeto</button>
  </div>

  <table class="table table-striped">
    <thead>
      <tr>
        <th>Título</th>
        <th>Template</th>
        <th>Atualizado</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($projects)): foreach ($projects as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['title']) ?></td>
          <td><?= htmlspecialchars($p['template']) ?></td>
          <td><?= htmlspecialchars($p['updated_at']) ?></td>
          <td>
            <a href="/editor?id=<?= $p['id'] ?>" class="btn btn-sm btn-success">Editar</a>
            <button class="btn btn-sm btn-danger" onclick="deleteProject(<?= $p['id'] ?>)">Excluir</button>
          </td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="4" class="text-center text-muted">Nenhum projeto criado ainda.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- MODAL DE SELEÇÃO DE TEMPLATE -->
<div id="templateModal" class="modal" tabindex="-1" style="display:none;">
  <div class="modal-dialog modal-lg">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title">Escolha um Template</h5>
        <button type="button" class="btn-close" onclick="closeTemplateModal()"></button>
      </div>
      <div class="modal-body">
        <div class="row" id="templateGrid"></div>
      </div>
    </div>
  </div>
</div>

<!-- MODAL DE CRIAÇÃO DE PROJETO -->
<div id="createModal" class="modal" tabindex="-1" style="display:none;">
  <div class="modal-dialog">
    <div class="modal-content shadow">
      <div class="modal-header">
        <h5 class="modal-title">Criar Novo Projeto</h5>
        <button type="button" class="btn-close" onclick="closeCreateModal()"></button>
      </div>
      <div class="modal-body">
        <form id="createForm">
          <label class="form-label">Título do Projeto</label>
          <input type="text" name="title" class="form-control mb-3" placeholder="Ex: Site da Pizzaria" required>

          <input type="hidden" name="template" id="templateChoice">
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" onclick="closeCreateModal()">Cancelar</button>
        <button class="btn btn-primary" onclick="createProject()">Criar Projeto</button>
      </div>
    </div>
  </div>
</div>

<script>
const templates = [
  { name: 'institucional', title: 'Institucional', thumb: '/templates/thumbs/institucional.jpg' },
  { name: 'restaurante', title: 'Restaurante', thumb: '/templates/thumbs/restaurante1.jpg' },
];

// ========== MODAL DE TEMPLATES ==========
function openTemplateModal() {
  const grid = document.getElementById('templateGrid');
  grid.innerHTML = templates.map(t => `
    <div class="col-md-3 mb-4">
      <div class="template-card" onclick="chooseTemplate('${t.name}')">
        <img src="${t.thumb}" alt="${t.title}" class="template-thumb">
        <div class="template-title">${t.title}</div>
      </div>
    </div>
  `).join('');
  document.getElementById('templateModal').style.display = 'block';
}
function closeTemplateModal() {
  document.getElementById('templateModal').style.display = 'none';
}

// ========== CRIAÇÃO DE PROJETO ==========
function chooseTemplate(templateName) {
  document.getElementById('templateModal').style.display = 'none';
  document.getElementById('templateChoice').value = templateName;
  document.getElementById('createModal').style.display = 'block';
}
function closeCreateModal() {
  document.getElementById('createModal').style.display = 'none';
}

async function createProject() {
  const form = document.getElementById('createForm');
  const formData = new FormData(form);
  const res = await fetch('/projects/save', { method: 'POST', body: formData });
  const data = await res.json();

  if (data.success) {
    window.location.href = `/editor?id=${data.id}&template=${formData.get('template')}`;
  } else {
    alert('Erro: ' + (data.message || 'Falha ao criar projeto'));
  }
}

// ========== EXCLUIR ==========
async function deleteProject(id) {
  if (!confirm('Tem certeza que deseja excluir este projeto?')) return;
  const res = await fetch(`/projects/delete?id=${id}`);
  const data = await res.json();
  if (data.success) location.reload();
}
</script>

</body>
</html>

