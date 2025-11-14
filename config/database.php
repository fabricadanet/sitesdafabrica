<?php
// config/database.php
// Conexão SQLite - Migrations cuidam das tabelas

$dbFile = __DIR__ . '/../database/app.db';

// Cria a pasta se não existir
if (!file_exists(dirname($dbFile))) {
    mkdir(dirname($dbFile), 0777, true);
}

try {
    // Conexão SQLite
    $pdo = new \PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    // IMPORTANTE: Habilitar foreign keys no SQLite
    $pdo->exec('PRAGMA foreign_keys = ON');

    return $pdo;

} catch (\PDOException $e) {
    die('Erro ao conectar ao banco SQLite: ' . $e->getMessage());
}

