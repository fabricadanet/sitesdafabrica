<?php
// Simulate logged-in user in test_projects.php
require_once __DIR__ . '/../vendor/autoload.php';
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Test User';

try {
    $controller = new \App\Controllers\ProjectController();
    $controller->list();
} catch (\Throwable $t) {
    echo "ERROR: " . $t->getMessage() . "\n" . $t->getTraceAsString() . "\n";
}
