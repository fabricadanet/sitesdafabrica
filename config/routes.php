<?php
// config/routes.php
// Router principal do Sites da Fábrica — compatível com editor v3.0

use App\Controllers\AuthController;
use App\Controllers\EditorController;
use App\Controllers\ProjectController;

// 🔹 Pega o path da URL (sem query string)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// 🔹 Normaliza barra final
$uri = rtrim($uri, '/');
if ($uri === '') $uri = '/';

switch ($uri) {

  // 🔐 Autenticação
  case '/':
  case '/login':
    (new AuthController)->login();
    break;

  case '/register':
    (new AuthController)->register();
    break;

  case '/logout':
    (new AuthController)->logout();
    break;

  // 🧩 Editor visual
  case '/editor':
    (new EditorController)->index();
    break;

  // 💾 Projetos
  case '/projects':
    // Exibe a lista HTML (painel do usuário)
     header('Content-Type: text/html; charset=utf-8');
    (new ProjectController)->list();
    break;

  case '/projects/save':
    // Salvar (POST) — Ajax JSON
    if ($method === 'POST') {
      (new ProjectController)->save();
    } else {
      http_response_code(405);
      echo json_encode(['success' => false, 'message' => 'Método inválido.']);
    }
    break;

  case '/projects/get':
    // Obter projeto (GET)
    (new ProjectController)->get();
    break;

  case '/projects/delete':
    // Deletar projeto (GET ou POST)
    (new ProjectController)->delete();
    break;

  // 📂 (opcional) API JSON pura — ex: /api/projects
  case '/api/projects':
    header('Content-Type: application/json');
    (new ProjectController)->list();
    break;

  default:
    http_response_code(404);
    echo "404 - Página não encontrada";
}
