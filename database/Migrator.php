<?php
// database/Migrator.php
// Versão final - Suporte SQLite e MySQL

namespace Database;

class Migrator
{
    private $pdo;
    private $dbDriver;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->dbDriver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    public function createMigrationsTable()
    {
        if ($this->dbDriver === 'sqlite') {
            $sql = "CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration TEXT UNIQUE NOT NULL,
                batch INTEGER NOT NULL,
                executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS migrations (
                id INT PRIMARY KEY AUTO_INCREMENT,
                migration VARCHAR(255) UNIQUE NOT NULL,
                batch INT NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        }

        try {
            $this->pdo->exec($sql);
            echo "✅ Tabela de migrations criada/verificada.\n";
        } catch (\Exception $e) {
            echo "❌ Erro: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    public function runPending()
    {
        $this->createMigrationsTable();

        $migrations = $this->getPendingMigrations();

        if (empty($migrations)) {
            echo "✓ Nenhuma migration pendente.\n";
            return true;
        }

        $batch = $this->getNextBatch();

        foreach ($migrations as $file => $migration) {
            try {
                echo "▶ Executando: {$migration}...\n";

                $this->runMigration($file);

                $stmt = $this->pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
                $stmt->execute([$migration, $batch]);

                echo "✅ {$migration} executada com sucesso.\n";
            } catch (\Exception $e) {
                echo "❌ Erro em {$migration}: " . $e->getMessage() . "\n";
                return false;
            }
        }

        echo "\n✅ Todas as migrations foram executadas com sucesso!\n";
        return true;
    }

    public function rollback()
    {
        $this->createMigrationsTable();

        $lastBatch = $this->getLastBatch();

        if ($lastBatch === null) {
            echo "✓ Nenhuma migration para reverter.\n";
            return true;
        }

        $stmt = $this->pdo->query("
            SELECT migration FROM migrations 
            WHERE batch = {$lastBatch} 
            ORDER BY id DESC
        ");
        $migrations = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($migrations as $migration) {
            try {
                echo "▶ Revertendo: {$migration}...\n";

                $this->rollbackMigration($migration);

                $stmt = $this->pdo->prepare("DELETE FROM migrations WHERE migration = ?");
                $stmt->execute([$migration]);

                echo "✅ {$migration} revertida com sucesso.\n";
            } catch (\Exception $e) {
                echo "❌ Erro ao reverter {$migration}: " . $e->getMessage() . "\n";
                return false;
            }
        }

        echo "\n✅ Todas as migrations foram revertidas com sucesso!\n";
        return true;
    }

    private function getPendingMigrations()
    {
        $executed = $this->getExecutedMigrations();
        $migrationsDir = __DIR__ . '/migrations';

        if (!is_dir($migrationsDir)) {
            echo "⚠️  Pasta database/migrations/ não encontrada\n";
            return [];
        }

        $files = scandir($migrationsDir);
        $pending = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') continue;
            if (!preg_match('/^20\d{2}_\d{2}_\d{2}_\d+_/', $file)) continue;

            $basename = pathinfo($file, PATHINFO_FILENAME);

            if (!in_array($basename, $executed)) {
                $pending[$file] = $basename;
            }
        }

        ksort($pending);
        return $pending;
    }

    private function getExecutedMigrations()
    {
        try {
            $stmt = $this->pdo->query("SELECT migration FROM migrations ORDER BY id");
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getNextBatch()
    {
        $stmt = $this->pdo->query("SELECT MAX(batch) as batch FROM migrations");
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return ($result['batch'] ?? 0) + 1;
    }

    private function getLastBatch()
    {
        $stmt = $this->pdo->query("SELECT MAX(batch) as batch FROM migrations WHERE batch > 0");
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['batch'] ?? null;
    }

    private function runMigration($file)
    {
        $migrationsDir = __DIR__ . '/migrations';
        $filepath = $migrationsDir . '/' . $file;

        if (!file_exists($filepath)) {
            throw new \Exception("Arquivo não encontrado: $filepath");
        }

        require_once $filepath;

        $basename = pathinfo($file, PATHINFO_FILENAME);
        $className = $this->getClassName($basename);

        if (!class_exists($className)) {
            throw new \Exception("Classe não encontrada: $className");
        }

        $instance = new $className($this->pdo, $this->dbDriver);

        if (!method_exists($instance, 'up')) {
            throw new \Exception("Método 'up' não encontrado em $className");
        }

        $sql = $instance->up();

        if (is_array($sql)) {
            foreach ($sql as $statement) {
                $stmt = trim($statement);
                if (!empty($stmt)) {
                    $this->pdo->exec($stmt);
                }
            }
        } else {
            $stmt = trim($sql);
            if (!empty($stmt)) {
                $this->pdo->exec($stmt);
            }
        }
    }

    private function rollbackMigration($migration)
    {
        $migrationsDir = __DIR__ . '/migrations';

        // Encontrar o arquivo
        $files = scandir($migrationsDir);
        $filepath = null;

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_FILENAME) === $migration) {
                $filepath = $migrationsDir . '/' . $file;
                break;
            }
        }

        if (!$filepath || !file_exists($filepath)) {
            throw new \Exception("Arquivo não encontrado para: $migration");
        }

        require_once $filepath;

        $className = $this->getClassName($migration);

        if (!class_exists($className)) {
            throw new \Exception("Classe não encontrada: $className");
        }

        $instance = new $className($this->pdo, $this->dbDriver);

        if (!method_exists($instance, 'down')) {
            throw new \Exception("Método 'down' não encontrado em $className");
        }

        $sql = $instance->down();

        if (is_array($sql)) {
            foreach ($sql as $statement) {
                $stmt = trim($statement);
                if (!empty($stmt)) {
                    $this->pdo->exec($stmt);
                }
            }
        } else {
            $stmt = trim($sql);
            if (!empty($stmt)) {
                $this->pdo->exec($stmt);
            }
        }
    }

    private function getClassName($migration)
    {
        // 2025_01_01_000001_create_users_table -> CreateUsersTable
        $parts = explode('_', $migration);
        $parts = array_slice($parts, 4);
        $className = implode('', array_map('ucfirst', $parts));
        return 'Database\\Migrations\\' . $className;
    }

    public function list()
    {
        $executed = $this->getExecutedMigrations();
        $migrationsDir = __DIR__ . '/migrations';

        echo "\n📋 Status das Migrations\n";
        echo "========================\n\n";

        if (!is_dir($migrationsDir)) {
            echo "❌ Pasta database/migrations/ não encontrada\n\n";
            return;
        }

        $files = scandir($migrationsDir);
        $found = false;

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') continue;
            if (!preg_match('/^20\d{2}_\d{2}_\d{2}_\d+_/', $file)) continue;

            $basename = pathinfo($file, PATHINFO_FILENAME);
            $status = in_array($basename, $executed) ? '✅ Executada' : '⏳ Pendente';
            echo "{$status} - {$basename}\n";
            $found = true;
        }

        if (!$found) {
            echo "❌ Nenhuma migration encontrada em database/migrations/\n";
        }

        echo "\nDriver: " . strtoupper($this->dbDriver) . "\n\n";
    }
}