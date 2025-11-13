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
    * 📄 Listar projetos com informações de template
    * Usa COALESCE para evitar NULL quando template não existe
    */
    public function list()
    {
    $user_id = $_SESSION['user_id'] ?? 1;

    $stmt = $this->pdo->prepare("
    SELECT 
    p.id, 
    p.title, 
    p.template, 
    p.created_at, 
    p.updated_at,
    COALESCE(t.category, 'geral') as template_category,
    COALESCE(t.title, 'Sem Template') as template_title
    FROM projects p
    LEFT JOIN templates_library t ON p.template = t.name
    WHERE p.user_id = ? 
    ORDER BY p.updated_at DESC
    ");
    $stmt->execute([$user_id]);
    $projects = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    include __DIR__ . '/../Views/projects/list.php';
    }

    /**
    * 📋 Obter templates (JSON)
    */
    public function getTemplates()
    {
    try {
    // Buscar templates do banco de dados
    $stmt = $this->pdo->query("
    SELECT id, name, title, description, category, html_file, thumb_file as thumb
    FROM templates_library 
    WHERE status = 'active'
    ORDER BY order_position ASC
    ");
    $templates = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    if (!empty($templates)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $templates]);
    return;
    }

    // Fallback: templates da pasta public/templates
    $templatesDir = __DIR__ . '/../../public/templates';
    $htmlFiles = glob($templatesDir . '/*.html');
    
    $fallbackTemplates = [];
    foreach ($htmlFiles as $file) {
    $filename = basename($file, '.html');
    if ($filename !== '.gitkeep') {
    $fallbackTemplates[] = [
    'name' => $filename,
    'title' => ucfirst(str_replace('_', ' ', $filename)),
    'description' => 'Template ' . ucfirst($filename),
    'category' => 'geral',
    'thumb' => null
    ];
    }
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $fallbackTemplates]);

    } catch (\Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    }

    /**
    * 💾 Salvar ou atualizar projeto
    */
    public function save()
    {
    $user_id = $_SESSION['user_id'] ?? 1;

    $id    = trim($_POST['id'] ?? '');
    $title    = trim($_POST['title'] ?? '');
    $template    = trim($_POST['template'] ?? '');
    $content    = $_POST['content_html'] ?? '';
    $global_vars = $_POST['global_vars'] ?? '{}';
    $now    = date('Y-m-d H:i:s');
    
    // Se o título estiver vazio, usar 'Novo Projeto' como padrão
    if (empty($title)) {
        $title = 'Novo Projeto';
    }

    try {
    if ($id) {
    // Atualizar projeto existente
    $stmt = $this->pdo->prepare("
    UPDATE projects
    SET title = ?, content_html = ?, template = ?, global_vars = ?, updated_at = ?
    WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$title, $content, $template, $global_vars, $now, $id, $user_id]);
    } else {
    // Criar novo projeto
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
    * 📖 Obter projeto com dados do template (JSON)
    */
    public function get()
    {
    $user_id = $_SESSION['user_id'] ?? 1;
    $id = $_GET['id'] ?? null;

    if (!$id) {
    return $this->jsonError('ID ausente');
    }

    $stmt = $this->pdo->prepare("
    SELECT 
    p.*,
    COALESCE(t.title, 'Sem Template') as template_title,
    COALESCE(t.category, 'geral') as template_category,
    t.html_file
    FROM projects p
    LEFT JOIN templates_library t ON p.template = t.name
    WHERE p.id = ? AND p.user_id = ?
    ");
    $stmt->execute([$id, $user_id]);
    $project = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$project) {
    return $this->jsonError('Projeto não encontrado.');
    }

    $this->jsonSuccess($project);
    }

    /**
    * 🗑️ Deletar projeto
    */
    public function delete()
    {
    $user_id = $_SESSION['user_id'] ?? 1;
    $id = $_GET['id'] ?? null;

    if (!$id) {
    return $this->jsonError('ID não informado.');
    }

    $stmt = $this->pdo->prepare("DELETE FROM projects WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);

    if ($stmt->rowCount() === 0) {
    return $this->jsonError('Projeto não encontrado.');
    }

    $this->jsonSuccess('Projeto excluído com sucesso.');
    }

    // ==== Helpers ====
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