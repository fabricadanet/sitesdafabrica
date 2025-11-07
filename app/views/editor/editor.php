<?php
//app/views/editor/editor.php
$projectId = $_GET['id'] ?? null;
$userName = $_SESSION['user_name'] ?? 'Usuário';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editor - Sites da Fábrica</title>
<link rel="stylesheet" href="assets/css/editor.css">
<script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
</head>
<body>

<header>
  <div class="brand">⚡ Sites da Fábrica — Editor</div>
  <div class="actions">
    <button id="saveProject" class="btn">💾 Salvar</button>
    <button id="downloadSite" class="btn">⬇️ Baixar</button>
    <button id="preview" class="btn">👁️ Visualizar</button>
    <a href="/projects" class="btn">⬅️ Voltar</a>
  </div>
</header>

<main>


  <div class="preview">
    <iframe id="editorFrame"></iframe>
  </div>
  <div id="editorContainer" style="display: flex; height: 100vh;">
  <!-- Área principal -->
  <div style="flex: 1; border: none;">
    <iframe id="editorFrame" style="width: 100%; height: 100%; border: none;"></iframe>
  </div>

  <!-- Sidebar -->
  <aside id="sidebar" style="width: 340px; background: #f9fafb; border-left: 1px solid #ddd; overflow-y: auto; padding: 1rem;">
    <h5 style="margin-bottom: 1rem;">🧱 Editor</h5>

    <div id="panel-vars" class="panel">
      <h6>🎨 Variáveis Globais</h6>
      <div id="vars-container"></div>
    </div>

    <div id="panel-texts" class="panel mt-3">
      <h6>🖋️ Textos</h6>
      <div id="texts-container"></div>
    </div>

    <div id="panel-images" class="panel mt-3">
      <h6>🖼️ Imagens</h6>
      <div id="images-container"></div>
    </div>

    <hr>
    <div class="d-grid gap-2">
      <button id="saveProject" class="btn btn-success">💾 Salvar</button>
      <button id="preview" class="btn btn-secondary">👁️ Preview</button>
      <button id="downloadSite" class="btn btn-outline-dark">⬇️ Download</button>
    </div>
  </aside>
</div>

<script>
  const PROJECT_ID = <?= json_encode($project['id'] ?? null) ?>;
  const TEMPLATE_NAME = <?= json_encode($_GET['template'] ?? ($project['template'] ?? 'institucional')) ?>;
</script>

<script src="/assets/js/editor.js"></script>

</main>

</body>
</html>
