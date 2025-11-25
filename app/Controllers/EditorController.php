<?php
namespace App\Controllers;
use \PDO;

class EditorController {
    private $pdo;

    public function __construct() {
        $this->pdo = require __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../helpers/subscription.php';
    }

    public function index() {
        $user_id = $_SESSION['user_id'];
        $canAccess = userCanAccessPremium($this->pdo, $user_id);

        $projectId = $_GET['id'] ?? null;
        $templateId = $_GET['template'] ?? null;
        $project = null;
        $templateHtml = null;
        $projectName = "Novo Projeto";

        // ===== CASO 1: Editando projeto existente =====
        if ($projectId) {
            $stmt = $this->pdo->prepare("
                SELECT p.*, t.html_file, t.name AS template_name
                FROM projects p
                LEFT JOIN templates_library t ON p.template_id = t.id
                WHERE p.id = ? AND p.user_id = ?
            ");
            $stmt->execute([$projectId, $user_id]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$project) {
                echo "<script>alert('Projeto não encontrado.');window.location='/projects';</script>";
                exit;
            }

            $projectName = $project['name'];
            $templateId = $project['template_id'] ?? $templateId;

            // Prioridade: HTML salvo > HTML do template
            if (!empty($project['html_content'])) {
                $templateHtml = $project['html_content'];
            } elseif (!empty($project['html_file'])) {
                $file = $_SERVER['DOCUMENT_ROOT'] . "/templates/{$project['html_file']}";
                if (file_exists($file)) {
                    $templateHtml = file_get_contents($file);
                }
            }
        }

        // ===== CASO 2: Novo projeto com template =====
        if (!$templateHtml && $templateId) {
            $stmt = $this->pdo->prepare("
                SELECT id, html_file, name, is_premium
                FROM templates_library
                WHERE id = ? AND status = 'active'
            ");
            $stmt->execute([$templateId]);
            $template = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($template) {
                // Verificar se é premium e se o usuário tem acesso
                if ($template['is_premium'] == 1 && !$canAccess) {
                    die("⚠ Você precisa de um plano Premium para usar este template.");
                }

                if (empty($projectName) || $projectName === "Novo Projeto") {
                    $projectName = $template['name'];
                }

                $file = __DIR__ . "/../../public/templates/{$template['html_file']}";
                if (file_exists($file)) {
                    $templateHtml = file_get_contents($file);
                }
            }
        }

        // ===== Fallback: Template vazio =====
        if (!$templateHtml) {
            $templateHtml = "<h1>Template vazio</h1><p>Selecione um template para começar.</p>";
        }

        include __DIR__ . '/../Views/editor/editor.php';
    }
}
