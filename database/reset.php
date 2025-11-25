global$pdo; #!/usr/bin/env php
<?php
/**
 * Reset Database - Limpa tudo e recria do zero
 * Com suporte para desbloqueio de SQLite
 *
 * Uso: php database/reset.php
 */

require_once __DIR__ . '/../config/database.php';

echo "🔄 Limpando banco de dados...\n\n";

// Para SQLite, desabilitar foreign keys durante exclusão
if ($pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite') {
    echo "ℹ️  Detectado SQLite - aplicando correções...\n";
    $pdo->exec('PRAGMA foreign_keys = OFF');
    echo "✅ Foreign keys desabilitadas temporariamente\n\n";
}

$tables = [
    'downloads_log',
    'user_domains',
    'subscriptions',
    'projects',
    'templates_library',
    'plans',
    'users',
    'migrations'
];

$success = 0;
$failed = 0;

foreach ($tables as $table) {
    try {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "✅ Deletada: $table\n";
        $success++;
    } catch (Exception $e) {
        echo "⚠️  Erro ao deletar $table: " . $e->getMessage() . "\n";
        $failed++;
    }
}

// Reabilitar foreign keys
if ($pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite') {
    $pdo->exec('PRAGMA foreign_keys = ON');
    echo "\n✅ Foreign keys reabilitadas\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 Resultado: $success deletadas, $failed erros\n";
echo str_repeat("=", 50) . "\n\n";

if ($failed === 0) {
    echo "✅ Banco de dados limpo com sucesso!\n";
    echo "⏭️  Execute agora: php database/migrate.php migrate\n\n";
} else {
    echo "⚠️  Alguns erros ocorreram durante limpeza\n";
    echo "💡 Tente deletar manualmente: del database\\app.db (Windows) ou rm database/app.db (Linux)\n";
    echo "⏭️  Então execute: php database/migrate.php migrate\n\n";
}