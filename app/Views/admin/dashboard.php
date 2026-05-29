<!-- app/views/admin/dashboard.php -->
<?php $pageTitle = 'Dashboard'; ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> — Sites da Fábrica Admin</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Outfit', 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 flex min-h-screen">

    <!-- 💻 BARRA LATERAL (Sidebar) -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col fixed inset-y-0 left-0 z-20 border-r border-slate-800">
        <div class="p-6 border-b border-slate-800 flex items-center gap-3">
            <div class="h-9 w-9 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                <i class="fa-solid fa-screwdriver-wrench text-base"></i>
            </div>
            <div>
                <h4 class="font-bold text-white text-base leading-tight">Fábrica Admin</h4>
                <p class="text-xs text-slate-500">Painel Geral</p>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
            <a href="/admin" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all bg-blue-600 text-white shadow-md shadow-blue-600/10">
                <i class="fa-solid fa-chart-pie text-lg"></i>
                <span>Dashboard</span>
            </a>
            <a href="/admin/templates" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all hover:bg-slate-800 hover:text-white text-slate-400">
                <i class="fa-solid fa-cubes text-lg"></i>
                <span>Templates</span>
            </a>
            <a href="/admin/plans" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all hover:bg-slate-800 hover:text-white text-slate-400">
                <i class="fa-solid fa-wallet text-lg"></i>
                <span>Planos</span>
            </a>
            <a href="/admin/users" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all hover:bg-slate-800 hover:text-white text-slate-400">
                <i class="fa-solid fa-users text-lg"></i>
                <span>Usuários</span>
            </a>
            <a href="/admin/projects" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all hover:bg-slate-800 hover:text-white text-slate-400">
                <i class="fa-solid fa-folder-open text-lg"></i>
                <span>Projetos</span>
            </a>
            <a href="/admin/subscriptions" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all hover:bg-slate-800 hover:text-white text-slate-400">
                <i class="fa-solid fa-rotate text-lg"></i>
                <span>Assinaturas</span>
            </a>

            <div class="pt-6 mt-6 border-t border-slate-800">
                <a href="/projects" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all hover:bg-slate-800 hover:text-white text-slate-400">
                    <i class="fa-solid fa-arrow-left-long text-lg"></i>
                    <span>Voltar ao App</span>
                </a>
                <a href="/logout" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all hover:bg-red-500/10 hover:text-red-400 text-slate-400">
                    <i class="fa-solid fa-door-open text-lg"></i>
                    <span>Sair</span>
                </a>
            </div>
        </nav>

        <div class="p-4 border-t border-slate-800 bg-slate-950/40 flex items-center gap-3">
            <div class="h-9 w-9 bg-slate-800 rounded-full flex items-center justify-center text-slate-300 font-bold text-sm">
                <?= strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)) ?>
            </div>
            <div class="overflow-hidden">
                <p class="text-sm font-semibold text-white truncate"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></p>
                <p class="text-xs text-slate-500 truncate">Administrador</p>
            </div>
        </div>
    </aside>

    <!-- ⚡ CONTEÚDO PRINCIPAL -->
    <main class="flex-1 ml-64 flex flex-col min-w-0">
        
        <!-- TOPBAR -->
        <header class="h-20 bg-white border-b border-slate-200 px-8 flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-slate-900 leading-tight">📊 Painel Estatístico</h1>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-sm font-medium text-slate-900"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></p>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span> Admin
                    </span>
                </div>
            </div>
        </header>

        <!-- ESPAÇO DO CONTEÚDO -->
        <div class="p-8 space-y-8 max-w-[1600px] w-full mx-auto">
            
            <!-- 📊 GRID DE CARTÕES (Cards) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- 1. Receita MRR -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all flex items-center justify-between">
                    <div class="space-y-2">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Receita MRR</p>
                        <h3 class="text-3xl font-extrabold text-slate-900">R$ <?= number_format($mrr, 2, ',', '.') ?></h3>
                        <p class="text-xs text-emerald-600 font-medium">
                            <i class="fa-solid fa-circle-nodes mr-1"></i> Recorrência ativa
                        </p>
                    </div>
                    <div class="h-14 w-14 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 text-2xl shadow-inner">
                        <i class="fa-solid fa-money-bill-trend-up"></i>
                    </div>
                </div>

                <!-- 2. Clientes Ativos -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all flex items-center justify-between">
                    <div class="space-y-2">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Clientes Ativos</p>
                        <h3 class="text-3xl font-extrabold text-slate-900"><?= $totalUsers ?></h3>
                        <p class="text-xs text-blue-600 font-medium">
                            <i class="fa-solid fa-user-check mr-1"></i> Contas registradas
                        </p>
                    </div>
                    <div class="h-14 w-14 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 text-2xl shadow-inner">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>

                <!-- 3. Sites Publicados -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all flex items-center justify-between">
                    <div class="space-y-2">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Sites Publicados</p>
                        <h3 class="text-3xl font-extrabold text-slate-900"><?= $publishedProjects ?></h3>
                        <p class="text-xs text-violet-600 font-medium">
                            <i class="fa-solid fa-globe mr-1"></i> No ar com HTTPS
                        </p>
                    </div>
                    <div class="h-14 w-14 bg-violet-50 rounded-2xl flex items-center justify-center text-violet-600 text-2xl shadow-inner">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                </div>

            </div>

            <!-- 📁 TABELA DE ATIVIDADE RECENTE -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">⚡ Atividade Recente de Projetos</h3>
                        <p class="text-xs text-slate-500">Os últimos 5 projetos criados ou atualizados pelos clientes</p>
                    </div>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                        <span class="w-2 h-2 rounded-full bg-slate-400 animate-pulse"></span> Tempo Real
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Nome do Projeto</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Cliente</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Data de Criação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-150">
                            <?php if (!empty($recentProjects)): ?>
                                <?php foreach ($recentProjects as $project): ?>
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="h-9 w-9 bg-slate-100 rounded-lg flex items-center justify-center text-slate-500">
                                                    <i class="fa-solid fa-laptop-code text-sm"></i>
                                                </div>
                                                <span class="font-semibold text-slate-900 text-sm"><?= htmlspecialchars($project['name']) ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium text-slate-700 text-sm"><?= htmlspecialchars($project['user_name'] ?? 'Desconhecido') ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php 
                                                $status = strtolower($project['status'] ?? 'draft');
                                                if ($status === 'published' || $status === 'ativo' || (isset($project['is_published']) && $project['is_published'] == 1)) {
                                                    echo '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/50"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Publicado</span>';
                                                } else {
                                                    echo '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200/50"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Rascunho</span>';
                                                }
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-500">
                                            <?= date('d/m/Y H:i', strtotime($project['created_at'])) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="fa-regular fa-folder-open text-3xl opacity-40"></i>
                                            <span class="text-sm">Nenhum projeto recente registrado</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 🔗 LINKS DE ACESSO RÁPIDO -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <div class="h-10 w-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 text-lg">
                            <i class="fa-solid fa-cubes"></i>
                        </div>
                        <h4 class="text-base font-bold text-slate-900">📋 Gerenciar Templates</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">Adicione novos templates ou edite as estruturas base disponibilizadas na biblioteca de layouts para os clientes.</p>
                    </div>
                    <a href="/admin/templates" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all self-start">
                        Acessar Biblioteca <i class="fa-solid fa-arrow-right-long text-[10px]"></i>
                    </a>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <div class="h-10 w-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 text-lg">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                        <h4 class="text-base font-bold text-slate-900">💰 Configurar Planos</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">Defina a precificação mensal, limites máximos de projetos criados, suporte a domínios personalizados e acesso a templates premium.</p>
                    </div>
                    <a href="/admin/plans" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all self-start">
                        Configurar Planos <i class="fa-solid fa-arrow-right-long text-[10px]"></i>
                    </a>
                </div>
            </div>

        </div>
    </main>

</body>

</html>