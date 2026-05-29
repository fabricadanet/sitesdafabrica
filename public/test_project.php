<?php
$pdo = require __DIR__ . '/../config/database.php';
$stmt = $pdo->query('SELECT id, name, template_id, user_id FROM projects');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
