<?php
// ===== MODIFICAÇÕES NO ProjectController.php =====
// Adicione/Atualize estes métodos no seu controller

namespace App\Controllers;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;


class ProjectController
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = require __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../helpers/subscription.php';
        $this->requireAuth();
    }

    private function requireAuth()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * MÉTODO MELHORADO: save
     * - Aceita template_id e sincroniza com o banco
     * - Salva HTML content do projeto
     * - Mantém o template_id para recarregar depois
     */
public function save()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false]);
        return;
    }
    header('Content-Type: application/json');

    // 1. Validação do Token CSRF
    $clientToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $clientToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Sessão inválida ou expirada (CSRF). Recarregue a página.']);
        return;
    }
    $userId = $_SESSION['user_id'];
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? 'Sem Título');
    $html = $_POST['html'] ?? '';
    $templateId = $_POST['template_id'] ?? null;
    $loadTemplateContent = $_POST['load_template_content'] ?? false;

    if (!empty($html)) {
        $htmlAttributes = '';
        $headBlock = '';
        $bodyAttributes = '';
        $bodyContent = $html;
        $isFullDocument = false;

        // Check if there is a <body> tag to determine if it is a full HTML document
        if (preg_match('/<body[^>]*>/i', $html)) {
            $isFullDocument = true;

            // 1. Extract <html> attributes
            if (preg_match('/<html([^>]*)>/i', $html, $matches)) {
                $htmlAttributes = $matches[1];
            }

            // 2. Extract <head> block
            if (preg_match('/(<head[^>]*>.*?<\/head>)/is', $html, $matches)) {
                $headBlock = $matches[1];
            }

            // 3. Extract <body> attributes
            if (preg_match('/<body([^>]*)>/i', $html, $matches)) {
                $bodyAttributes = $matches[1];
            }

            // 4. Extract <body> inner content
            if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
                $bodyContent = $matches[1];
            }
        }

        // Extrair e preservar as tags <style> antes da sanitização (dentro do conteúdo do body)
        $styleTags = [];
        $bodyContent = preg_replace_callback('/(<style[^>]*>.*?<\/style>)/is', function ($matches) use (&$styleTags) {
            $placeholder = '<!-- STYLE_PLACEHOLDER_' . count($styleTags) . ' -->';
            $styleTags[] = $matches[1];
            return $placeholder;
        }, $bodyContent);

        $config = (new HtmlSanitizerConfig())
            ->allowSafeElements() // Permite elementos básicos de texto seguros
            // Permite as tags estruturais com seus atributos essenciais para o Tailwind/Construtor
            ->allowElement('div', ['class', 'id', 'style', 'data-edit'])
            ->allowElement('span', ['class', 'id', 'style', 'data-edit'])
            ->allowElement('section', ['class', 'id', 'style', 'data-edit'])
            ->allowElement('header', ['class', 'id', 'style', 'data-edit'])
            ->allowElement('footer', ['class', 'id', 'style', 'data-edit'])
            ->allowElement('nav', ['class', 'id', 'style', 'data-edit'])
            ->allowElement('button', ['class', 'type', 'style', 'data-edit'])
            ->allowElement('img', ['src', 'alt', 'class', 'style', 'data-edit'])
            ->allowElement('a', ['href', 'target', 'class', 'style', 'data-edit'])
            ->allowElement('style', ['type']) // Adiciona a permissão da tag <style> no sanitizador
            // Permite SVGs (comum em templates e ícones)
            ->allowElement('svg', ['class', 'viewBox', 'fill', 'xmlns'])
            ->allowElement('path', ['d', 'fill', 'stroke'])
            // Libera os atributos class, style e data-edit globalmente para as tags permitidas acima
            ->allowAttribute('class', '*')
            ->allowAttribute('style', '*')
            ->allowAttribute('data-edit', '*');

        $sanitizer = new HtmlSanitizer($config);
        $bodyContent = $sanitizer->sanitize($bodyContent);

        // Reinserir as tags <style> originais de volta ao HTML sanitizado
        foreach ($styleTags as $index => $originalStyle) {
            $placeholder = '<!-- STYLE_PLACEHOLDER_' . $index . ' -->';
            $bodyContent = str_replace($placeholder, $originalStyle, $bodyContent);
        }

        // Reconstruir o HTML completo
        if ($isFullDocument) {
            $html = "<!DOCTYPE html>\n<html" . $htmlAttributes . ">\n" . $headBlock . "\n<body" . $bodyAttributes . ">\n" . $bodyContent . "\n</body>\n</html>";
        } else {
            $html = $bodyContent;
        }
    }

    // Novos campos SEO
    $seoTitle = trim($_POST['seo_title'] ?? '');
    $seoDescription = trim($_POST['seo_description'] ?? '');
    $seoImage = trim($_POST['seo_image'] ?? '');

    if (!$name) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Nome é obrigatório']);
        return;
    }

    try {
        if ($id) {
            // UPDATE
            $stmt = $this->pdo->prepare("SELECT user_id FROM projects WHERE id = ?");
            $stmt->execute([$id]);
            $project = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$project || $project['user_id'] != $userId) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Projeto não encontrado']);
                return;
            }

            $fields = ['name = ?', 'updated_at = CURRENT_TIMESTAMP'];
            $values = [$name];

            if ($html !== '') {
                $fields[] = 'html_content = ?';
                $values[] = $html;
            }

            if ($templateId !== null && $templateId !== '') {
                $fields[] = 'template_id = ?';
                $values[] = $templateId;
            }
            // Injetar campos de SEO no update
            $fields[] = 'seo_title = ?';
            $values[] = $seoTitle;
            $fields[] = 'seo_description = ?';
            $values[] = $seoDescription;
            $fields[] = 'seo_image = ?';
            $values[] = $seoImage;

            $values[] = $id;
            $sql = "UPDATE projects SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($values);

        } else {
            // INSERT NOVO PROJETO
            // INSERT NOVO PROJETO - VERIFICAÇÃO DE QUOTA
            
            // 1. Descobrir qual o limite do utilizador (via subscrição ativa ou plano gratuito)
            $stmt = $this->pdo->prepare("
                SELECT p.max_projects 
                FROM subscriptions s
                JOIN plans p ON s.plan_id = p.id
                WHERE s.user_id = ? AND s.status = 'active'
                ORDER BY s.created_at DESC LIMIT 1
            ");
            $stmt->execute([$userId]);
            $plan = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Se não tiver subscrição, assumimos o limite do plano Gratuito (ex: 3 projetos)
            $maxProjects = $plan ? $plan['max_projects'] : 3; 

            // 2. Contar quantos projetos ele já tem
            $stmt = $this->pdo->prepare("SELECT COUNT(id) as total FROM projects WHERE user_id = ?");
            $stmt->execute([$userId]);
            $currentProjects = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            if ($currentProjects >= $maxProjects) {
                http_response_code(403);
                echo json_encode([
                    'success' => false, 
                    'message' => "Atingiu o limite de {$maxProjects} projetos do seu plano. Faça upgrade para criar mais."
                ]);
                return;
            }
            
            // ===== CARREGAR HTML DO TEMPLATE =====
            if ($loadTemplateContent && $templateId && empty($html)) {
                $stmt = $this->pdo->prepare("
                    SELECT html_file 
                    FROM templates_library 
                    WHERE id = ? AND status = 'active'
                ");
                $stmt->execute([$templateId]);
                $template = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if ($template && !empty($template['html_file'])) {
                    $templatePath = __DIR__ . '/../../public/templates/' . $template['html_file'];
                    if (file_exists($templatePath)) {
                        $html = file_get_contents($templatePath);
                    }
                }
            }
            
            $stmt = $this->pdo->prepare("
                INSERT INTO projects (user_id, name, html_content, template_id)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $name, $html, $templateId]);
            $id = $this->pdo->lastInsertId();
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'project_id' => $id, 'message' => 'Projeto salvo com sucesso']);

    } catch (\Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
    /**
     * NOVO MÉTODO: getTemplates
     * Retorna todos os templates disponíveis para o seletor
     */
    public function getTemplates()
    {
        try {
            $stmt = $this->pdo->query("
                SELECT id, name, title, description, thumb_file, category
                FROM templates_library
                WHERE status = 'active'
                ORDER BY order_position ASC
            ");
            $templates = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'templates' => $templates]);

        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * MÉTODO EXISTENTE MELHORADO: list
     * Mostrar projetos com nomes de templates
     */
    public function list()
    {
        $userId = $_SESSION['user_id'];

        $stmt = $this->pdo->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stmt = $this->pdo->prepare("
            SELECT s.*, p.name, p.price, p.max_projects, p.max_storage_mb, p.description, p.can_access_premium
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

        // Melhorado: trazer nome do template
        $stmt = $this->pdo->prepare("
            SELECT 
                p.id,
                p.name,
                p.template_id,
                p.is_published,
                p.published_url,
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

    /**
     * MÉTODO EXISTENTE: get
     * Buscar projeto individual
     */
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
    /**
     * MÉTODO EXISTENTE: delete
     */
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

    // === OUTROS MÉTODOS EXISTENTES (sem alterações) ===

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
                SELECT *
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

    /**
     * 👁️ Visualizar (Preview) o site estático completo com URLs absolutas
     */
    public function preview()
    {
        $userId = $_SESSION['user_id'];
        $id = $_GET['id'] ?? null;

        if (!$id) {
            die('ID do projeto ausente');
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT id, name, html_content 
                FROM projects 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$id, $userId]);
            $project = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$project) {
                die('Projeto não encontrado');
            }

            require_once __DIR__ . '/../helpers/whm_deploy.php';
            $html = buildStaticHtml($project);

            header('Content-Type: text/html; charset=utf-8');
            echo $html;
            exit;

        } catch (\Exception $e) {
            die('Erro ao gerar preview: ' . $e->getMessage());
        }
    }

    /**
     * ⬇️ Baixar (Download) o site empacotado como um ZIP com URLs absolutas
     */
    public function download()
    {
        $userId = $_SESSION['user_id'];
        $id = $_GET['id'] ?? null;

        if (!$id) {
            die('ID do projeto ausente');
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT id, name, html_content 
                FROM projects 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$id, $userId]);
            $project = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$project) {
                die('Projeto não encontrado');
            }

            require_once __DIR__ . '/../helpers/whm_deploy.php';
            $html = buildStaticHtml($project);

            $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $project['name']);
            if (empty($filename)) {
                $filename = 'projeto_' . $id;
            }

            // Registrar download para cotas
            try {
                $stmtLog = $this->pdo->prepare("
                    INSERT INTO downloads_log (user_id, project_id, created_at)
                    VALUES (?, ?, CURRENT_TIMESTAMP)
                ");
                $stmtLog->execute([$userId, $id]);
            } catch (\Exception $e) {
                // Ignorar erro do log de download para não travar a exportação
            }

            if (class_exists('\ZipArchive')) {
                $zip = new \ZipArchive();
                $zipFile = tempnam(sys_get_temp_dir(), 'zip');
                if ($zip->open($zipFile, \ZipArchive::CREATE) === TRUE) {
                    $zip->addFromString('index.html', $html);
                    $zip->close();

                    // Limpa qualquer buffer de saída ativo para evitar corrupção de binário
                    while (ob_get_level()) {
                        ob_end_clean();
                    }

                    header('Content-Type: application/zip');
                    header('Content-Disposition: attachment; filename="' . $filename . '.zip"');
                    header('Content-Length: ' . filesize($zipFile));
                    header('Pragma: no-cache');
                    header('Expires: 0');
                    
                    readfile($zipFile);
                    unlink($zipFile);
                    exit;
                }
            }

            // Fallback: download como arquivo HTML direto se ZipArchive não estiver disponível
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.html"');
            echo $html;
            exit;

        } catch (\Exception $e) {
            die('Erro ao gerar download: ' . $e->getMessage());
        }
    }
}