<!-- app/views/projects/list.php -->
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Projetos – Sites da Fábrica</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    body {
        font-family: 'Inter', sans-serif;
    }

    .template-card {
        @apply bg-white rounded-xl overflow-hidden border border-slate-200 hover: border-blue-300 transition-all duration-300 hover:shadow-lg hover:scale-105 cursor-pointer;
    }

    .template-card:hover {
        @apply shadow-2xl;
    }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen">
    <!-- Header -->
    <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-xl border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl">
                    <span class="text-lg font-bold text-white">⚡</span>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Sites da Fábrica</h1>
                    <p class="text-xs text-slate-500">Gerenciador de Projetos</p>
                </div>
            </div>
            <button onclick="openTemplateModal()"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Novo Projeto
            </button>
            <!-- Botão Admin (visível apenas para admins) -->

            <a href="/admin"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl active:scale-95"
                title="Painel Administrativo">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Admin
            </a>

            <a href="/logout" onclick="return confirm('Tem certeza que deseja fazer logout?');" class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-700 
    font-semibold rounded-lg transition-all duration-200 border border-red-200 hover:border-red-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Sair
            </a>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Título da Seção -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-slate-900 mb-2">Meus Projetos</h2>
            <p class="text-slate-600">Gerencie e edite todos os seus sites em um único lugar</p>
        </div>
        <!-- Lista de Projetos -->
        <?php if (!empty($projects)): ?>
        <div
            class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition-all">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50">
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Projeto</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Categoria</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Template</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Criado em</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-slate-700">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($projects as $p): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <!-- Projeto -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-200 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <span class="text-lg">🌐</span>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900"><?= htmlspecialchars($p['title']) ?></p>

                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                    <?= htmlspecialchars(ucfirst($p['template_category'] ?? 'geral')) ?>
                                </span>
                            </td>

                            <!-- Template -->
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                    <?= htmlspecialchars(ucfirst($p['template_title'] ?? 'Sem Template')) ?>
                                </span>
                            </td>


                            <!-- Data -->
                            <td class="px-6 py-4 text-sm text-slate-600">
                                <?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>


                            <!-- Ações -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="/editor?id=<?= $p['id'] ?>"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium rounded-lg transition-all duration-200 text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Editar
                                    </a>
                                    <button
                                        onclick="deleteProject(<?= $p['id'] ?>, '<?= htmlspecialchars($p['title']) ?>')"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-red-50 hover:bg-red-100 text-red-700 font-medium rounded-lg transition-all duration-200 text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Excluir
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <!-- Estado Vazio -->
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m0 0h6M6 12a6 6 0 11-12 0 6 6 0 0112 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-900 mb-2">Nenhum projeto ainda</h3>
            <p class="text-slate-600 mb-6">Comece criando seu primeiro site selecionando um template</p>
            <button onclick="openTemplateModal()"
                class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Criar Novo Projeto
            </button>
        </div>
        <?php endif; ?>
    </main>

    <!-- MODAL DE SELEÇÃO DE TEMPLATE -->
    <div id="templateModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
        style="display:none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Header Modal -->
            <div class="sticky top-0 bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Escolha um Template</h2>
                    <p class="text-sm text-slate-600 mt-1">Selecione o tipo de site que deseja criar</p>
                </div>
                <button type="button" onclick="closeTemplateModal()"
                    class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Body Modal -->
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="templateGrid"></div>
            </div>
        </div>
    </div>

    <!-- MODAL DE CRIAÇÃO DE PROJETO -->
    <div id="createModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
        style="display:none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">

            <!-- Header Modal -->
            <div class="border-b border-slate-200 px-6 py-4">
                <h2 class="text-xl font-bold text-slate-900">Criar Novo Projeto</h2>
                <p class="text-sm text-slate-600 mt-1">Dê um nome ao seu novo site</p>
            </div>

            <!-- Body Modal -->
            <div class="p-6">
                <form id="createForm" class="space-y-4">
                    <div>
                        <label for="projectTitle" class="block text-sm font-semibold text-slate-700 mb-2">
                            Nome do Projeto
                        </label>
                        <input type="text" id="projectTitle" name="title" placeholder="Ex: Meu site Incrível" required
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-lg text-slate-900 placeholder-slate-500
                        focus:bg-white focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none transition-all">
                    </div>

                    <!-- Template selecionado -->
                    <input type="hidden" name="template" id="templateChoice">
                    <input type="hidden" name="category" id="categoryChoice">

                    <!-- IMPORTANTE: O botão agora é SUBMIT -->
                    <button type="submit"
                        class="w-full px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800
                    text-white font-semibold rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl active:scale-95">
                        Criar Projeto
                    </button>
                </form>
            </div>

            <!-- Footer Modal -->
            <div class="border-t border-slate-200 px-6 py-4 flex items-center justify-end">
                <button type="button" onclick="closeCreateModal()"
                    class="px-4 py-2.5 text-slate-700 font-semibold bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors duration-200">
                    Cancelar
                </button>
            </div>
        </div>
    </div>


    <!-- MODAL DE CONFIRMAÇÃO DE EXCLUSÃO -->
    <div id="deleteModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
        style="display:none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full">
            <!-- Body Modal -->
            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 bg-red-100 rounded-full mx-auto mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4v2m0 0v2m-6-6v-6a2 2 0 012-2h6a2 2 0 012 2v6m-9 0a9 9 0 1118 0 9 9 0 01-18 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 text-center mb-2">Excluir Projeto?</h3>
                <p class="text-slate-600 text-center mb-6">
                    Tem certeza que deseja excluir o projeto <span id="projectNameDelete" class="font-semibold"></span>?
                    Esta ação não pode ser desfeita.
                </p>
            </div>

            <!-- Footer Modal -->
            <div class="border-t border-slate-200 px-6 py-4 flex items-center justify-end gap-3">
                <button type="button" onclick="closeDeleteModal()"
                    class="px-4 py-2.5 text-slate-700 font-semibold bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors duration-200">
                    Cancelar
                </button>
                <button type="button" onclick="confirmDelete()"
                    class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl active:scale-95">
                    Excluir
                </button>
            </div>
        </div>
    </div>

    <script>
    let templates = []; // Array vazio, será preenchido via AJAX

    // Carregar templates ao abrir o modal
    async function openTemplateModal() {
        try {
            const res = await fetch('/projects/templates');
            const data = await res.json();

            if (data.success && data.data) {
                templates = data.data;
            }
        } catch (error) {
            console.warn('Erro ao carregar templates:', error);
            // Fallback para templates hardcoded em caso de erro
            templates = [{
                    name: 'institucional',
                    title: 'Institucional',
                    thumb: '/templates/thumbs/institucional.jpg',
                    description: 'Para empresas e profissionais'
                },
                {
                    name: 'restaurante',
                    title: 'Restaurante',
                    thumb: '/templates/thumbs/restaurante1.jpg',
                    description: 'Cardápio e reservas'
                },
            ];
        }

        const grid = document.getElementById('templateGrid');
        grid.innerHTML = templates.map(t => `
    <div class="template-card group" onclick="chooseTemplate('${t.html_file || t.name}')">
    <div class="relative overflow-hidden bg-gradient-to-br from-slate-100 to-slate-200 h-40 flex items-center justify-center">
    ${t.thumb ? `<img src="/templates/thumbs/${t.thumb}" style="width:100%; height:100%; object-fit:cover;" alt="${t.title}">` : `<span class="text-5xl group-hover:scale-110 transition-transform duration-300">📄</span>`}
    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
    </div>
    <div class="p-4">
    <h3 class="font-bold text-slate-900 text-center mb-1">${t.title}</h3>
    <p class="text-xs text-slate-600 text-center">${t.description || 'Template customizado'}</p>
    </div>
    </div>
    `).join('');

        document.getElementById('templateModal').style.display = 'flex';
    }

    let pendingDeleteId = null;

    // ==== HELPER PARA REMOVER EXTENSÃO .html ====
    function getTemplateNameWithoutExtension(filename) {
        if (!filename) return '';
        return filename.replace(/\.html$/i, '');
    }

    function closeTemplateModal() {
        document.getElementById('templateModal').style.display = 'none';
    }

    let isCreating = false;

    // Ao clicar em um template
    function chooseTemplate(templateName) {
        const clean = getTemplateNameWithoutExtension(templateName);

        // Salva imediatamente
        const input = document.getElementById('templateChoice');
        input.value = clean;
        input.setAttribute('value', clean);

        document.getElementById('templateModal').style.display = 'none';
        document.getElementById('createModal').style.display = 'flex';

        setTimeout(() => document.getElementById('projectTitle').focus(), 80);
    }

    // FORMA ÚNICA DE ENVIAR O FORM
    document.getElementById('createForm').addEventListener('submit', function(e) {
        e.preventDefault();
        createProject();
    });

    // Criar projeto — garantido sem duplicação
    async function createProject() {
        if (isCreating) return; // evita salvar 2x
        isCreating = true;

        const form = document.getElementById('createForm');
        const title = document.getElementById('projectTitle').value.trim();
        const template = document.getElementById('templateChoice').value;

        if (!title) {
            alert('Por favor, insira um nome para o projeto');
            isCreating = false;
            return;
        }

        const formData = new FormData(form);

        try {
            const res = await fetch('/projects/save', {
                method: 'POST',
                body: formData
            });

            const data = await res.json();

            if (data.success) {
                window.location.href = `/editor?id=${data.id}&template=${template}`;
            } else {
                alert('Erro: ' + (data.message || 'Falha ao criar projeto'));
            }
        } catch (error) {
            alert('Erro ao criar projeto: ' + error.message);
        }

        isCreating = false;
    }



    function closeCreateModal() {
        document.getElementById('createModal').style.display = 'none';
        document.getElementById('createForm').reset();
    }

    async function createProject() {
        const form = document.getElementById('createForm');
        const title = document.getElementById('projectTitle').value.trim();
        const template = document.getElementById('templateChoice').value;

        if (!title) {
            alert('Por favor, insira um nome para o projeto');
            return;
        }

        const formData = new FormData(form);
        try {
            const res = await fetch('/projects/save', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                window.location.href = `/editor?id=${data.id}&template=${template}`;
            } else {
                alert('Erro: ' + (data.message || 'Falha ao criar projeto'));
            }
        } catch (error) {
            alert('Erro ao criar projeto: ' + error.message);
        }
    }

    // ==== EXCLUIR ====
    function deleteProject(id, title) {
        pendingDeleteId = id;
        document.getElementById('projectNameDelete').textContent = title;
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
        pendingDeleteId = null;
    }

    async function confirmDelete() {
        if (!pendingDeleteId) return;

        try {
            const res = await fetch(`/projects/delete?id=${pendingDeleteId}`);
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                alert('Erro ao excluir projeto');
            }
        } catch (error) {
            alert('Erro: ' + error.message);
        }
    }

    // Fechar modais ao clicar fora
    document.addEventListener('click', (e) => {
        if (e.target.id === 'templateModal') closeTemplateModal();
        if (e.target.id === 'createModal') closeCreateModal();
        if (e.target.id === 'deleteModal') closeDeleteModal();
    });

    // Enter para criar projeto
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && document.getElementById('createModal').style.display !== 'none') {
            e.preventDefault();
            createProject();
        }
    });
    </script>
</body>

</html>