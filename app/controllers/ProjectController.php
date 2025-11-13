<?php
// ===== MODIFICAÇÕES NO ProjectController.php =====
// Adicione/Atualize estes métodos no seu controller

namespace App\Controllers;

class ProjectController
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = require __DIR__ . '/../../config/database.php';
        $this->requireAuth();
    }

    private function requireAuth()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }

    // ===== MÉTODO MODIFICADO: save =====
    // Agora aceita template_id além de name e html
    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false]);
            return;
        }

        $userId = $_SESSION['user_id'];
        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $html = $_POST['html'] ?? '';
        $templateId = $_POST['template_id'] ?? null;

        if (!$name) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Nome é obrigatório']);
            return;
        }

        try {
            if ($id) {
                // UPDATE: projeto já existe, atualizar
                $stmt = $this->pdo->prepare("SELECT user_id FROM projects WHERE id = ?");
                $stmt->execute([$id]);
                $project = $stmt->fetch(\PDO::FETCH_ASSOC);

                if (!$project || $project['user_id'] != $userId) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Projeto não encontrado']);
                    return;
                }

                // Atualizar com os campos fornecidos
                $fields = ['name = ?', 'updated_at = CURRENT_TIMESTAMP'];
                $values = [$name];

                if ($html !== '') {
                    $fields[] = 'html_content = ?';
                    $values[] = $html;
                }

                if ($templateId !== null) {
                    $fields[] = 'template_id = ?';
                    $values[] = $templateId;
                }

                $values[] = $id;

                $sql = "UPDATE projects SET " . implode(', ', $fields) . " WHERE id = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($values);
            } else {
                // INSERT: novo projeto
                if ($templateId) {
                    $stmt = $this->pdo->prepare("
                        INSERT INTO projects (user_id, name, html_content, template_id)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$userId, $name, $html, $templateId]);
                } else {
                    $stmt = $this->pdo->prepare("
                        INSERT INTO projects (user_id, name, html_content)
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$userId, $name, $html]);
                }
                $id = $this->pdo->lastInsertId();
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'project_id' => $id]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ===== NOVO MÉTODO: getTemplates =====
    // Retorna todos os templates disponíveis
    public function getTemplates()
    {
        try {
            $templates = $this->pdo->query("
                SELECT id, name, title, description, html_file, thumb_file, category
                FROM templates_library
                WHERE status = 'active'
                ORDER BY order_position ASC
            ")->fetchAll(\PDO::FETCH_ASSOC);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'templates' => $templates]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ===== MÉTODO EXISTENTE - SEM MUDANÇA =====
    public function list()
    {
        $userId = $_SESSION['user_id'];

        $stmt = $this->pdo->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stmt = $this->pdo->prepare("
            SELECT s.*, p.name, p.price, p.max_projects, p.max_storage_mb, p.description
            FROM subscriptions s
            JOIN plans p ON s.plan_id = p.id
            WHERE s.user_id = ? AND s.status = 'active'
            ORDER BY s.created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $subscriptionData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$subscriptionData) {
            $stmt = $this->pdo->prepare("SELECT * FROM plans WHERE name = 'Gratuito' OR price = 0 LIMIT 1");
            $stmt->execute();
            $planData = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$planData) {
                $planData = [
                    'id' => 0,
                    'name' => 'Gratuito',
                    'price' => 0,
                    'max_projects' => 3,
                    'max_storage_mb' => 100,
                    'description' => 'Plano gratuito'
                ];
            }

            $subscriptionData = [
                'id' => 0,
                'renews_at' => date('Y-m-d', strtotime('+30 days')),
                'status' => 'active'
            ];
        } else {
            $planData = $subscriptionData;
        }

        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM projects WHERE user_id = ?");
        $stmt->execute([$userId]);
        $totalProjects = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

        $stmt = $this->pdo->prepare("
            SELECT 
                p.id,
                p.name,
                p.template_id,
                t.name as template_name,
                p.created_at,
                p.updated_at
            FROM projects p
            LEFT JOIN templates_library t ON p.template_id = t.id
            WHERE p.user_id = ?
            ORDER BY p.updated_at DESC
            LIMIT 50
        ");
        $stmt->execute([$userId]);
        $projects = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/projects/list.php';
    }

    // ===== OUTROS MÉTODOS EXISTENTES =====
    // (userData, updateProfile, plansList, upgradePlan, get, delete - sem mudanças)

    public function userData()
    {
        $userId = $_SESSION['user_id'];

        $stmt = $this->pdo->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'user' => $user]);
    }

    public function updateProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $name = trim($_POST['name'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$name) {
            echo json_encode(['success' => false, 'message' => 'Nome é obrigatório']);
            return;
        }

        try {
            if ($password) {
                if (strlen($password) < 8) {
                    echo json_encode(['success' => false, 'message' => 'Senha deve ter no mínimo 8 caracteres']);
                    return;
                }

                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $this->pdo->prepare("UPDATE users SET name = ?, password = ? WHERE id = ?");
                $stmt->execute([$name, $passwordHash, $userId]);
            } else {
                $stmt = $this->pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
                $stmt->execute([$name, $userId]);
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Perfil atualizado!']);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function plansList()
    {
        try {
            $plans = $this->pdo->query("
                SELECT id, name, price, max_projects, max_storage_mb, description
                FROM plans
                WHERE status = 'active'
                ORDER BY price ASC
            ")->fetchAll(\PDO::FETCH_ASSOC);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'plans' => $plans]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function upgradePlan()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $planId = $_POST['plan_id'] ?? null;

        if (!$planId) {
            echo json_encode(['success' => false, 'message' => 'Plano não especificado']);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("SELECT id FROM plans WHERE id = ?");
            $stmt->execute([$planId]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Plano não encontrado']);
                return;
            }

            $this->pdo->prepare("
                UPDATE subscriptions 
                SET status = 'canceled', canceled_at = CURRENT_TIMESTAMP 
                WHERE user_id = ? AND status = 'active'
            ")->execute([$userId]);

            $startedAt = date('Y-m-d');
            $renewsAt = date('Y-m-d', strtotime('+30 days'));

            $stmt = $this->pdo->prepare("
                INSERT INTO subscriptions (user_id, plan_id, status, started_at, renews_at, payment_method)
                VALUES (?, ?, 'active', ?, ?, 'online')
            ");
            $stmt->execute([$userId, $planId, $startedAt, $renewsAt]);
            $subscriptionId = $this->pdo->lastInsertId();

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'subscription_id' => $subscriptionId,
                'message' => 'Plano atualizado com sucesso!'
            ]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function get()
    {
        $userId = $_SESSION['user_id'];
        $id = $_GET['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID ausente']);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT id, name, html_content, template_id
                FROM projects
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$id, $userId]);
            $project = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$project) {
                echo json_encode(['success' => false, 'message' => 'Projeto não encontrado']);
                return;
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $project]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete()
    {
        $userId = $_SESSION['user_id'];
        $id = $_GET['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false]);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("SELECT user_id FROM projects WHERE id = ?");
            $stmt->execute([$id]);
            $project = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$project || $project['user_id'] != $userId) {
                echo json_encode(['success' => false, 'message' => 'Projeto não encontrado']);
                return;
            }

            $this->pdo->prepare("DELETE FROM projects WHERE id = ? AND user_id = ?")->execute([$id, $userId]);

            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}