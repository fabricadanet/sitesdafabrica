<?php
// public/index.php
session_start();

// Gera um token de segurança único para a sessão se ele não existir
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/routes.php';
