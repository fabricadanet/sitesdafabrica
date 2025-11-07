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
  <aside class="sidebar" id="sidebar">
    <div class="section">
      <h3 onclick="toggleSection(this)">🧩 Template</h3>
      <div class="section-content">
        <select id="templateSelect">
          <option value="institucional">Institucional</option>
          <option value="restaurante">Restaurante</option>
          <option value="portfolio">Portfólio</option>
        </select>
        <button id="loadTemplate" class="btn">Carregar</button>
      </div>
    </div>

    <div id="editorControls">
      <!-- As variáveis globais e campos dinâmicos serão gerados aqui -->
    </div>
  </aside>

  <div class="preview">
    <iframe id="editorFrame"></iframe>
  </div>
</main>

<script>
  const PROJECT_ID = <?= json_encode($project['id'] ?? null) ?>;
  const TEMPLATE_NAME = <?= json_encode($_GET['template'] ?? ($project['template'] ?? 'institucional')) ?>;
</script>
<script src="/assets/js/editor.js"></script>


</body>
</html>
