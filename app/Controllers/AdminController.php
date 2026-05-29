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
            // 1. Receita Mensal Recorrente (MRR)
            $stmt = $this->pdo->query("
                SELECT COALESCE(SUM(p.price), 0) as mrr 
                FROM subscriptions s 
                JOIN plans p ON s.plan_id = p.id 
                WHERE s.status = 'active'
            ");
            $mrr = $stmt->fetch(\PDO::FETCH_ASSOC)['mrr'];

            // 2. Total de Utilizadores
            $stmt = $this->pdo->query("SELECT COUNT(id) as total FROM users WHERE role != 'admin'");
            $totalUsers = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // 3. Taxa de Ativação (Sites Publicados)
            $stmt = $this->pdo->query("SELECT COUNT(id) as total FROM projects WHERE is_published = 1");
            $publishedProjects = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // 4. Últimos Projetos (Para monitorizar a atividade recente)
            $stmt = $this->pdo->query("
                SELECT p.name, p.status, p.created_at, u.name as user_name 
                FROM projects p 
                JOIN users u ON p.user_id = u.id 
                ORDER BY p.updated_at DESC 
                LIMIT 5
            ");
            $recentProjects = $stmt->fetchAll(\PDO::FETCH_ASSOC);

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
            echo json_encode(['success' => false]);
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
        $maxDownloads = (int)($_POST['max_downloads'] ?? 1000);
        $maxDomains = (int)($_POST['max_domains'] ?? 1);
        $maxSubdomains = (int)($_POST['max_subdomains'] ?? 3);
        $maxDomainsPerProject = (!empty($_POST['max_domains_per_project']) ? (int)$_POST['max_domains_per_project'] : null);
        $isFeatured = (int)($_POST['is_featured'] ?? 0);
        $isVisible = (int)($_POST['is_visible'] ?? 1);
        $displayOrder = (int)($_POST['display_order'] ?? 0);
        $status = $_POST['status'] ?? 'active';

        if (!$name) {
            echo json_encode(['success' => false, 'message' => 'Nome obrigatório']);
            return;
        }

        try {
            if ($id) {
                // UPDATE existente
                $stmt = $this->pdo->prepare("
                UPDATE plans
                SET name = ?, 
                    description = ?, 
                    price = ?, 
                    max_projects = ?, 
                    max_storage_mb = ?,
                    max_downloads = ?,
                    max_domains = ?,
                    max_subdomains = ?,
                    max_domains_per_project = ?,
                    is_featured = ?,
                    is_visible = ?,
                    display_order = ?,
                    status = ?, 
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
                $stmt->execute([
                    $name, $description, $price, $maxProjects, $maxStorage,
                    $maxDownloads, $maxDomains, $maxSubdomains, $maxDomainsPerProject,
                    $isFeatured, $isVisible, $displayOrder, $status, $id
                ]);
            } else {
                // INSERT novo
                $stmt = $this->pdo->prepare("
                INSERT INTO plans 
                (name, description, price, max_projects, max_storage_mb, 
                 max_downloads, max_domains, max_subdomains, max_domains_per_project,
                 is_featured, is_visible, display_order, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
                $stmt->execute([
                    $name, $description, $price, $maxProjects, $maxStorage,
                    $maxDownloads, $maxDomains, $maxSubdomains, $maxDomainsPerProject,
                    $isFeatured, $isVisible, $displayOrder, $status
                ]);
                $id = $this->pdo->lastInsertId();
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'id' => $id, 'message' => 'Plano salvo com sucesso!']);
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

    // ===== NOVO MÉTODO: RETORNAR PLANOS EM JSON =====
    public function plansList()
    {
        try {
            $plans = $this->pdo->query("
                SELECT id, name, price, max_projects, max_storage_mb, description, status
                FROM plans
                WHERE status = 'active'
                ORDER BY price ASC
            ")->fetchAll(\PDO::FETCH_ASSOC);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $plans]);
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

    // ===== CRIAR ASSINATURA (ADMIN) =====
    public function subscriptionCreate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        $userId = $_POST['user_id'] ?? null;
        $planId = $_POST['plan_id'] ?? null;
        $startedAt = $_POST['started_at'] ?? date('Y-m-d');
        $renewsAt = $_POST['renews_at'] ?? date('Y-m-d', strtotime('+30 days'));
        $paymentMethod = $_POST['payment_method'] ?? 'admin_manual';
        $status = $_POST['status'] ?? 'active';

        // Validações
        if (!$userId || !$planId) {
            echo json_encode(['success' => false, 'message' => 'Usuário e plano são obrigatórios']);
            return;
        }

        if (!in_array($status, ['active', 'inactive', 'canceled'])) {
            echo json_encode(['success' => false, 'message' => 'Status inválido']);
            return;
        }

        try {
            // Verificar se usuário existe
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
                return;
            }

            // Verificar se plano existe
            $stmt = $this->pdo->prepare("SELECT id FROM plans WHERE id = ?");
            $stmt->execute([$planId]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Plano não encontrado']);
                return;
            }

            // Validar datas
            $startDate = \DateTime::createFromFormat('Y-m-d', $startedAt);
            $renewDate = \DateTime::createFromFormat('Y-m-d', $renewsAt);

            if (!$startDate || !$renewDate) {
                echo json_encode(['success' => false, 'message' => 'Formato de data inválido (use YYYY-MM-DD)']);
                return;
            }

            if ($renewDate < $startDate) {
                echo json_encode(['success' => false, 'message' => 'Data de renovação não pode ser anterior à data de início']);
                return;
            }

            // Criar assinatura
            $stmt = $this->pdo->prepare("
            INSERT INTO subscriptions 
            (user_id, plan_id, status, started_at, renews_at, payment_method, created_at)
            VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ");

            $stmt->execute([
                $userId,
                $planId,
                $status,
                $startedAt,
                $renewsAt,
                $paymentMethod
            ]);

            $subscriptionId = $this->pdo->lastInsertId();

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'subscription_id' => $subscriptionId,
                'message' => 'Assinatura criada com sucesso!'
            ]);

        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

// ===== LISTAR PLANOS EM JSON (para select no admin) =====
    public function plansListJson()
    {
        try {
            $stmt = $this->pdo->query("
            SELECT id, name, price, max_projects, max_storage_mb, status
            FROM plans
            WHERE status = 'active'
            ORDER BY price ASC
        ");
            $plans = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $plans]);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    /**
     * Entra na conta de um utilizador específico
     */
    public function impersonate()
    {
        $targetUserId = $_GET['id'] ?? null;
        
        if (!$targetUserId) {
            die("ID não fornecido.");
        }

        // Guarda o ID do Admin atual numa variável de sessão especial
        $_SESSION['admin_id'] = $_SESSION['user_id'];
        
        // Troca o ID da sessão para o ID do cliente
        $_SESSION['user_id'] = $targetUserId;
        
        // Redireciona para o painel do cliente
        header('Location: /projects');
        exit;
    }

    /**
     * Devolve a sessão ao Admin original
     */
    public function stopImpersonating()
    {
        if (isset($_SESSION['admin_id'])) {
            $_SESSION['user_id'] = $_SESSION['admin_id'];
            unset($_SESSION['admin_id']);
        }
        
        header('Location: /admin/users');
        exit;
    }

    public function templateUploadZip()
    {
        if (!isset($_FILES['zip_file']) || $_FILES['zip_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Erro no upload do ficheiro.']);
            return;
        }

        $templateName = trim($_POST['name'] ?? 'Novo Template');
        $category = trim($_POST['category'] ?? 'Geral');
        $file = $_FILES['zip_file']['tmp_name'];

        $zip = new \ZipArchive();
        if ($zip->open($file) === true) {
            // Gerar nome único para o template
            $hash = bin2hex(random_bytes(4));
            $folderName = 'tpl_' . time() . '_' . $hash;
            $extractPath = sys_get_temp_dir() . '/' . $folderName;
            
            $zip->extractTo($extractPath);
            $zip->close();

            // Procurar pelo index.html
            $indexPath = $extractPath . '/index.html';
            if (!file_exists($indexPath)) {
                echo json_encode(['success' => false, 'message' => 'O ZIP deve conter um ficheiro index.html na raiz.']);
                return;
            }

            // Ler conteúdo do HTML
            $htmlContent = file_get_contents($indexPath);

            // Mover pasta de assets se existir (ex: imagens, css do template)
            $publicAssetsPath = __DIR__ . '/../../public/templates/assets/' . $folderName;
            if (is_dir($extractPath . '/assets')) {
                mkdir($publicAssetsPath, 0755, true);
                // Copiar ficheiros de assets (simplificado)
                $assets = glob($extractPath . '/assets/*');
                foreach ($assets as $asset) {
                    if (is_file($asset)) {
                        copy($asset, $publicAssetsPath . '/' . basename($asset));
                        // Atualizar caminhos no HTML
                        $htmlContent = str_replace('assets/' . basename($asset), '/templates/assets/' . $folderName . '/' . basename($asset), $htmlContent);
                    }
                }
            }

            // Guardar o ficheiro HTML final na pasta de templates
            $htmlFileName = $folderName . '.html';
            file_put_contents(__DIR__ . '/../../public/templates/' . $htmlFileName, $htmlContent);

            // Inserir na base de dados
            $stmt = $this->pdo->prepare("
                INSERT INTO templates_library (name, title, description, category, html_file, status, order_position) 
                VALUES (?, ?, ?, ?, ?, 'active', 0)
            ");
            $stmt->execute([
                strtolower(str_replace(' ', '_', $templateName)),
                $templateName,
                'Template importado automaticamente',
                $category,
                $htmlFileName
            ]);

            // Limpeza
            $this->deleteDir($extractPath);

            echo json_encode(['success' => true, 'message' => 'Template instalado com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Não foi possível ler o ficheiro ZIP.']);
        }
    }

    private function deleteDir($dirPath) {
        if (!is_dir($dirPath)) return;
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            is_dir($file) ? $this->deleteDir($file) : unlink($file);
        }
        rmdir($dirPath);
    }
}