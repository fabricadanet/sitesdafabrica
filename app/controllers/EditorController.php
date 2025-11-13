<?php
namespace App\Controllers;

class EditorController {
    private $pdo;

    public function __construct() {
        $this->pdo = require __DIR__ . '/../../config/database.php';
    }

    public function index() {
     
        $user_id = $_SESSION['user_id'] ?? 1;

        $id = $_GET['id'] ?? null;
        $template = $_GET['template'] ?? null;
        $project = null;

        if ($id) {
            $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            $project = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$project) {
                echo "<script>alert('Projeto não encontrado.');window.location='/projects';</script>";
                return;
            }
        }

        // se não há HTML salvo, carrega o template padrão
        if ($project && empty($project['content_html']) && $template) {
            $tplPath = __DIR__ . "/../../templates/{$template}.html";
            if (file_exists($tplPath)) {
                $project['content_html'] = file_get_contents($tplPath);
            }
        }

        include __DIR__ . '/../Views/editor/editor.php';
    }
}