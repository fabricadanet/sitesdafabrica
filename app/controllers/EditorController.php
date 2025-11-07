<?php
// app/controllers/EditorController.php
namespace App\Controllers;
use \PDO;

class EditorController {
    private $pdo;

    public function __construct() {
        $this->pdo = require __DIR__ . '/../../config/database.php';
    }

    public function index() {
       
        $user_id = $_SESSION['user_id'] ?? 1;
        $id = $_GET['id'] ?? null;
        if ($id) {
         $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        $project = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$project) {
            echo "Projeto não encontrado.";
            return;
        }
        }else{
            // listar projetos pelo id do usuário
            $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $projects = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }



        // 🔹 Nova lógica: carregar variáveis globais separadas (se existirem)
        $vars = [];
        if (!empty($project['global_vars'])) {
            $vars = json_decode($project['global_vars'], true);
        }

        include __DIR__ . '/../views/editor/editor.php';
    }
}

