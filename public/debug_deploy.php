<?php
/**
 * Debug Deploy - Captura erros detalhados
 * 
 * Copie para: debug_deploy.php
 * Acesse: https://sitesdafabrica.test/debug_deploy.php?project_id=SEU_ID
 */

// Habilitar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir configurações
require_once __DIR__ . '/../config/database.php';

// Simular sessão (AJUSTE O USER_ID)
session_start();
$_SESSION['user_id'] = 1; // AJUSTE PARA SEU ID DE USUÁRIO

// Obter PDO
$pdo = require __DIR__ . '/../config/database.php';

// Incluir helper
require_once __DIR__ . '/../app/helpers/whm_deploy.php';

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <title>Debug Deploy</title>
    <style>
        body {
            font-family: monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
        }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .warning { color: #dcdcaa; }
        .info { color: #569cd6; }
        pre {
            background: #2d2d2d;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        h2 { color: #4ec9b0; }
        hr { border-color: #3e3e3e; }
    </style>
</head>
<body>";

echo "<h1>🔍 Debug Deploy System</h1>";
echo "<hr>";

// Verificar se project_id foi passado
$projectId = $_GET['project_id'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

if (!$projectId) {
    echo "<p class='error'>❌ Passe o ID do projeto na URL: ?project_id=SEU_ID</p>";
    echo "<p class='info'>Exemplo: debug_deploy.php?project_id=1</p>";
    exit;
}

if (!$userId) {
    echo "<p class='error'>❌ Sessão não encontrada. Ajuste o \$_SESSION['user_id'] no código.</p>";
    exit;
}

echo "<h2>📋 Informações</h2>";
echo "<pre>";
echo "Project ID: " . htmlspecialchars($projectId) . "\n";
echo "User ID: " . htmlspecialchars($userId) . "\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "</pre>";

// TESTE 1: Verificar conexão com banco
echo "<h2>1️⃣ Conexão com Banco</h2>";
try {
    $stmt = $pdo->query("SELECT 1");
    echo "<p class='success'>✅ Conexão OK</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro de conexão: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// TESTE 2: Verificar se projeto existe
echo "<h2>2️⃣ Verificar Projeto</h2>";
try {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND user_id = ?");
    $stmt->execute([$projectId, $userId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$project) {
        echo "<p class='error'>❌ Projeto não encontrado ou não pertence ao usuário</p>";
        exit;
    }
    
    echo "<p class='success'>✅ Projeto encontrado</p>";
    echo "<pre>";
    echo "Nome: " . htmlspecialchars($project['name']) . "\n";
    echo "Template ID: " . htmlspecialchars($project['template_id'] ?? 'null') . "\n";
    echo "Subdomain: " . htmlspecialchars($project['subdomain'] ?? 'null') . "\n";
    echo "Is Published: " . ($project['is_published'] ? 'Sim' : 'Não') . "\n";
    echo "Status: " . htmlspecialchars($project['status'] ?? 'null') . "\n";
    echo "HTML Content: " . (empty($project['html_content']) ? '❌ VAZIO' : '✅ ' . strlen($project['html_content']) . ' caracteres') . "\n";
    echo "</pre>";
    
    if (empty($project['html_content'])) {
        echo "<p class='error'>❌ ERRO: Projeto não tem conteúdo HTML!</p>";
        echo "<p class='warning'>⚠️ Edite o projeto no editor primeiro para adicionar conteúdo.</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro ao buscar projeto: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// TESTE 3: Verificar estrutura da tabela
echo "<h2>3️⃣ Estrutura da Tabela Projects</h2>";
try {
    // SQLite
    $stmt = $pdo->query("PRAGMA table_info(projects)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($columns)) {
        // MySQL
        $stmt = $pdo->query("DESCRIBE projects");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    $requiredColumns = ['subdomain', 'published_url', 'is_published', 'status'];
    $foundColumns = array_column($columns, 'name') ?: array_column($columns, 'Field');
    
    echo "<p class='info'>Colunas necessárias:</p>";
    echo "<pre>";
    foreach ($requiredColumns as $col) {
        $exists = in_array($col, $foundColumns);
        $status = $exists ? "✅" : "❌";
        echo "$status $col\n";
    }
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro ao verificar estrutura: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// TESTE 4: Tentar executar deployProject
echo "<h2>4️⃣ Executar Deploy</h2>";

if (empty($project['html_content'])) {
    echo "<p class='error'>❌ ABORTADO: Projeto sem conteúdo HTML</p>";
    echo "<p class='warning'>⚠️ Edite o projeto no editor antes de publicar</p>";
} else {
    echo "<p class='info'>🚀 Tentando publicar...</p>";
    
    try {
        // Chamar função de deploy
        $result = deployProject($pdo, $projectId, $userId);
        
        echo "<pre>";
        echo "Resultado:\n";
        print_r($result);
        echo "</pre>";
        
        if ($result['success']) {
            echo "<p class='success'>✅ Deploy executado com sucesso!</p>";
            if (isset($result['url'])) {
                echo "<p class='info'>🔗 URL: <a href='" . htmlspecialchars($result['url']) . "' target='_blank' style='color: #569cd6;'>" . htmlspecialchars($result['url']) . "</a></p>";
            }
        } else {
            echo "<p class='error'>❌ Deploy falhou: " . htmlspecialchars($result['message'] ?? 'Erro desconhecido') . "</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ EXCEÇÃO: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>";
        echo "Stack trace:\n";
        echo htmlspecialchars($e->getTraceAsString());
        echo "</pre>";
    }
}

// TESTE 5: Verificar diretório de sites
echo "<h2>5️⃣ Verificar Diretório de Sites</h2>";
$sitesDir = __DIR__ . '/public/sites';

if (is_dir($sitesDir)) {
    echo "<p class='success'>✅ Diretório existe: $sitesDir</p>";
    
    if (is_writable($sitesDir)) {
        echo "<p class='success'>✅ Diretório tem permissão de escrita</p>";
    } else {
        echo "<p class='error'>❌ Diretório SEM permissão de escrita</p>";
        echo "<p class='warning'>Execute: chmod 755 $sitesDir</p>";
    }
    
    // Listar sites existentes
    $sites = array_diff(scandir($sitesDir), ['.', '..']);
    if (!empty($sites)) {
        echo "<p class='info'>Sites publicados:</p>";
        echo "<pre>";
        foreach ($sites as $site) {
            echo "📁 $site\n";
        }
        echo "</pre>";
    }
} else {
    echo "<p class='error'>❌ Diretório NÃO existe: $sitesDir</p>";
    echo "<p class='warning'>Será criado automaticamente no primeiro deploy</p>";
}

// TESTE 6: Verificar configurações PHP
echo "<h2>6️⃣ Configurações PHP</h2>";
echo "<pre>";
echo "Error Reporting: " . (error_reporting() ? 'Ativo' : 'Inativo') . "\n";
echo "Display Errors: " . (ini_get('display_errors') ? 'Sim' : 'Não') . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . "s\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "</pre>";

echo "<hr>";
echo "<p class='info'>✅ Debug finalizado!</p>";
echo "</body></html>";