<?php
$pdo = require __DIR__ . '/../config/database.php';
$stmt = $pdo->query('SELECT id, name, html_file, status, is_premium FROM templates_library');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "TEMPLATES:\n";
print_r($rows);
