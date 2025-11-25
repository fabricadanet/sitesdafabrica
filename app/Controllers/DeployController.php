<?php
// app/controllers/DeployController.php

namespace App\Controllers;

class DeployController
{
    private $pdo;

    public function __construct()
    {
        // Iniciar buffer de saída para capturar qualquer output indesejado
        if (!ob_get_level()) {
            ob_start();
        }
        
        try {
            $this->pdo = require __DIR__ . '/../../config/database.php';
            require_once __DIR__ . '/../helpers/whm_deploy.php';
          //  require_once __DIR__ . '/../helpers/cloudflare_cdn.php';
           // require_once __DIR__ . '/../helpers/analytics_injector.php';
            $this->requireAuth();
        } catch (\Exception $e) {
            // Limpar buffer em caso de erro
            ob_clean();
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao inicializar sistema: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    private function requireAuth()
    {
        if (!isset($_SESSION['user_id'])) {
            ob_clean();
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Não autorizado']);
            exit;
        }
    }

    // 🚀 PUBLICAR
    public function publish()
    {
        try {
            // Limpar qualquer output anterior
            if (ob_get_level()) {
                ob_clean();
            }
            
            header('Content-Type: application/json');
            
            $userId = $_SESSION['user_id'];
            $projectId = $_POST['project_id'] ?? null;

            if (!$projectId) {
                echo json_encode(['success' => false, 'message' => 'ID do projeto não informado']);
                return;
            }

            $result = deployProject($this->pdo, $projectId, $userId);
            echo json_encode($result);
            
        } catch (\Exception $e) {
            // Log do erro
            error_log("Erro em DeployController::publish - " . $e->getMessage());
            
            ob_clean();
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao publicar: ' . $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ]);
        }
    }

    // 🗑️ DESPUBLICAR
    public function unpublish()
    {
        try {
            if (ob_get_level()) {
                ob_clean();
            }
            
            header('Content-Type: application/json');
            
            $userId = $_SESSION['user_id'];
            $projectId = $_POST['project_id'] ?? null;

            if (!$projectId) {
                echo json_encode(['success' => false, 'message' => 'ID do projeto não informado']);
                return;
            }

            $result = unpublishProject($this->pdo, $projectId, $userId);
            echo json_encode($result);
            
        } catch (\Exception $e) {
            error_log("Erro em DeployController::unpublish - " . $e->getMessage());
            
            ob_clean();
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao despublicar: ' . $e->getMessage()
            ]);
        }
    }

    // 🌐 ADICIONAR DOMÍNIO PERSONALIZADO
    public function addDomain()
    {
        error_log("ADD-DOMAIN FOI CHAMADO");
        try {
            if (ob_get_level()) {
                ob_clean();
            }
            
            header('Content-Type: application/json');
            
            $userId = $_SESSION['user_id'];
            $projectId = $_POST['project_id'] ?? null;
            $customDomain = trim($_POST['custom_domain'] ?? '');

            if (!$projectId || !$customDomain) {
                echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
                return;
            }

            $result = addCustomDomain($this->pdo, $projectId, $userId, $customDomain);
            echo json_encode($result);
            
        } catch (\Exception $e) {
            error_log("Erro em DeployController::addDomain - " . $e->getMessage());
            
            ob_clean();
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao adicionar domínio deploy controller: ' . $e->getMessage()
            ]);
        }
    }

    // 🗑️ REMOVER DOMÍNIO PERSONALIZADO
    public function removeDomain()
    {
        try {
            if (ob_get_level()) {
                ob_clean();
            }
            
            header('Content-Type: application/json');
            
            $userId = $_SESSION['user_id'];
            $domainId = $_POST['domain_id'] ?? null;

            if (!$domainId) {
                echo json_encode(['success' => false, 'message' => 'ID do domínio não informado']);
                return;
            }

            $result = removeCustomDomain($this->pdo, $domainId, $userId);
            echo json_encode($result);
            
        } catch (\Exception $e) {
            error_log("Erro em DeployController::removeDomain - " . $e->getMessage());
            
            ob_clean();
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao remover domínio: ' . $e->getMessage()
            ]);
        }
    }

    // ✅ VERIFICAR DNS
    public function verifyDomain()
    {
        try {
            if (ob_get_level()) {
                ob_clean();
            }
            
            header('Content-Type: application/json');
            
            $domainId = $_POST['domain_id'] ?? null;

            if (!$domainId) {
                echo json_encode(['success' => false, 'message' => 'ID do domínio não informado']);
                return;
            }

            $result = verifyDomainDNS($this->pdo, $domainId);
            echo json_encode($result);
            
        } catch (\Exception $e) {
            error_log("Erro em DeployController::verifyDomain - " . $e->getMessage());
            
            ob_clean();
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao verificar domínio: ' . $e->getMessage()
            ]);
        }
    }

    // 📊 LISTAR DOMÍNIOS DO PROJETO
    public function listDomains()
    {
        try {
            if (ob_get_level()) {
                ob_clean();
            }
            
            header('Content-Type: application/json');
            
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
            
        } catch (\Exception $e) {
            error_log("Erro em DeployController::listDomains - " . $e->getMessage());
            
            ob_clean();
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao listar domínios: ' . $e->getMessage()
            ]);
        }
    }

    // 🔄 LIMPAR CACHE CLOUDFLARE
    public function purgeCache()
    {
        try {
            if (ob_get_level()) {
                ob_clean();
            }
            
            header('Content-Type: application/json');
            
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
            
        } catch (\Exception $e) {
            error_log("Erro em DeployController::purgeCache - " . $e->getMessage());
            
            ob_clean();
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao limpar cache: ' . $e->getMessage()
            ]);
        }
    }

    // 📊 CONFIGURAR GOOGLE ANALYTICS
    public function saveAnalytics()
    {
        try {
            if (ob_get_level()) {
                ob_clean();
            }
            
            header('Content-Type: application/json');
            
            $userId = $_SESSION['user_id'];
            $gaId = trim($_POST['ga_tracking_id'] ?? '');

            if (!$gaId) {
                echo json_encode(['success' => false, 'message' => 'ID do Google Analytics não informado']);
                return;
            }

            $result = saveUserAnalyticsId($this->pdo, $userId, $gaId);
            echo json_encode($result);
            
        } catch (\Exception $e) {
            error_log("Erro em DeployController::saveAnalytics - " . $e->getMessage());
            
            ob_clean();
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao salvar Analytics: ' . $e->getMessage()
            ]);
        }
    }
} 