#!/usr/bin/env php
<?php
/**
 * Setup de Migrations para SQLite/MySQL
 *
 * Uso: php database/setup.php
 *
 * Este script:
 * 1. Cria as pastas necessárias
 * 2. Copia o Migrator corrigido
 * 3. Cria as migrations corrigidas (sem o sufixo _SQLITE)
 * 4. Tudo pronto para usar!
 */

$baseDir = __DIR__;
$migrationsDir = $baseDir . '/migrations';
$seedersDir = $baseDir . '/seeders';

// Cores para output
$colors = [
    'reset' => "\033[0m",
    'green' => "\033[32m",
    'red' => "\033[31m",
    'yellow' => "\033[33m",
    'blue' => "\033[34m",
];

function echo_success($msg) {
    global $colors;
    echo $colors['green'] . "✅ " . $msg . $colors['reset'] . "\n";
}

function echo_error($msg) {
    global $colors;
    echo $colors['red'] . "❌ " . $msg . $colors['reset'] . "\n";
}

function echo_info($msg) {
    global $colors;
    echo $colors['blue'] . "ℹ️  " . $msg . $colors['reset'] . "\n";
}

function echo_section($msg) {
    global $colors;
    echo "\n" . $colors['yellow'] . "=== " . $msg . " ===" . $colors['reset'] . "\n";
}

// Criar pastas
echo_section("1. CRIANDO PASTAS");
if (!is_dir($migrationsDir)) {
    mkdir($migrationsDir, 0755, true);
    echo_success("Pasta database/migrations criada");
} else {
    echo_info("Pasta database/migrations já existe");
}

if (!is_dir($seedersDir)) {
    mkdir($seedersDir, 0755, true);
    echo_success("Pasta database/seeders criada");
} else {
    echo_info("Pasta database/seeders já existe");
}

// Verificar se está em Windows ou Linux
$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
echo_info("Sistema Operacional: " . ($isWindows ? "Windows" : "Linux/Mac"));

echo_section("2. VERIFICAÇÃO DE MIGRATOR");

if (file_exists($baseDir . '/Migrator.php')) {
    echo_success("Migrator.php já existe");
} else {
    echo_error("Migrator.php não encontrado em database/");
    echo_info("Copie Migrator_CORRIGIDO.php para database/Migrator.php");
}

echo_section("3. LISTANDO MIGRATIONS ENCONTRADAS");

$outputDir = __DIR__ . '/../../../mnt/user-data/outputs';
if (is_dir($outputDir)) {
    $files = glob($outputDir . '/2025_*.php');
    $count = 0;
    foreach ($files as $file) {
        $basename = basename($file);
        echo "  • $basename\n";
        $count++;
    }
    echo_info("Total de $count migrations encontradas");
} else {
    echo_error("Pasta /mnt/user-data/outputs não encontrada");
}

echo_section("4. PRÓXIMOS PASSOS");

echo "1️⃣  Copie Migrator_CORRIGIDO.php para database/Migrator.php:\n";
echo "    cp Migrator_CORRIGIDO.php database/Migrator.php\n\n";

echo "2️⃣  Copie as migrations corrigidas (sem _SQLITE) para database/migrations/:\n";
echo "    cp 2025_01_01_000001_create_users_table_SQLITE.php database/migrations/2025_01_01_000001_create_users_table.php\n";
echo "    cp 2025_01_01_000002_create_plans_table_SQLITE.php database/migrations/2025_01_01_000002_create_plans_table.php\n";
echo "    cp 2025_01_01_000003_create_subscriptions_table_SQLITE.php database/migrations/2025_01_01_000003_create_subscriptions_table.php\n";
echo "    cp 2025_01_01_000004_create_projects_table_SQLITE.php database/migrations/2025_01_01_000004_create_projects_table.php\n";
echo "    cp 2025_01_01_000005_create_user_domains_table_SQLITE.php database/migrations/2025_01_01_000005_create_user_domains_table.php\n";
echo "    cp 2025_01_01_000006_create_templates_library_table_SQLITE.php database/migrations/2025_01_01_000006_create_templates_library_table.php\n";
echo "    cp 2025_01_01_000007_create_downloads_log_table_SQLITE.php database/migrations/2025_01_01_000007_create_downloads_log_table.php\n\n";

echo "3️⃣  Copie DatabaseSeeder.php:\n";
echo "    cp DatabaseSeeder.php database/seeders/\n\n";

echo "4️⃣  Copie migrate.php:\n";
echo "    cp migrate.php database/\n\n";

echo "5️⃣  Execute as migrations:\n";
echo "    php database/migrate.php migrate\n\n";

echo_section("✅ SETUP CONCLUÍDO");
echo "Siga os passos acima e suas migrations estarão prontas!\n\n";
