<?php
// app/views/editor/editor.php

$projectId  = $_GET['id'] ?? null;
$templateId = $_GET['template'] ?? null;

$userName = $_SESSION['user_name'] ?? 'Usuário';
$userId   = $_SESSION['user_id'] ?? null;

$templateHtml = null;
$projectName  = "Novo Projeto";
$projectData  = null;

/* ============================================================
   OPÇÃO 1 — Sempre usar o template salvo no projeto
   ============================================================ */
if ($projectId) {

    // Buscar dados do projeto
    $stmt = $this->pdo->prepare("
        SELECT p.id, p.name, p.html_content, p.template_id, p.user_id,
               t.html_file, t.name AS template_name
        FROM projects p
        LEFT JOIN templates_library t ON t.id = p.template_id
        WHERE p.id = ? AND p.user_id = ?
    ");
    $stmt->execute([$projectId, $userId]);
    $projectData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$projectData) {
        echo "<script>alert('Projeto não encontrado'); window.location='/projects';</script>";
        exit;
    }

    $projectName = $projectData['name'];

    // 1️⃣ PRIORIDADE: conteúdo salvo no projeto
    if (!empty($projectData['html_content'])) {
        $templateHtml = $projectData['html_content'];
    }

    // 2️⃣ Caso não tenha conteúdo salvo, usa o template do projeto
    elseif (!empty($projectData['template_id']) && !empty($projectData['html_file'])) {
        $file = $_SERVER['DOCUMENT_ROOT'] . "/templates/{$projectData['html_file']}";

        if (file_exists($file)) {
            $templateHtml = file_get_contents($file);
        }
    }

    // Se mesmo assim não encontrou → fallback
    if (!$templateHtml) {
        $templateHtml = "<h1>Template vazio</h1><p>Nenhum conteúdo encontrado.</p>";
    }
}

/* ============================================================
   PROJETO NOVO: usar o template passado via GET
   ============================================================ */
