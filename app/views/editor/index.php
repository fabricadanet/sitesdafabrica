<!-- app/views/editor/index.php -->
<?php if (!isset($_SESSION['user'])) { header('Location:/login'); exit; } ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Editor — Sites da Fábrica</title>
<link rel="stylesheet" href="/assets/css/editor.css">
</head>
<body>
<header>
  <div class="brand">Sites da Fábrica</div>
  <div class="controls">
    <a href="/projects" class="link">← Meus projetos</a>
    <button id="btnSave" class="btn">💾 Salvar Projeto</button>
    <a href="/logout" class="btn btn-logout">Sair</a>
  </div>
</header>

<main>
  <aside class="sidebar">
    <h3>Personalizar Template</h3>

    <label>Escolher template</label>
    <select id="templateSelect">
      <option value="restaurante1.html">Restaurante</option>
      <option value="institucional1.html">Institucional</option>

    </select>
    <button id="loadTemplate" class="btn">🔁 Carregar</button>

    <hr>

  

    <div style="margin-top:12px">
      <button id="previewBtn" class="btn">▶️ Visualizar</button>
      <button id="exportBtn" class="btn">⬇️ Exportar HTML</button>
    </div>
  </aside>

  <section class="preview">
    <iframe id="previewFrame"></iframe>
  </section>
</main>

<script src="/assets/js/editor.js"></script>
</body>
</html>
