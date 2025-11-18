<!-- app/views/auth/register.php -->
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar – Sites da Fábrica</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    body {
        font-family: 'Inter', sans-serif;
    }

    .input-focus:focus {
        @apply ring-2 ring-green-400 ring-offset-0;
    }
    </style>
</head>

<body
    class="min-h-screen bg-gradient-to-br from-slate-900 via-emerald-900 to-slate-900 flex items-center justify-center p-4">
    <!-- Decoração de fundo -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl"></div>
    </div>

    <!-- Conteúdo principal -->
    <div class="relative w-full max-w-md">
        <!-- Card -->
        <div class="bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl p-8 border border-white/20">
            <!-- Cabeçalho -->
            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center justify-center w-12 h-12 bg-gradient-to-br from-emerald-600 to-emerald-700 rounded-xl mb-4">
                    <span class="text-xl font-bold text-white">⚡</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 mb-1">Sites da Fábrica</h1>
                <p class="text-slate-600 text-sm">Crie sua conta e comece agora</p>
            </div>

            <!-- Erro -->
            <?php if (!empty($error)): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-medium text-red-700"><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>

            <!-- Formulário -->
            <form method="post" action="/register" class="space-y-5">
                <!-- Nome -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">
                        Nome Completo
                    </label>
                    <input type="text" name="name" id="name" placeholder="Seu nome" required
                        class="input-focus w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-lg text-slate-900 placeholder-slate-500 transition-all focus:bg-white focus:outline-none">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">
                        E-mail
                    </label>
                    <input type="email" name="email" id="email" placeholder="seu@email.com" required
                        class="input-focus w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-lg text-slate-900 placeholder-slate-500 transition-all focus:bg-white focus:outline-none">
                </div>

                <!-- Senha -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">
                        Senha
                    </label>
                    <input type="password" name="password" id="password" placeholder="••••••••" required minlength="6"
                        class="input-focus w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-lg text-slate-900 placeholder-slate-500 transition-all focus:bg-white focus:outline-none">
                    <p class="text-xs text-slate-500 mt-2">Mínimo 6 caracteres</p>
                </div>

                <!-- Botão Cadastrar -->
                <button type="submit"
                    class="w-full mt-6 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl active:scale-95">
                    Cadastrar
                </button>
            </form>

            <!-- Divisor -->
            <div class="my-6 flex items-center gap-3">
                <div class="flex-1 h-px bg-slate-200"></div>
                <span class="text-sm text-slate-500">ou</span>
                <div class="flex-1 h-px bg-slate-200"></div>
            </div>

            <!-- Link para login -->
            <div class="text-center">
                <span class="text-slate-600 text-sm">
                    Já tem conta?
                    <a href="/login" class="font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">
                        Fazer login
                    </a>
                </span>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-slate-400 text-xs">
            <p>© 2025 Sites da Fábrica. Todos os direitos reservados.</p>
        </div>
    </div>
</body>

</html>