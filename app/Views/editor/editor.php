<?php
// app/views/editor/editor.php - Editor Inteligente com CSS Variables

$projectId  = $_GET['id'] ?? null;
$templateId = $_GET['template'] ?? null;

$userName = $_SESSION['user_name'] ?? 'Usuário';
$userId   = $_SESSION['user_id'] ?? null;

$templateHtml = null;
$projectName  = "Novo Projeto";
$projectData  = null;

/* ============================================================
   CARREGAMENTO DO TEMPLATE
   ============================================================ */
if ($projectId) {
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

    if (!empty($projectData['html_content'])) {
        $templateHtml = $projectData['html_content'];
    }
    elseif (!empty($projectData['template_id']) && !empty($projectData['html_file'])) {
        $file = $_SERVER['DOCUMENT_ROOT'] . "/templates/{$projectData['html_file']}";
        if (file_exists($file)) {
            $templateHtml = file_get_contents($file);
        }
    }

    if (!$templateHtml) {
        $templateHtml = "<h1>Template vazio</h1><p>Nenhum conteúdo encontrado.</p>";
    }
}
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
else {
    $templateHtml = "<h1>Template vazio</h1><p>Nenhum conteúdo encontrado.</p>";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor Inteligente - Sites da Fábrica</title>
    <!-- CSS do Sistema de Feedback -->
    <link rel="stylesheet" href="/assets/css/editor-feedback.css">
    <script src="/assets/js/editor-feedback.js" defer></script>

    <link rel="stylesheet" href="/assets/css/editor.css?v=<?= time() ?>">
    <script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>

    <style>
    /* ... existing styles ... */
    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: #f3f4f6;
    }

    /* ... (keeping existing styles) ... */

    header {
        background: #1f2937;
        color: white;
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    header .brand {
        font-weight: 600;
        font-size: 1rem;
    }

    .actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .btn {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        cursor: pointer;
        font-size: 0.875rem;
        font-weight: 500;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-block;
    }

    .btn:hover {
        background: #2563eb;
    }

    .btn-logout {
        background: #ef4444;
    }

    .btn-logout:hover {
        background: #dc2626;
    }

    main {
        display: flex;
        height: calc(100vh - 60px);
        gap: 0;
    }

    .preview {
        flex: 1;
        display: flex;
        background: white;
        min-width: 0;
        position: relative;
        /* For absolute positioning if needed */
    }

    #editorFrame {
        width: 100%;
        height: 100%;
        border: none;
    }

    #sidebar {
        width: 380px;
        background: #fff;
        border-left: 1px solid #e5e7eb;
        overflow-y: auto;
        padding: 1.5rem;
        box-shadow: -1px 0 3px rgba(0, 0, 0, 0.05);
    }

    #sidebar h5 {
        margin: 0 0 1.5rem 0;
        font-size: 1rem;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .category {
        margin-bottom: 1.5rem;
    }

    .category-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        background: #f3f4f6;
        border-radius: 0.375rem;
        cursor: pointer;
        user-select: none;
        transition: all 0.2s;
        margin-bottom: 0.5rem;
        border: 1px solid #e5e7eb;
    }

    .category-header:hover {
        background: #e5e7eb;
    }

    .category-header h6 {
        margin: 0;
        font-size: 0.875rem;
        font-weight: 600;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .collapse-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        transition: transform 0.3s ease;
        font-size: 0.75rem;
        color: #6b7280;
    }

    .collapse-toggle.rotated {
        transform: rotate(180deg);
    }

    .category-content {
        max-height: 5000px;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }

    .category-content.collapsed {
        max-height: 0;
    }

    .field {
        margin-bottom: 1rem;
    }

    .field-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.375rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .field-input {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-family: inherit;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .field-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    textarea.field-input {
        resize: vertical;
        min-height: 80px;
    }

    input[type="color"].field-input {
        height: 40px;
        padding: 0.25rem;
        cursor: pointer;
    }

    input[type="range"].field-input {
        height: 6px;
        padding: 0;
        cursor: pointer;
    }

    .image-preview {
        width: 100%;
        height: 120px;
        background: #f9fafb;
        border: 1px dashed #d1d5db;
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }

    .image-preview img {
        max-width: 100%;
        max-height: 100%;
        object-fit: cover;
    }

    .image-preview.empty {
        color: #9ca3af;
        font-size: 0.75rem;
        text-align: center;
    }

    #sidebar::-webkit-scrollbar {
        width: 6px;
    }

    #sidebar::-webkit-scrollbar-track {
        background: transparent;
    }

    #sidebar::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 3px;
    }

    #sidebar::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    @media (max-width: 768px) {
        main {
            flex-direction: column;
        }

        #sidebar {
            width: 100%;
            height: 40vh;
            border-left: none;
            border-top: 1px solid #e5e7eb;
        }

        .preview {
            height: 60vh;
        }
    }

    #loadingOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(5px);
        z-index: 10000;
        display: none;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        transition: opacity 0.3s ease;
    }

    .spinner {
        width: 50px;
        height: 50px;
        border: 4px solid rgba(59, 130, 246, 0.1);
        border-left-color: #3b82f6;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
    </style>
</head>

<body>

    <header>
        <div class="brand">⚡ Sites da Fábrica — Editor Inteligente</div>
        <div class="actions">
            <button id="saveProject" class="btn">💾 Salvar</button>
            <button id="downloadSite" class="btn">⬇️ Baixar</button>
            <button id="preview" class="btn">👁️ Preview</button>
            <a href="/projects" class="btn">⬅️ Voltar</a>
            <a href="/logout" class="btn btn-logout"
                onclick="return confirm('Tem certeza que deseja fazer logout?');">🚪 Sair</a>
        </div>
    </header>

    <main>
        <div class="preview">
            <iframe id="editorFrame"></iframe>
        </div>

        <aside id="sidebar">
            <h5>
                <span>🧱</span>
                Editor de Conteúdo
            </h5>
            <div id="categories-container"></div>
        </aside>
        </aside>
    </main>

    <div id="loading-overlay" class="hidden">
        <div class="spinner-container">
            <img src="/assets/img/spinner-logo.png" alt="Loading..." class="spinner-logo">
            <div class="spinner-text" id="loading-text">Processando...</div>
        </div>
    </div>

    <script>
    const PROJECT_ID = <?= json_encode($projectId) ?>;
    const TEMPLATE_ID = <?= json_encode($templateId) ?>;
    const PROJECT_NAME = <?= json_encode($projectName) ?>;
    const INITIAL_HTML = <?= json_encode($templateHtml) ?>;

    const iframe = document.getElementById("editorFrame");
    let iframeDoc = null;

    // Categorias e padrões
    const categoryConfigs = {
        imagens: {
            name: '🖼️ Imagens',
            patterns: [/img|image|foto|picture|icon|logo|banner|hero/i],
            inputType: 'file'
        },
        cores: {
            name: '🎨 Cores & Temas',
            patterns: [/cor|color|theme|background|bg|fundo/i],
            inputType: 'color'
        },
        textos: {
            name: '📝 Textos & Conteúdo',
            patterns: [/texto|text|titulo|title|descri|content|nome|name|paragraph|description/i],
            inputType: 'textarea'
        },
        links: {
            name: '🔗 Links & URLs',
            patterns: [/url|link|href|whatsapp|email|phone|contact/i],
            inputType: 'url'
        },
        seo: {
            name: '📊 SEO & Metadados',
            patterns: [/seo|meta|google|tiktok|hotjar|outras|fb|other|keyword|description|title|og:|canonical/i],
            inputType: 'textarea'
        },
        espacamento: {
            name: '📐 Espaçamento & Layout',
            patterns: [/padding|margin|gap|spacing|size|width|height|tamanho/i],
            inputType: 'number'
        },
        efeitos: {
            name: '✨ Efeitos & Animações',
            patterns: [/shadow|sombra|border|animation|transition|opacity|blur|effect/i],
            inputType: 'range'
        }
    };

    function categorizeField(key, element) {
        if (element && element.tagName === 'IMG') {
            return 'imagens';
        }

        for (const [categoryKey, config] of Object.entries(categoryConfigs)) {
            if (config.patterns.some(pattern => pattern.test(key))) {
                return categoryKey;
            }
        }

        if (element && element.tagName === 'A') return 'links';
        return 'textos';
    }

    function extractCSSVariables(categories) {
        if (!iframeDoc.documentElement) return;

        const styleTag = iframeDoc.querySelector('style');
        if (!styleTag) return;

        const cssText = styleTag.textContent || '';
        const varMatches = cssText.match(/--[\w-]+\s*:\s*[^;]+/g) || [];

        varMatches.forEach(match => {
            const [varName, ...valueParts] = match.split(':');
            const varNameTrimmed = varName.trim();
            const value = valueParts.join(':').trim();

            if (!varNameTrimmed.startsWith('--')) return;

            const key = varNameTrimmed.substring(2);
            const category = categorizeField(key, {
                tagName: 'STYLE'
            });

            if (!categories[category]) {
                categories[category] = [];
            }

            const exists = categories[category].some(item => item.key === key);
            if (!exists) {
                categories[category].push({
                    key,
                    element: null,
                    isCSSVar: true,
                    varName: varNameTrimmed,
                    value: value
                });
            }
        });
    }

    function buildSidebar() {
        const container = document.getElementById('categories-container');
        container.innerHTML = '';

        const categories = {};

        // Agrupa elementos com data-edit
        const allElements = iframeDoc.querySelectorAll('[data-edit]');
        allElements.forEach(el => {
            const key = el.dataset.edit;
            const category = categorizeField(key, el);

            if (!categories[category]) {
                categories[category] = [];
            }

            categories[category].push({
                key,
                element: el
            });
        });

        // Extrai variáveis CSS
        extractCSSVariables(categories);

        // Renderiza categorias
        Object.keys(categories).sort().forEach(categoryKey => {
            const config = categoryConfigs[categoryKey] || {
                name: categoryKey
            };
            const fields = categories[categoryKey];

            const categoryDiv = document.createElement('div');
            categoryDiv.className = 'category';
            categoryDiv.innerHTML = `
                <div class="category-header" onclick="toggleCategory(this)">
                    <h6>${config.name} [${fields.length}]</h6>
                    <div class="collapse-toggle">▼</div>
                </div>
                <div class="category-content"></div>
            `;

            const contentDiv = categoryDiv.querySelector('.category-content');

            fields.forEach(({
                key,
                element,
                isCSSVar,
                varName,
                value
            }) => {
                const fieldDiv = createFieldInput(key, element, config.inputType, isCSSVar, varName,
                    value);
                contentDiv.appendChild(fieldDiv);
            });

            container.appendChild(categoryDiv);
        });
    }

    function createFieldInput(key, element, preferredType, isCSSVar, varName, cssValue) {
        const fieldDiv = document.createElement('div');
        fieldDiv.className = 'field';

        const label = document.createElement('label');
        label.className = 'field-label';
        label.textContent = key.replace(/[-_]/g, ' ').toUpperCase();

        fieldDiv.appendChild(label);

        // Variável CSS
        if (isCSSVar) {
            if (cssValue.includes('#') || cssValue.includes('rgb')) {
                const input = document.createElement('input');
                input.type = 'color';
                input.className = 'field-input';
                input.value = parseColorValue(cssValue) || '#3b82f6';
                input.oninput = () => {
                    iframeDoc.documentElement.style.setProperty(varName, input.value);
                };
                fieldDiv.appendChild(input);
            } else if (cssValue.match(/^\d+/) || cssValue.includes('px') || cssValue.includes('rem')) {
                const input = document.createElement('input');
                input.type = 'number';
                input.className = 'field-input';
                input.value = parseInt(cssValue) || 0;
                input.oninput = () => {
                    const unit = cssValue.match(/[a-z%]+/i)?. [0] || 'px';
                    iframeDoc.documentElement.style.setProperty(varName, input.value + unit);
                };
                fieldDiv.appendChild(input);
            } else {
                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'field-input';
                input.value = cssValue;
                input.oninput = () => {
                    iframeDoc.documentElement.style.setProperty(varName, input.value);
                };
                fieldDiv.appendChild(input);
            }
            return fieldDiv;
        }

        // Elementos com data-edit
        const tagName = element.tagName;
        const currentValue = tagName === 'IMG' ? element.src : element.innerText || element.value || '';

        if (tagName === 'IMG') {
            const preview = document.createElement('div');
            preview.className = 'image-preview';
            preview.innerHTML = `<img src="${element.src}" alt="preview">`;

            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = 'image/*';
            fileInput.className = 'field-input';

            fileInput.onchange = e => {
                const file = e.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = ev => {
                    element.src = ev.target.result;
                    preview.innerHTML = `<img src="${ev.target.result}" alt="preview">`;
                };
                reader.readAsDataURL(file);
            };

            fieldDiv.appendChild(preview);
            fieldDiv.appendChild(fileInput);
        } else if (preferredType === 'color') {
            const input = document.createElement('input');
            input.type = 'color';
            input.className = 'field-input';
            input.value = parseColorValue(currentValue) || '#3b82f6';
            input.oninput = () => {
                element.innerText = input.value;
                iframeDoc.documentElement.style.setProperty('--' + key, input.value);
            };
            fieldDiv.appendChild(input);
        } else if (preferredType === 'range') {
            const input = document.createElement('input');
            input.type = 'range';
            input.className = 'field-input';
            input.min = '0';
            input.max = '100';
            input.value = parseInt(currentValue) || 50;

            const valueSpan = document.createElement('span');
            valueSpan.className = 'field-label';
            valueSpan.textContent = input.value + '%';

            input.oninput = () => {
                valueSpan.textContent = input.value + '%';
                iframeDoc.documentElement.style.setProperty('--' + key, input.value);
            };

            fieldDiv.appendChild(input);
            fieldDiv.appendChild(valueSpan);
        } else if (preferredType === 'url' || tagName === 'A') {
            const input = document.createElement('input');
            input.type = 'url';
            input.className = 'field-input';
            input.value = element.href || currentValue;
            input.placeholder = 'https://...';
            input.oninput = () => {
                if (tagName === 'A') {
                    element.href = input.value;
                } else {
                    element.innerText = input.value;
                }
            };
            fieldDiv.appendChild(input);
        } else if (currentValue.includes('\n') || currentValue.length > 50) {
            const textarea = document.createElement('textarea');
            textarea.className = 'field-input';
            textarea.value = currentValue;
            textarea.oninput = () => element.innerText = textarea.value;
            fieldDiv.appendChild(textarea);
        } else {
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'field-input';
            input.value = currentValue;
            input.oninput = () => element.innerText = input.value;
            fieldDiv.appendChild(input);
        }

        return fieldDiv;
    }

    function parseColorValue(value) {
        if (value.startsWith('#')) return value;
        if (value.startsWith('rgb')) {
            const match = value.match(/\d+/g);
            if (match && match.length >= 3) {
                return '#' + match.slice(0, 3).map(x => {
                    const hex = parseInt(x).toString(16);
                    return hex.length === 1 ? '0' + hex : hex;
                }).join('');
            }
        }
        return null;
    }

    function toggleCategory(header) {
        const content = header.nextElementSibling;
        const toggle = header.querySelector('.collapse-toggle');

        content.classList.toggle('collapsed');
        toggle.classList.toggle('rotated');
    }

    window.addEventListener('load', () => {
        showLoading('Inicializando editor...');
        iframeDoc = iframe.contentDocument;
        iframeDoc.open();
        iframeDoc.write(INITIAL_HTML);
        iframeDoc.close();

        // Inject Editor Styles and Scripts into Iframe
        injectEditorTools(iframeDoc);

        tryBuildSidebar(0);
    });

    function injectEditorTools(doc) {
        // 1. Inject CSS for Toolbar and Selection
        const style = doc.createElement('style');
        style.id = 'sf-editor-style';
        style.textContent = `
            #sf-editor-toolbar {
                position: fixed;
                z-index: 9999;
                background: #1f2937;
                color: white;
                padding: 8px;
                border-radius: 6px;
                display: none;
                gap: 8px;
                align-items: center;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                font-family: sans-serif;
                font-size: 14px;
            }
            #sf-editor-toolbar button {
                background: transparent;
                border: 1px solid transparent;
                color: #d1d5db;
                cursor: pointer;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 14px;
                transition: all 0.2s;
            }
            #sf-editor-toolbar button:hover {
                background: #374151;
                color: white;
            }
            #sf-editor-toolbar button.active {
                background: #3b82f6;
                color: white;
            }
            #sf-editor-toolbar select {
                background: #374151;
                color: white;
                border: 1px solid #4b5563;
                border-radius: 4px;
                padding: 4px;
                font-size: 12px;
                outline: none;
            }
            #sf-editor-toolbar .divider {
                width: 1px;
                height: 20px;
                background: #4b5563;
                margin: 0 4px;
            }
            [contenteditable="true"] {
                outline: 2px dashed #3b82f6;
                outline-offset: 2px;
            }
            [contenteditable="true"]:focus {
                outline: 2px solid #3b82f6;
            }
        `;
        doc.head.appendChild(style);

        // 2. Inject Toolbar HTML
        const toolbar = doc.createElement('div');
        toolbar.id = 'sf-editor-toolbar';
        toolbar.innerHTML = `
            <select id="sf-font-family">
                <option value="">Fonte...</option>
                <option value="Arial, sans-serif">Arial</option>
                <option value="'Helvetica Neue', Helvetica, sans-serif">Helvetica</option>
                <option value="'Times New Roman', Times, serif">Times New Roman</option>
                <option value="'Courier New', Courier, monospace">Courier New</option>
                <option value="Verdana, sans-serif">Verdana</option>
                <option value="Georgia, serif">Georgia</option>
                <option value="'Palatino Linotype', 'Book Antiqua', Palatino, serif">Palatino</option>
                <option value="'Arial Black', Gadget, sans-serif">Arial Black</option>
                <option value="'Comic Sans MS', cursive, sans-serif">Comic Sans</option>
                <option value="Impact, Charcoal, sans-serif">Impact</option>
                <option value="'Trebuchet MS', Helvetica, sans-serif">Trebuchet MS</option>
            </select>
            <div class="divider"></div>
            <button data-cmd="bold" title="Negrito"><b>B</b></button>
            <button data-cmd="italic" title="Itálico"><i>I</i></button>
            <button data-cmd="underline" title="Sublinhado"><u>U</u></button>
            <div class="divider"></div>
            <button data-cmd="justifyLeft" title="Esquerda">⬅️</button>
            <button data-cmd="justifyCenter" title="Centralizar">⏺️</button>
            <button data-cmd="justifyRight" title="Direita">➡️</button>
            <button data-cmd="justifyFull" title="Justificar">↔️</button>
        `;
        doc.body.appendChild(toolbar);

        // 3. Inject JS Logic
        let activeElement = null;

        // Detect clicks on text elements
        doc.body.addEventListener('click', (e) => {
            const target = e.target;

            // Ignore clicks on the toolbar itself
            if (target.closest('#sf-editor-toolbar')) return;

            // Check if element is editable (text tags or data-edit)
            const isText = ['H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'P', 'SPAN', 'A', 'LI', 'TD', 'DIV', 'BUTTON']
                .includes(target.tagName);
            const hasDataEdit = target.hasAttribute('data-edit');

            if (isText || hasDataEdit) {
                e.preventDefault(); // Prevent link navigation etc.
                activateElement(target);
            } else {
                hideToolbar();
            }
        });

        function activateElement(el) {
            if (activeElement && activeElement !== el) {
                activeElement.contentEditable = "false";
            }

            activeElement = el;

            // Auto-assign data-edit if missing to persist editability
            if (!activeElement.hasAttribute('data-edit')) {
                const tagName = activeElement.tagName.toLowerCase();
                const uniqueId = tagName + '-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
                activeElement.setAttribute('data-edit', uniqueId);
            }

            activeElement.contentEditable = "true";
            activeElement.focus();

            updateToolbarPosition(el);
            updateToolbarState(el);

            toolbar.style.display = 'flex';
        }

        function hideToolbar() {
            if (activeElement) {
                activeElement.contentEditable = "false";
                activeElement = null;
            }
            toolbar.style.display = 'none';
        }

        function updateToolbarPosition(el) {
            const rect = el.getBoundingClientRect();
            const toolbarRect = toolbar.getBoundingClientRect();

            let top = rect.top - toolbarRect.height - 10;
            let left = rect.left;

            // Keep within viewport
            if (top < 0) top = rect.bottom + 10;
            if (left + toolbarRect.width > window.innerWidth) left = window.innerWidth - toolbarRect.width - 10;
            if (left < 0) left = 10;

            toolbar.style.top = top + 'px';
            toolbar.style.left = left + 'px';
        }

        function updateToolbarState(el) {
            // Update button states based on current selection style
            const computed = window.getComputedStyle(el);

            // Font Family
            const fontSelect = toolbar.querySelector('#sf-font-family');
            // Simple check - might need more robust matching
            // We can try to match the computed font family with options
            let currentFont = computed.fontFamily.replace(/['"]/g, ''); // Remove quotes for easier matching

            // Reset first
            fontSelect.value = '';

            for (let i = 0; i < fontSelect.options.length; i++) {
                let optVal = fontSelect.options[i].value.replace(/['"]/g, '');
                if (optVal && currentFont.includes(optVal.split(',')[0])) { // Match primary font
                    fontSelect.selectedIndex = i;
                    break;
                }
            }

            // Buttons
            const isBold = computed.fontWeight === '700' || computed.fontWeight === 'bold' || parseInt(computed
                .fontWeight) >= 700;
            const isItalic = computed.fontStyle === 'italic';
            const isUnderline = computed.textDecorationLine.includes('underline');

            toolbar.querySelector('[data-cmd="bold"]').classList.toggle('active', isBold);
            toolbar.querySelector('[data-cmd="italic"]').classList.toggle('active', isItalic);
            toolbar.querySelector('[data-cmd="underline"]').classList.toggle('active', isUnderline);

            // Alignment
            const align = computed.textAlign;
            toolbar.querySelector('[data-cmd="justifyLeft"]').classList.toggle('active', align === 'left' || align ===
                'start');
            toolbar.querySelector('[data-cmd="justifyCenter"]').classList.toggle('active', align === 'center');
            toolbar.querySelector('[data-cmd="justifyRight"]').classList.toggle('active', align === 'right');
            toolbar.querySelector('[data-cmd="justifyFull"]').classList.toggle('active', align === 'justify');
        }

        // Toolbar Actions
        toolbar.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const cmd = btn.dataset.cmd;

                if (activeElement) {
                    // Direct Style Manipulation
                    switch (cmd) {
                        case 'bold':
                            activeElement.style.fontWeight = (activeElement.style.fontWeight ===
                                    'bold' || activeElement.style.fontWeight === '700') ? 'normal' :
                                'bold';
                            break;
                        case 'italic':
                            activeElement.style.fontStyle = (activeElement.style.fontStyle ===
                                'italic') ? 'normal' : 'italic';
                            break;
                        case 'underline':
                            activeElement.style.textDecoration = (activeElement.style.textDecoration
                                .includes('underline')) ? 'none' : 'underline';
                            break;
                        case 'justifyLeft':
                            activeElement.style.textAlign = 'left';
                            break;
                        case 'justifyCenter':
                            activeElement.style.textAlign = 'center';
                            break;
                        case 'justifyRight':
                            activeElement.style.textAlign = 'right';
                            break;
                        case 'justifyFull':
                            activeElement.style.textAlign = 'justify';
                            break;
                    }

                    activeElement.focus(); // Keep focus
                    updateToolbarState(activeElement);
                }
            });
        });

        toolbar.querySelector('#sf-font-family').addEventListener('change', (e) => {
            const font = e.target.value;
            if (activeElement && font) {
                // execCommand 'fontName' is tricky with custom fonts, let's try style
                // doc.execCommand('fontName', false, font); 
                // Better to apply style directly to the element for block level
                activeElement.style.fontFamily = font;
                activeElement.focus();
            }
        });

        // Update position on scroll/resize
        window.addEventListener('scroll', () => {
            if (activeElement) updateToolbarPosition(activeElement);
        }, true);
        window.addEventListener('resize', () => {
            if (activeElement) updateToolbarPosition(activeElement);
        });
    }


    function tryBuildSidebar(attempt) {
        if (attempt > 10) {
            hideLoading();
            return;
        }
        if (iframeDoc.body && iframeDoc.querySelector('style')) {
            buildSidebar();
            hideLoading();
            return;
        }
        setTimeout(() => tryBuildSidebar(attempt + 1), 150);
    }



    function showLoading(text) {
        const overlay = document.getElementById('loading-overlay');
        const textEl = document.getElementById('loading-text');
        if (text) textEl.textContent = text;
        overlay.classList.remove('hidden');
        // Force reflow
        void overlay.offsetWidth;
        overlay.classList.add('visible');
    }

    function hideLoading() {
        const overlay = document.getElementById('loading-overlay');
        overlay.classList.remove('visible');
        setTimeout(() => {
            overlay.classList.add('hidden');
        }, 300);

        function showLoading(msg = 'Carregando...') {
            const overlay = document.getElementById('loadingOverlay');
            overlay.querySelector('p').textContent = msg;
            overlay.style.display = 'flex';
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }

        function getCleanHTML() {
            // Clone the document to avoid modifying the live editor
            const clone = iframeDoc.documentElement.cloneNode(true);

            // Remove editor artifacts from the clone
            const toolbar = clone.querySelector('#sf-editor-toolbar');
            if (toolbar) toolbar.remove();

            const editables = clone.querySelectorAll('[contenteditable]');
            editables.forEach(el => el.removeAttribute('contenteditable'));

            const editorStyle = clone.querySelector('#sf-editor-style');
            if (editorStyle) editorStyle.remove();

            return clone.outerHTML;
        }

        document.getElementById('saveProject').onclick = async () => {
                    showLoading('Salvando projeto...');
                    try {
                        const html = iframeDoc.documentElement.outerHTML;
                        const form = new FormData();
                        form.append('id', PROJECT_ID || '');
                        form.append('name', PROJECT_NAME);
                        form.append('html', html);

                        if (TEMPLATE_ID) {
                            form.append('template_id', TEMPLATE_ID);
                        }

                        const res = await fetch('/projects/save', {
                            method: 'POST',
                            body: form
                        });
                        const data = await res.json();

                        if (data.success) {
                            // Short delay to show success state
                            setTimeout(() => {
                                hideLoading();
                                // alert('Projeto salvo!'); // Removed for better UX
                                if (!PROJECT_ID && data.project_id) {
                                    window.location.href = '/editor?id=' + data.project_id;
                                }
                            }, 2500); // Increased to 2.5s for visibility
                        } else {
                            hideLoading();
                            console.error('Erro ao salvar o projeto');
                        }
                    } catch (e) {
                        hideLoading();
                        console.error('Erro de conexão ao salvar.', e);
                        const html = getCleanHTML();
                        const form = new FormData();
                        form.append('id', PROJECT_ID || '');
                        form.append('name', PROJECT_NAME);
                        form.append('html', html);

                        if (TEMPLATE_ID) {
                            form.append('template_id', TEMPLATE_ID);
                        }

                        try {
                            const res = await fetch('/projects/save', {
                                method: 'POST',
                                body: form
                            });
                            const data = await res.json();

                            if (data.success) {
                                alert('Projeto salvo!');
                                if (!PROJECT_ID && data.project_id) {
                                    window.location.href = '/editor?id=' + data.project_id;
                                }
                            } else {
                                alert('Erro ao salvar o projeto');
                            }
                        } catch (e) {
                            alert('Erro de conexão ao salvar');
                        } finally {
                            hideLoading();
                        }
                    };

                    document.getElementById('preview').onclick = () => {

                        showLoading('Abrindo preview...');
                        setTimeout(() => {
                            const blob = new Blob([iframeDoc.documentElement.outerHTML], {
                                type: 'text/html'
                            });
                            const url = URL.createObjectURL(blob);
                            window.open(url, '_blank');
                            hideLoading();
                        }, 2000); // Increased to 2s

                        showLoading('Gerando preview...');
                        setTimeout(() => {
                            const blob = new Blob([getCleanHTML()], {
                                type: 'text/html'
                            });
                            const url = URL.createObjectURL(blob);
                            window.open(url, '_blank');
                            hideLoading();
                        }, 500);
                    };

                    document.getElementById('downloadSite').onclick = async () => {
                        showLoading('Gerando arquivo ZIP...');
                        try {
                            const zip = new JSZip();
                            zip.file('index.html', iframeDoc.documentElement.outerHTML);
                            const blob = await zip.generateAsync({
                                type: 'blob'
                            });
                            saveAs(blob, PROJECT_NAME + '.zip');
                            hideLoading();
                        } catch (e) {
                            hideLoading();
                            alert('Erro ao gerar download.');
                            console.error(e);
                            zip.file('index.html', getCleanHTML());
                            const blob = await zip.generateAsync({
                                type: 'blob'
                            });
                            saveAs(blob, PROJECT_NAME + '.zip');
                        }
                    };

                    window.toggleCategory = toggleCategory;
    </script>

</body>

</html>