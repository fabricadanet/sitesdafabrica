#!/usr/bin/env php
<?php
// database/migrate.php - Script CLI para executar migrations

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Migrator.php';

use Database\Migrator;

// Obter comando da linha de comando
$command = $argv[1] ?? 'migrate';

// Criar instância do migrator
$migrator = new Migrator($pdo, __DIR__);

// Executar comando
switch ($command) {
    case 'migrate':
    case 'up':
        echo "\n🚀 Executando Migrations...\n";
        echo "============================\n\n";
        $migrator->runPending();
        break;

    case 'rollback':
    case 'down':
        echo "\n⬅️  Revertendo Migrations...\n";
        echo "============================\n\n";
        if ($migrator->rollback()) {
            echo "\n✅ Rollback concluído com sucesso!\n";
        }
        break;

    case 'status':
    case 'list':
        $migrator->list();
        break;

    case 'fresh':
        echo "\n🔄 Recriando banco de dados...\n";
        echo "================================\n\n";
        if ($migrator->rollback()) {
            echo "\n▶ Executando migrations novamente...\n\n";
            $migrator->runPending();
        }
        break;

    case 'seed':
        echo "\n🌱 Populando banco de dados com dados iniciais...\n";
        echo "================================================\n\n";
        require_once __DIR__ . '/seeders/DatabaseSeeder.php';
        break;

    default:
        echo "\n❓ Comando não reconhecido: {$command}\n\n";
        echo "Comandos disponíveis:\n";
        echo "  migrate (ou up)     - Executa migrations pendentes\n";
        echo "  rollback (ou down)  - Reverte a última batch de migrations\n";
        echo "  status (ou list)    - Lista status das migrations\n";
        echo "  fresh               - Reverte e executa novamente todas\n";
        echo "  seed                - Popula dados iniciais\n";
        echo "\nExemplo de uso:\n";
        echo "  php database/migrate.php migrate\n";
        echo "  php database/migrate.php rollback\n";
        echo "\n";
        exit(1);
}

exit(0);