elseif ($templateId) {

    $stmt = $this->pdo->prepare("
        SELECT id, html_file, name 
        FROM templates_library 
        WHERE id = ? AND status = 'active'
    ");
    $stmt->execute([$templateId]);
    $tpl = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($tpl) {
        $projectName = $tpl['name'];

        $file = __DIR__ . "/../../public/templates/{$tpl['html_file']}";
        if (file_exists($file)) {
            $templateHtml = file_get_contents($file);
        }
    }

    if (!$templateHtml) {
        $templateHtml = "<h1>Template vazio</h1><p>Nenhum conteúdo encontrado.</p>";
    }
}

/* ============================================================
   Se NENHUMA opção carregou: fallback
   ============================================================ */
else {
    $templateHtml = "<h1>Template vazio</h1><p>Nenhum conteúdo encontrado.</p>";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor - Sites da Fábrica</title>

    <link rel="stylesheet" href="/assets/css/editor.css">
    <script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
</head>

<body>

<header>
    <div class="brand">⚡ Sites da Fábrica — Editor</div>
    <div class="actions">
        <button id="saveProject" class="btn">💾 Salvar</button>
        <button id="downloadSite" class="btn">⬇️ Baixar</button>
        <button id="preview" class="btn">👁️ Preview</button>
        <a href="/projects" class="btn">⬅️ Voltar</a>
        <a href="/logout" class="btn btn-logout" onclick="return confirm('Tem certeza que deseja fazer logout?');">🚪 Sair</a>
    </div>
</header>

<main>

    <div class="preview">
        <iframe id="editorFrame"></iframe>
    </div>

    <aside id="sidebar" style="width:340px;background:#f9fafb;border-left:1px solid #ddd;overflow-y:auto;padding:1rem;">
        <h5>🧱 Editor</h5>

        <div id="panel-vars" class="panel">
            <div class="panel-header" onclick="togglePanel('panel-vars')">
                <h6>🎨 Variáveis Globais</h6>
                <span class="collapse-icon">▼</span>
            </div>
            <div id="vars-container" class="panel-content"></div>
        </div>

        <div id="panel-texts" class="panel">
            <div class="panel-header" onclick="togglePanel('panel-texts')">
                <h6>🖋️ Textos</h6>
                <span class="collapse-icon">▼</span>
            </div>
            <div id="texts-container" class="panel-content"></div>
        </div>

        <div id="panel-images" class="panel">
            <div class="panel-header" onclick="togglePanel('panel-images')">
                <h6>🖼️ Imagens</h6>
                <span class="collapse-icon">▼</span>
            </div>
            <div id="images-container" class="panel-content"></div>
        </div>

    </aside>

</main>

<script>
    const PROJECT_ID   = <?= json_encode($projectId) ?>;
    const TEMPLATE_ID  = <?= json_encode($templateId) ?>;
    const PROJECT_NAME = <?= json_encode($projectName) ?>;
    const INITIAL_HTML = <?= json_encode($templateHtml) ?>;

    const iframe = document.getElementById("editorFrame");
    let iframeDoc = null;

    window.addEventListener("load", () => {
        iframeDoc = iframe.contentDocument;
        iframeDoc.open();
        iframeDoc.write(INITIAL_HTML);
        iframeDoc.close();

        tryBuildSidebar(0);
    });

    function tryBuildSidebar(attempt) {
        if (attempt > 10) return;
        if (iframeDoc.body.querySelectorAll("[data-edit]").length > 0) {
            buildSidebar();
            return;
        }
        setTimeout(() => tryBuildSidebar(attempt + 1), 150);
    }

    function buildSidebar() {

        const varsContainer   = document.getElementById("vars-container");
        const textsContainer  = document.getElementById("texts-container");
        const imagesContainer = document.getElementById("images-container");

        // Variáveis CSS
        const styles = getComputedStyle(iframeDoc.documentElement);
        const vars = [...styles].filter(name => name.startsWith("--"));

        varsContainer.innerHTML = "";
        vars.forEach(name => {
            const value = styles.getPropertyValue(name).trim();

            const label = document.createElement("label");
            label.className = "form-label small text-muted mt-2";
            label.textContent = name;

            const input = document.createElement("input");
            input.type = value.match(/^#|rgb|hsl/) ? "color" : "text";
            input.value = value;
            input.className = "form-control mb-2";

            input.oninput = () => {
                iframeDoc.documentElement.style.setProperty(name, input.value);
            };

            varsContainer.appendChild(label);
            varsContainer.appendChild(input);
        });

        // Textos
        const textEls = iframeDoc.querySelectorAll("[data-edit]:not(img)");
        textsContainer.innerHTML = "";

        textEls.forEach(el => {
            const name = el.dataset.edit;

            const label = document.createElement("label");
            label.className = "form-label small text-muted mt-2";
            label.textContent = name;

            const textarea = document.createElement("textarea");
            textarea.className = "form-control mb-2";
            textarea.rows = 2;
            textarea.value = el.innerText.trim();
            textarea.oninput = () => el.innerText = textarea.value;

            textsContainer.appendChild(label);
            textsContainer.appendChild(textarea);
        });

        // Imagens
        const imgs = iframeDoc.querySelectorAll("img[data-edit]");
        imagesContainer.innerHTML = "";

        imgs.forEach(img => {
            const name = img.dataset.edit;

            const label = document.createElement("label");
            label.className = "form-label small text-muted mt-2";
            label.textContent = name;

            const preview = document.createElement("img");
            preview.src = img.src;
            preview.style.width = "100%";

            const input = document.createElement("input");
            input.type = "file";
            input.accept = "image/*";
            input.className = "form-control mb-3";

            input.onchange = e => {
                const file = e.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = ev => {
                    img.src = ev.target.result;
                    preview.src = ev.target.result;
                };
                reader.readAsDataURL(file);
            };

            imagesContainer.appendChild(label);
            imagesContainer.appendChild(preview);
            imagesContainer.appendChild(input);
        });
    }

    // SALVAR
    document.getElementById("saveProject").onclick = async () => {

        const html = iframeDoc.documentElement.outerHTML;

        const form = new FormData();
        form.append("id", PROJECT_ID || "");
        form.append("name", PROJECT_NAME);
        form.append("html", html);

        if (TEMPLATE_ID) {
            form.append("template_id", TEMPLATE_ID);
        }

        const res  = await fetch("/projects/save", { method: "POST", body: form });
        const data = await res.json();

        if (data.success) {
            alert("Projeto salvo!");
            if (!PROJECT_ID && data.project_id) {
                window.location.href = "/editor?id=" + data.project_id;
            }
        } else {
            alert("Erro ao salvar o projeto");
        }
    };

    // PREVIEW
    document.getElementById("preview").onclick = () => {
        const blob = new Blob([iframeDoc.documentElement.outerHTML], { type: "text/html" });
        const url  = URL.createObjectURL(blob);
        window.open(url, "_blank");
    };

    // DOWNLOAD
    document.getElementById("downloadSite").onclick = async () => {
        const zip  = new JSZip();
        zip.file("index.html", iframeDoc.documentElement.outerHTML);
        const blob = await zip.generateAsync({ type: "blob" });
        saveAs(blob, PROJECT_NAME + ".zip");
    };
</script>

</body>
</html>


