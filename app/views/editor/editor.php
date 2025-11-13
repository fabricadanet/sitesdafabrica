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
    <style>
    /* Estilos para painéis colapsáveis */
    .panel {
        border: 1px solid #ddd;
        border-radius: 6px;
        margin-bottom: 1rem;
        overflow: hidden;
    }

    .panel-header {
        padding: 0.75rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: linear-gradient(135deg, #f0f9ff 0%, #f5f7fa 100%);
        border-bottom: 1px solid #ddd;
        user-select: none;
        transition: background-color 0.2s ease;
    }

    .panel-header:hover {
        background: linear-gradient(135deg, #e0f2fe 0%, #eff6ff 100%);
    }

    .panel-header.active {
        background: linear-gradient(135deg, #0284c7 0%, #1e40af 100%);
    }

    .panel-header.active h6 {
        color: white;
    }

    .panel-header.active .collapse-icon {
        color: white;
    }

    .panel-header h6 {
        margin: 0;
        font-size: 0.875rem;
        font-weight: 600;
        color: #0284c7;
        transition: color 0.2s ease;
    }

    .collapse-icon {
        display: inline-block;
        transition: transform 0.3s ease;
        font-size: 0.75rem;
        color: #0284c7;
    }

    .collapse-icon.collapsed {
        transform: rotate(-90deg);
    }

    .panel-content {
        display: block;
        padding: 0.75rem;
        max-height: 300px;
        overflow-y: auto;
        overflow-x: hidden;
        transition: all 0.3s ease;
        opacity: 1;
    }

    .panel-content.collapsed {
        display: none;
        max-height: 0;
        padding: 0;
        opacity: 0;
    }

    /* Scroll personalizado */
    .panel-content::-webkit-scrollbar {
        width: 6px;
    }

    .panel-content::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }

    .panel-content::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
        transition: background 0.2s ease;
    }

    .panel-content::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Estilos para imagens dentro dos painéis */
    .panel-content img {
        max-width: 100%;
        height: auto;
        display: block;
        border-radius: 6px;
        margin-bottom: 0.75rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: all 0.2s ease;
    }

    .panel-content img:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-color: #cbd5e1;
    }

    .panel-content img:last-child {
        margin-bottom: 0;
    }

    /* Container para grupo de imagens */
    .image-preview {
        width: 100%;
        overflow: hidden;
        border-radius: 6px;
        background: #f8fafc;
        padding: 0.5rem;
        margin-bottom: 0.75rem;
    }

    .image-preview img {
        width: 100%;
        height: auto;
        margin: 0;
        border: none;
        box-shadow: none;
    }

    /* Animação suave de colapso */
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .panel-content:not(.collapsed) {
        animation: slideDown 0.3s ease;
    }
    </style>
</head>

<body>

    <header>
        <div class="brand">⚡ Sites da Fábrica — Editor</div>
        <div class="actions">
            <button id="saveProject" class="btn">💾 Salvar</button>
            <button id="downloadSite" class="btn">⬇️ Baixar</button>
            <button id="preview" class="btn">👁️ Preview</button>
            <a href="/projects" class="btn">⬅️ Voltar</a>
            <a href="/logout" class="btn btn-logout" onclick="return confirm('Tem certeza que deseja fazer logout?');">
                🚪 Sair
            </a>

        </div>
    </header>

    <main>
        <!-- Área principal -->
        <div class="preview">
            <iframe id="editorFrame"></iframe>
        </div>
        <div id="editorContainer" style="display: flex; height: 100vh;">
            <!-- Sidebar -->
            <aside id="sidebar"
                style="width: 340px; background: #f9fafb; border-left: 1px solid #ddd; overflow-y: auto; padding: 1rem;">
                <h5 style="margin-bottom: 1rem;">🧱 Editor</h5>

                <!-- Painel Variáveis Globais -->
                <div id="panel-vars" class="panel">
                    <div class="panel-header" onclick="togglePanel('panel-vars')">
                        <h6>🎨 Variáveis Globais</h6>
                        <span class="collapse-icon">▼</span>
                    </div>
                    <div id="vars-container" class="panel-content"></div>
                </div>

                <!-- Painel Textos -->
                <div id="panel-texts" class="panel">
                    <div class="panel-header" onclick="togglePanel('panel-texts')">
                        <h6>🖋️ Textos</h6>
                        <span class="collapse-icon">▼</span>
                    </div>
                    <div id="texts-container" class="panel-content"></div>
                </div>

                <!-- Painel Imagens -->
                <div id="panel-images" class="panel">
                    <div class="panel-header" onclick="togglePanel('panel-images')">
                        <h6>🖼️ Imagens</h6>
                        <span class="collapse-icon">▼</span>
                    </div>
                    <div id="images-container" class="panel-content"></div>
                </div>
                <hr>
            </aside>
        </div>

        <script>
        const PROJECT_ID = <?= json_encode($project['id'] ?? null) ?>;
        const TEMPLATE_NAME = <?= json_encode($_GET['template'] ?? ($project['template'] ?? 'institucional')) ?>;
        const PROJECT_TITLE = <?= json_encode($project['title'] ?? '') ?>;


        // Função para fazer toggle dos painéis
        function togglePanel(panelId) {
            const panel = document.getElementById(panelId);
            const header = panel.querySelector('.panel-header');
            const content = panel.querySelector('.panel-content');
            const icon = panel.querySelector('.collapse-icon');

            // Toggle classes
            header.classList.toggle('active');
            content.classList.toggle('collapsed');
            icon.classList.toggle('collapsed');

            // Salvar estado do painel no localStorage
            const isOpen = !content.classList.contains('collapsed');
            localStorage.setItem(`panel-${panelId}`, isOpen ? 'open' : 'closed');
        }

        // Restaurar estado dos painéis ao carregar
        function restorePanelStates() {
            ['panel-vars', 'panel-texts', 'panel-images'].forEach(panelId => {
                const savedState = localStorage.getItem(`panel-${panelId}`);
                const panel = document.getElementById(panelId);
                const header = panel.querySelector('.panel-header');
                const content = panel.querySelector('.panel-content');
                const icon = panel.querySelector('.collapse-icon');

                // Se foi salvo como fechado, fechar
                if (savedState === 'closed') {
                    content.classList.add('collapsed');
                    icon.classList.add('collapsed');
                } else {
                    // Por padrão deixar abertos
                    header.classList.add('active');
                }
            });
        }

        // Restaurar estado ao carregar a página
        window.addEventListener('load', restorePanelStates);
        </script>

        <script src="/assets/js/editor.js"></script>

    </main>

</body>

</html>