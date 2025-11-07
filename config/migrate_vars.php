<?php

$dbFile = __DIR__ . '/database.sqlite';
try {
    echo "<h3>🚀 Executando migrações...</h3>";
    $pdo = new \PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    // Adiciona a coluna global_vars se não existir
    $pdo->exec("ALTER TABLE projects ADD COLUMN global_vars TEXT DEFAULT '{}'");
    echo "✅ Coluna 'global_vars' adicionada.\n";
} catch (\PDOException $e) {
    die('Erro ao conectar ao banco SQLite: ' . $e->getMessage());
}



