<?php
// app/controllers/DeployController.php

namespace App\Controllers;

class DeployController
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = require __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../helpers/whm_deploy.php';
        require_once __DIR__ . '/../helpers/cloudflare_cdn.php';
        require_once __DIR__ . '/../helpers/analytics_injector.php';
        $this->requireAuth();
    }

    private function requireAuth()
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Não autorizado']);
            exit;
        }
    }

    // 🚀 PUBLICAR
    public function publish()
    {
        $userId = $_SESSION['user_id'];
        $projectId = $_POST['project_id'] ?? null;

        if (!$projectId) {
            echo json_encode(['success' => false, 'message' => 'ID do projeto não informado']);
            return;
        }

        $result = deployProject($this->pdo, $projectId, $userId);
        echo json_encode($result);
    }

    // 🗑️ DESPUBLICAR
    public function unpublish()
    {
        $userId = $_SESSION['user_id'];
        $projectId = $_POST['project_id'] ?? null;

        if (!$projectId) {
            echo json_encode(['success' => false, 'message' => 'ID do projeto não informado']);
            return;
        }

        $result = unpublishProject($this->pdo, $projectId, $userId);
        echo json_encode($result);
    }

    // 🌐 ADICIONAR DOMÍNIO PERSONALIZADO
    public function addDomain()
    {
        $userId = $_SESSION['user_id'];
        $projectId = $_POST['project_id'] ?? null;
        $customDomain = trim($_POST['custom_domain'] ?? '');

        if (!$projectId || !$customDomain) {
            echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
            return;
        }

        $result = addCustomDomain($this->pdo, $projectId, $userId, $customDomain);
        echo json_encode($result);
    }

    // 🗑️ REMOVER DOMÍNIO PERSONALIZADO
    public function removeDomain()
    {
        $userId = $_SESSION['user_id'];
        $domainId = $_POST['domain_id'] ?? null;

        if (!$domainId) {
            echo json_encode(['success' => false, 'message' => 'ID do domínio não informado']);
            return;
        }

        $result = removeCustomDomain($this->pdo, $domainId, $userId);
        echo json_encode($result);
    }

    // ✅ VERIFICAR DNS
    public function verifyDomain()
    {
        $domainId = $_POST['domain_id'] ?? null;

        if (!$domainId) {
            echo json_encode(['success' => false, 'message' => 'ID do domínio não informado']);
            return;
        }

        $result = verifyDomainDNS($this->pdo, $domainId);
        echo json_encode($result);
    }

    // 📊 LISTAR DOMÍNIOS DO PROJETO
    public function listDomains()
    {
        $userId = $_SESSION['user_id'];
        $projectId = $_GET['project_id'] ?? null;

        if (!$projectId) {
            echo json_encode(['success' => false, 'message' => 'ID do projeto não informado']);
            return;
        }

        $stmt = $this->pdo->prepare("
            SELECT d.* 
            FROM user_domains d 
            WHERE d.project_id = ? AND d.user_id = ?
            ORDER BY d.created_at DESC
        ");
        $stmt->execute([$projectId, $userId]);
        $domains = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'domains' => $domains]);
    }

    // 🔄 LIMPAR CACHE CLOUDFLARE
    public function purgeCache()
    {
        $userId = $_SESSION['user_id'];
        $projectId = $_POST['project_id'] ?? null;

        if (!$projectId) {
            echo json_encode(['success' => false, 'message' => 'ID do projeto não informado']);
            return;
        }

        $stmt = $this->pdo->prepare("
            SELECT subdomain 
            FROM projects 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$projectId, $userId]);
        $project = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$project) {
            echo json_encode(['success' => false, 'message' => 'Projeto não encontrado']);
            return;
        }

        $result = purgeCloudflareCache($project['subdomain']);
        echo json_encode([
            'success' => $result['success'],
            'message' => $result['success'] ? 'Cache limpo com sucesso!' : 'Erro ao limpar cache'
        ]);
    }

    // 📊 CONFIGURAR GOOGLE ANALYTICS
    public function saveAnalytics()
    {
        $userId = $_SESSION['user_id'];
        $gaId = trim($_POST['ga_tracking_id'] ?? '');

        if (!$gaId) {
            echo json_encode(['success' => false, 'message' => 'ID do Google Analytics não informado']);
            return;
        }

        $result = saveUserAnalyticsId($this->pdo, $userId, $gaId);
        echo json_encode($result);
    }
}