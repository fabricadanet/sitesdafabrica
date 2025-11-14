<?php
// config/routes.php
// Router principal — com todos os endpoints
// ✅ CORRIGIDO: Adicionado suporte a /api/ routes

use App\Controllers\AuthController;
use App\Controllers\EditorController;
use App\Controllers\ProjectController;
use App\Controllers\AdminController;

// Pega o path da URL (sem query string)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Normaliza barra final
$uri = rtrim($uri, '/');
if ($uri === '') $uri = '/';

// Remover /api do início se existir (para roteamento)
$isApi = strpos($uri, '/api') === 0;
$cleanUri = $isApi ? substr($uri, 4) : $uri;

switch ($cleanUri) {

    // 🔐 Autenticação
    case '/':
    case '/login':
        if ($method === 'GET' || $method === 'POST') {
            (new AuthController)->login();
        }
        break;

    case '/register':
        if ($method === 'GET' || $method === 'POST') {
            (new AuthController)->register();
        }
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
        if (!$isApi) {
            header('Content-Type: text/html; charset=utf-8');
            (new ProjectController)->list();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Use GET /projects ou POST /api/projects/save']);
        }
        break;

    case '/projects/save':
        if ($method === 'POST') {
            header('Content-Type: application/json');
            (new ProjectController)->save();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método inválido. Use POST']);
        }
        break;

    case '/projects/get':
        header('Content-Type: application/json');
        (new ProjectController)->get();
        break;

    case '/projects/templates':
        header('Content-Type: application/json');
        (new ProjectController)->getTemplates();
        break;

    case '/projects/delete':
        if ($method === 'POST' || $method === 'GET') {
            header('Content-Type: application/json');
            (new ProjectController)->delete();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
        }
        break;

    case '/projects/user-data':
        header('Content-Type: application/json');
        (new ProjectController)->userData();
        break;

    case '/projects/update-profile':
        if ($method === 'POST') {
            header('Content-Type: application/json');
            (new ProjectController)->updateProfile();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
        }
        break;

    case '/projects/plans-list':
    case '/projects/plans':
        header('Content-Type: application/json');
        (new ProjectController)->plansList();
        break;

    case '/projects/upgrade-plan':
    case '/projects/upgrade':
        if ($method === 'POST') {
            header('Content-Type: application/json');
            (new ProjectController)->upgradePlan();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
        }
        break;

    // ⚙️ ADMIN DASHBOARD
    case '/admin':
        if (!$isApi) {
            (new AdminController)->dashboard();
        } else {
            http_response_code(404);
        }
        break;

    // 📋 TEMPLATES ADMIN
    case '/admin/templates':
        if (!$isApi) {
            (new AdminController)->templates();
        } else {
            header('Content-Type: application/json');
            (new AdminController)->templates();
        }
        break;

    case '/admin/template/save':
        if ($method === 'POST') {
            header('Content-Type: application/json');
            (new AdminController)->templateSave();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false]);
        }
        break;

    case '/admin/template/get':
        header('Content-Type: application/json');
        (new AdminController)->templateGet();
        break;

    case '/admin/template/delete':
        if ($method === 'POST' || $method === 'GET') {
            header('Content-Type: application/json');
            (new AdminController)->templateDelete();
        }
        break;

    // 👥 USUÁRIOS ADMIN
    case '/admin/users':
        if (!$isApi) {
            (new AdminController)->users();
        } else {
            header('Content-Type: application/json');
            (new AdminController)->users();
        }
        break;

    case '/admin/user/get':
        header('Content-Type: application/json');
        (new AdminController)->userGet();
        break;

    case '/admin/user/save':
        if ($method === 'POST') {
            header('Content-Type: application/json');
            (new AdminController)->userSave();
        }
        break;

    case '/admin/user/role':
        if ($method === 'POST') {
            header('Content-Type: application/json');
            (new AdminController)->userChangeRole();
        }
        break;

    case '/admin/user/delete':
        if ($method === 'POST' || $method === 'GET') {
            header('Content-Type: application/json');
            (new AdminController)->userDelete();
        }
        break;

    // 📁 PROJETOS ADMIN
    case '/admin/projects':
        if (!$isApi) {
            (new AdminController)->projects();
        } else {
            header('Content-Type: application/json');
            (new AdminController)->projects();
        }
        break;

    case '/admin/project/delete':
        if ($method === 'POST' || $method === 'GET') {
            header('Content-Type: application/json');
            (new AdminController)->projectDelete();
        }
        break;

    // 💰 PLANOS ADMIN
    case '/admin/plans':
        if (!$isApi) {
            (new AdminController)->plans();
        } else {
            header('Content-Type: application/json');
            (new AdminController)->plans();
        }
        break;

    case '/admin/plan/save':
        if ($method === 'POST') {
            header('Content-Type: application/json');
            (new AdminController)->planSave();
        }
        break;

    case '/admin/plan/get':
        header('Content-Type: application/json');
        (new AdminController)->planGet();
        break;

    case '/admin/plan/delete':
        if ($method === 'POST' || $method === 'GET') {
            header('Content-Type: application/json');
            (new AdminController)->planDelete();
        }
        break;

    // 🔄 ASSINATURAS ADMIN
    case '/admin/subscriptions':
        if (!$isApi) {
            (new AdminController)->subscriptions();
        } else {
            header('Content-Type: application/json');
            (new AdminController)->subscriptions();
        }
        break;

    case '/admin/plans/list':
        header('Content-Type: application/json');
        (new AdminController)->plansList();
        break;

    case '/admin/subscription/create':
        if ($method === 'POST') {
            header('Content-Type: application/json');
            (new AdminController)->subscriptionCreate();
        }
        break;

    case '/admin/subscription/cancel':
        if ($method === 'POST' || $method === 'GET') {
            header('Content-Type: application/json');
            (new AdminController)->subscriptionCancel();
        }
        break;

    default:
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => '404 - Rota não encontrada: ' . $uri
        ]);
}