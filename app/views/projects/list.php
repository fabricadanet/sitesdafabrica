<?php
//app/views/projects/list.php 
// list.php — Lista de projetos com preview e duplicar

$user_id = $_SESSION['user_id'] ?? 1; // 🔒 substituir por $_SESSION['user_id'] quando login estiver ativo
$userName = $_SESSION['user_name'] ?? 'Usuário';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Meus Projetos - Sites da Fábrica</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body {
    background: #f9fafb;
    font-family: 'Inter', sans-serif;
  }
  .container {
    max-width: 960px;
    margin-top: 40px;
  }
  .card-project {
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 16px;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    margin-bottom: 16px;
  }
  .card-project:hover {
    background: #f5f7fa;
  }
  .actions button, .actions a {
    margin-right: 6px;
  }
  iframe.preview-frame {
    width: 100%;
    height: 70vh;
    border: none;
  }
</style>
</head>
<body>
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">📁 Meus Projetos</h2>
    <a href="/editor" class="btn btn-primary">➕ Novo Projeto</a>
  </div>

  <?php if (empty($projects)): ?>
    <div class="alert alert-info">Nenhum projeto criado ainda.</div>
  <?php else: ?>
    <?php foreach ($projects as $p): ?>
      <div class="card-project">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-1"><?= htmlspecialchars($p['title']) ?></h5>
            <small class="text-muted">
              Template: <?= htmlspecialchars($p['template'] ?: 'Padrão') ?> |
              Atualizado em: <?= date('d/m/Y H:i', strtotime($p['updated_at'])) ?>
            </small>
          </div>
          <div class="actions">
            <a href="/editor?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">🧱 Editar</a>
            <button class="btn btn-sm btn-outline-success" onclick="previewProject(<?= $p['id'] ?>)">👁️ Preview</button>
            <button class="btn btn-sm btn-outline-secondary" onclick="duplicateProject(<?= $p['id'] ?>)">📄 Duplicar</button>
            <button class="btn btn-sm btn-outline-danger" onclick="deleteProject(<?= $p['id'] ?>)">🗑️ Deletar</button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Modal de Preview -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="previewLabel" class="modal-title">👁️ Visualizar Projeto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <iframe class="preview-frame" id="previewFrame"></iframe>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ===== Deletar projeto via AJAX =====
async function deleteProject(id) {
  if (!confirm('Deseja realmente excluir este projeto?')) return;

  try {
    const res = await fetch(`/projects/delete?id=${id}`);
    const data = await res.json();

    if (data.success) {
      alert('🗑️ Projeto excluído com sucesso!');
      location.reload();
    } else {
      alert('Erro ao excluir: ' + data.message);
    }
  } catch (e) {
    alert('Falha ao excluir projeto: ' + e.message);
  }
}

// ===== Duplicar projeto =====
async function duplicateProject(id) {
  try {
    const res = await fetch(`/projects/get?id=${id}`);
    const data = await res.json();

    if (!data.success) {
      alert('Erro ao duplicar: ' + data.message);
      return;
    }

    const project = data.data;
    const formData = new FormData();
    formData.append('title', project.title + ' (Cópia)');
    formData.append('content', project.content_html);
    formData.append('template', project.template);
    formData.append('user_id', 1);

    const saveRes = await fetch('/projects/save', { method: 'POST', body: formData });
    const saveData = await saveRes.json();

    if (saveData.success) {
      alert('📄 Projeto duplicado com sucesso!');
      location.reload();
    } else {
      alert('Erro ao salvar cópia: ' + saveData.message);
    }
  } catch (e) {
    alert('Falha ao duplicar projeto: ' + e.message);
  }
}

// ===== Preview em modal =====
async function previewProject(id) {
  try {
    const res = await fetch(`/projects/get?id=${id}`);
    const data = await res.json();

    if (!data.success) {
      alert('Erro ao carregar preview: ' + data.message);
      return;
    }

    const frame = document.getElementById('previewFrame');
    const doc = frame.contentDocument || frame.contentWindow.document;
    doc.open();
    doc.write(data.data.content_html);
    doc.close();

    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
  } catch (e) {
    alert('Erro ao gerar preview: ' + e.message);
  }
}
</script>

</body>
</html>

