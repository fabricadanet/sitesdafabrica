<?php
$pdo = new \PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
$pdo->exec("ALTER TABLE projects ADD COLUMN global_vars TEXT DEFAULT '{}'");
echo "✅ Coluna 'global_vars' adicionada.\n";
