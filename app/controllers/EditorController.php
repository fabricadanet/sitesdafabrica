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
       

        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo "ID do projeto não informado.";
            return;
        }

        $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        $project = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$project) {
            echo "Projeto não encontrado.";
            return;
        }

        // 🔹 Nova lógica: carregar variáveis globais separadas (se existirem)
        $vars = [];
        if (!empty($project['global_vars'])) {
            $vars = json_decode($project['global_vars'], true);
        }

        include __DIR__ . '/../views/editor/editor.php';
    }
}

