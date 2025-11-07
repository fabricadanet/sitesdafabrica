<?php
namespace App\Controllers;


class ProjectController
{
    private $pdo;


    public function __construct()
    {
        $this->pdo = require __DIR__ . '/../../config/database.php';
    }

    /**
     * 📄 Listar projetos (View ou JSON)
     */
    public function list()
    {
        $user_id = $_SESSION['user_id'] ?? 1;

        $stmt = $this->pdo->prepare("SELECT id, title, template, created_at, updated_at FROM projects WHERE user_id = ? ORDER BY updated_at DESC");
        $stmt->execute([$user_id]);
        $projects = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        include __DIR__ . '/../views/projects/list.php';
    }

    /**
     * 💾 Salvar ou atualizar projeto
     */

 public function save()
    {
        
        $user_id = $_SESSION['user_id'] ?? 1;

        $id          = trim($_POST['id'] ?? '');
        $title       = trim($_POST['title'] ?? 'Projeto sem título');
        $template    = trim($_POST['template'] ?? '');
        $content     = $_POST['content_html'] ?? '';
        $global_vars = $_POST['global_vars'] ?? '{}';
        $now         = date('Y-m-d H:i:s');

        try {
            if ($id) {
                $stmt = $this->pdo->prepare("
                    UPDATE projects
                    SET title = ?, content_html = ?, template = ?, global_vars = ?, updated_at = ?
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$title, $content, $template, $global_vars, $now, $id, $user_id]);
            } else {
                $stmt = $this->pdo->prepare("
                    INSERT INTO projects (user_id, title, content_html, template, global_vars, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$user_id, $title, $content, $template, $global_vars, $now, $now]);
                $id = $this->pdo->lastInsertId();
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'id' => $id]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }


    /**
     * 🔁 Obter projeto (JSON)
     */
    public function get()
    {
       
        $user_id = $_SESSION['user_id'] ?? 1;

        $id = $_GET['id'] ?? null;
           if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID ausente']);
        return;
    }

        $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        $project = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$project) return $this->jsonError('Projeto não encontrado.');
        $this->jsonSuccess($project);
    }

    /**
     * 🗑️ Deletar projeto
     */
    public function delete()
    {
       
        $user_id = $_SESSION['user_id'] ?? 1;

        $id = $_GET['id'] ?? null;
        if (!$id) return $this->jsonError('ID não informado.');

        $stmt = $this->pdo->prepare("DELETE FROM projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);

        if ($stmt->rowCount() === 0)
            return $this->jsonError('Projeto não encontrado.');

        $this->jsonSuccess('Projeto excluído com sucesso.');
    }

    // ====== Helpers ======
    private function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    private function jsonSuccess($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'data' => $data]);
    }

    private function jsonError($message)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $message]);
    }
}


