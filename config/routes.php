<?php
// config/routes.php
// Router principal do Sites da Fábrica — com endpoints de admin

use App\Controllers\AuthController;
use App\Controllers\EditorController;
use App\Controllers\ProjectController;
use App\Controllers\AdminController;

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
    header('Content-Type: text/html; charset=utf-8');
    (new ProjectController)->list();
    break;

  case '/projects/save':
    if ($method === 'POST') {
      (new ProjectController)->save();
    } else {
      http_response_code(405);
      echo json_encode(['success' => false, 'message' => 'Método inválido.']);
    }
    break;

  case '/projects/get':
    (new ProjectController)->get();
    break;

  case '/projects/templates':
    (new ProjectController)->getTemplates();
    break;

  case '/projects/delete':
    (new ProjectController)->delete();
    break;

  // ⚙️ ADMIN DASHBOARD
  case '/admin':
    (new AdminController)->dashboard();
    break;

  // 📋 TEMPLATES ADMIN
  case '/admin/templates':
    (new AdminController)->templates();
    break;

  case '/admin/template/save':
    if ($method === 'POST') {
      (new AdminController)->templateSave();
    } else {
      http_response_code(405);
      echo json_encode(['success' => false]);
    }
    break;

  case '/admin/template/delete':
    (new AdminController)->templateDelete();
    break;

  // 👥 USUÁRIOS ADMIN
  case '/admin/users':
    (new AdminController)->users();
    break;

  case '/admin/user/role':
    if ($method === 'POST') {
      (new AdminController)->userChangeRole();
    }
    break;

  case '/admin/user/delete':
    (new AdminController)->userDelete();
    break;

  // 📁 PROJETOS ADMIN
  case '/admin/projects':
    (new AdminController)->projects();
    break;

  case '/admin/project/delete':
    (new AdminController)->projectDelete();
    break;

  // 💰 PLANOS ADMIN
  case '/admin/plans':
    (new AdminController)->plans();
    break;

  case '/admin/plan/save':
    if ($method === 'POST') {
      (new AdminController)->planSave();
    }
    break;

  case '/admin/plan/delete':
    (new AdminController)->planDelete();
    break;

  // 🔄 ASSINATURAS ADMIN
  case '/admin/subscriptions':
    (new AdminController)->subscriptions();
    break;

  case '/admin/subscription/cancel':
    (new AdminController)->subscriptionCancel();
    break;

  default:
    http_response_code(404);
    echo "404 - Página não encontrada";
}