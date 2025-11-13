<?php
namespace App\Controllers;

class AdminController
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = require __DIR__ . '/../../config/database.php';
        $this->requireAdmin();
    }

    private function requireAdmin()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $stmt = $this->pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || $user['role'] !== 'admin') {
            http_response_code(403);
            echo "❌ Acesso negado. Apenas administradores.";
            exit;
        }
    }

    // ===== DASHBOARD =====
    public function dashboard()
    {
        $totalUsers = $this->pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $totalProjects = $this->pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
        $totalTemplates = $this->pdo->query("SELECT COUNT(*) FROM templates_library")->fetchColumn();
        $totalSubscriptions = $this->pdo->query("SELECT COUNT(*) FROM subscriptions WHERE status='active'")->fetchColumn();

        $revenue = $this->pdo->query("
            SELECT COALESCE(SUM(p.price), 0) as total
            FROM subscriptions s
            JOIN plans p ON s.plan_id = p.id
            WHERE s.status='active'
        ")->fetch(\PDO::FETCH_ASSOC)['total'];

        $recentUsers = $this->pdo->query("
            SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5
        ")->fetchAll(\PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/admin/dashboard.php';
    }

    // ===== TEMPLATES =====
    public function templates()
    {
        $templates = $this->pdo->query("
            SELECT * FROM templates_library ORDER BY order_position ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/admin/templates.php';
    }

    public function templateGet()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID ausente']);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM templates_library WHERE id = ?");
            $stmt->execute([$id]);
            $template = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$template) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Template não encontrado']);
                return;
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $template]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function templateSave()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? 'geral');
        $status = trim($_POST['status'] ?? 'active');

        if (!$name || !$title) {
            echo json_encode(['success' => false, 'message' => 'Nome e título são obrigatórios']);
            return;
        }

        // Validar arquivo HTML se for novo ou atualização
        $htmlFile = null;
        if (!empty($_FILES['html_file']['tmp_name'])) {
            if ($_FILES['html_file']['type'] !== 'text/html' && $_FILES['html_file']['type'] !== 'text/plain') {
                echo json_encode(['success' => false, 'message' => 'Arquivo deve ser HTML']);
                return;
            }
            $htmlFile = 'template_' . time() . '_' . bin2hex(random_bytes(4)) . '.html';
            move_uploaded_file($_FILES['html_file']['tmp_name'], __DIR__ . '/../../public/templates/' . $htmlFile);
        }

        // Thumbnail
        $thumbFile = null;
        if (!empty($_FILES['thumb_file']['tmp_name'])) {
            $thumbFile = 'thumb_' . time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
            move_uploaded_file($_FILES['thumb_file']['tmp_name'], __DIR__ . '/../../public/templates/thumbs/' . $thumbFile);
        }

        try {
            if ($id) {
                $updates = ["status = ?", "updated_at = CURRENT_TIMESTAMP"];
                $params = [$status];

                // Adicionar campos opcionais ao UPDATE
                $fieldsToUpdate = [
                    'name' => $name,
                    'title' => $title,
                    'description' => $description,
                    'category' => $category,
                    'html_file' => $htmlFile,
                    'thumb_file' => $thumbFile,
                ];

                foreach ($fieldsToUpdate as $field => $value) {
                    if ($value) {
                        array_unshift($updates, "$field = ?");
                        array_unshift($params, $value);
                    }
                }

                $params[] = $id;
                $stmt = $this->pdo->prepare("UPDATE templates_library SET " . implode(", ", $updates) . " WHERE id = ?");
                $stmt->execute($params);
            } else {
                if (!$htmlFile) {
                    echo json_encode(['success' => false, 'message' => 'Template HTML é obrigatório']);
                    return;
                }

                $stmt = $this->pdo->prepare("
                    INSERT INTO templates_library (name, title, description, category, html_file, thumb_file, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $title, $description, $category, $htmlFile, $thumbFile, $status]);
                $id = $this->pdo->lastInsertId();
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'id' => $id, 'message' => 'Template salvo!']);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function templateDelete()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID ausente']);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("DELETE FROM templates_library WHERE id = ?");
            $stmt->execute([$id]);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Template removido']);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ===== USUÁRIOS =====
    public function users()
    {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $total = $this->pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $users = $this->pdo->query("
            SELECT id, name, email, role, created_at FROM users
            ORDER BY created_at DESC LIMIT $perPage OFFSET $offset
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $totalPages = ceil($total / $perPage);

        include __DIR__ . '/../Views/admin/users.php';
    }

    public function userGet()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID ausente']);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
                return;
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $user]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function userSave()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'user';

        if (!$id || !$name || !$email) {
            echo json_encode(['success' => false, 'message' => 'ID, nome e e-mail são obrigatórios']);
            return;
        }

        if (!in_array($role, ['user', 'admin'])) {
            echo json_encode(['success' => false, 'message' => 'Função inválida']);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
            $stmt->execute([$name, $email, $role, $id]);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Usuário atualizado!']);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function userChangeRole()
    {
        $userId = $_POST['user_id'] ?? null;
        $role = $_POST['role'] ?? 'user';

        if (!in_array($role, ['user', 'admin'])) {
            echo json_encode(['success' => false]);
            return;
        }

        $stmt = $this->pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $userId]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }

    public function userDelete()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false]);
            return;
        }

        // Não deletar a si mesmo
        if ($id == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Não pode deletar a si mesmo']);
            return;
        }

        try {
            $this->pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ===== PROJETOS =====
    public function projects()
    {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $total = $this->pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
        $projects = $this->pdo->query("
            SELECT p.*, u.name as user_name, u.email
            FROM projects p
            JOIN users u ON p.user_id = u.id
            ORDER BY p.updated_at DESC LIMIT $perPage OFFSET $offset
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $totalPages = ceil($total / $perPage);

        include __DIR__ . '/../Views/admin/projects.php';
    }

    public function projectDelete()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false]);
            return;
        }

        try {
            $this->pdo->prepare("DELETE FROM projects WHERE id = ?")->execute([$id]);
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ===== PLANOS =====
    public function plans()
    {
        $plans = $this->pdo->query("
            SELECT * FROM plans ORDER BY price ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/admin/plans.php';
    }

    public function planGet()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID ausente']);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM plans WHERE id = ?");
            $stmt->execute([$id]);
            $plan = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$plan) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Plano não encontrado']);
                return;
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $plan]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function planSave()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false]);
            return;
        }

        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $maxProjects = (int)($_POST['max_projects'] ?? 5);
        $maxStorage = (int)($_POST['max_storage_mb'] ?? 100);
        $status = $_POST['status'] ?? 'active';

        if (!$name) {
            echo json_encode(['success' => false, 'message' => 'Nome obrigatório']);
            return;
        }

        try {
            if ($id) {
                $stmt = $this->pdo->prepare("
                    UPDATE plans
                    SET name = ?, description = ?, price = ?, max_projects = ?, 
                        max_storage_mb = ?, status = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");
                $stmt->execute([$name, $description, $price, $maxProjects, $maxStorage, $status, $id]);
            } else {
                $stmt = $this->pdo->prepare("
                    INSERT INTO plans (name, description, price, max_projects, max_storage_mb, status)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $description, $price, $maxProjects, $maxStorage, $status]);
                $id = $this->pdo->lastInsertId();
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'id' => $id]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function planDelete()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false]);
            return;
        }

        try {
            $this->pdo->prepare("DELETE FROM plans WHERE id = ?")->execute([$id]);
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ===== ASSINATURAS =====
    public function subscriptions()
    {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $total = $this->pdo->query("SELECT COUNT(*) FROM subscriptions")->fetchColumn();
        $subscriptions = $this->pdo->query("
            SELECT s.*, u.name as user_name, u.email, p.name as plan_name, p.price
            FROM subscriptions s
            JOIN users u ON s.user_id = u.id
            JOIN plans p ON s.plan_id = p.id
            ORDER BY s.created_at DESC LIMIT $perPage OFFSET $offset
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $totalPages = ceil($total / $perPage);

        include __DIR__ . '/../Views/admin/subscriptions.php';
    }

    public function subscriptionCancel()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false]);
            return;
        }

        try {
            $this->pdo->prepare("
                UPDATE subscriptions
                SET status = 'canceled', canceled_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ")->execute([$id]);

            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}