#!/usr/bin/env php
<?php
/**
 * Clean SQLite - Deleta o arquivo do banco completamente
 *
 * Uso: php database/clean.php
 *
 * Isto remove COMPLETAMENTE o arquivo app.db
 */

echo "🗑️  Limpeza Completa do Banco SQLite\n";
echo str_repeat("=", 50) . "\n\n";

$dbFile = __DIR__ . '/database.sqlite';
$journalFile = __DIR__ . '/app.db-journal';
$shmFile = __DIR__ . '/app.db-shm';
$walFile = __DIR__ . '/app.db-wal';

$files = [$dbFile, $journalFile, $shmFile, $walFile];

foreach ($files as $file) {
    if (file_exists($file)) {
        try {
            if (unlink($file)) {
                echo "✅ Deletado: " . basename($file) . "\n";
            } else {
                echo "❌ Erro ao deletar: " . basename($file) . "\n";
            }
        } catch (Exception $e) {
            echo "❌ Erro: " . $e->getMessage() . "\n";
        }
    } else {
        echo "ℹ️  Não existe: " . basename($file) . "\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ Banco de dados completamente deletado!\n";
echo "⏭️  Execute agora: php database/migrate.php migrate\n\n";
