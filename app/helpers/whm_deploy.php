<?php
// app/helpers/whm_deploy.php
// Helper para publicação de projetos via WHM/cPanel API

/**
 * Publicar projeto
 * 
 * @param PDO $pdo
 * @param int $projectId
 * @param int $userId
 * @return array
 */
function deployProject($pdo, $projectId, $userId)
{
    try {
        // Buscar projeto
        $stmt = $pdo->prepare("
            SELECT id, name, html_content, subdomain, is_published 
            FROM projects 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$projectId, $userId]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$project) {
            return [
                'success' => false,
                'message' => 'Projeto não encontrado'
            ];
        }

        // Se já está publicado
        if ($project['is_published'] == 1 && !empty($project['subdomain'])) {
            return [
                'success' => true,
                'message' => 'Projeto já está publicado',
                'url' => 'https://' . $project['subdomain'] . '.sitesdafabrica.com.br'
            ];
        }

        // Gerar subdomínio se não existir
        $subdomain = $project['subdomain'];
        if (empty($subdomain)) {
            $subdomain = generateSubdomain($project['name'], $projectId);
        }

        // Verificar se HTML existe
        if (empty($project['html_content'])) {
            return [
                'success' => false,
                'message' => 'Projeto sem conteúdo HTML. Edite o projeto primeiro.'
            ];
        }

        // Empacota o conteúdo num esqueleto HTML5 completo
        $fullStaticHtml = buildStaticHtml($project);

        // Criar subdomínio no servidor (WHM/cPanel)
        $createResult = createSubdomain($subdomain, $fullStaticHtml);
        
        if (!$createResult['success']) {
            return [
                'success' => false,
                'message' => 'Erro ao criar subdomínio: ' . ($createResult['message'] ?? 'Erro desconhecido')
            ];
        }

        // Atualizar banco de dados
        $publishedUrl = 'https://' . $subdomain . '.sitesdafabrica.com.br';
        
        $stmt = $pdo->prepare("
            UPDATE projects 
            SET 
                subdomain = ?,
                is_published = 1,
                published_url = ?,
                status = 'published',
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$subdomain, $publishedUrl, $projectId, $userId]);

        // Configurar CDN Cloudflare (opcional)
        if (function_exists('setupCloudflare')) {
            setupCloudflare($subdomain);
        }

        return [
            'success' => true,
            'message' => 'Projeto publicado com sucesso!',
            'url' => $publishedUrl,
            'subdomain' => $subdomain
        ];

    } catch (Exception $e) {
        error_log("Erro em deployProject: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Erro ao publicar projeto: ' . $e->getMessage()
        ];
    }
}

/**
 * Constrói a estrutura HTML5 completa para o site estático
 * * @param array $project
 * @return string
 */
function buildStaticHtml($project)
{
    $seoTitle = $project['seo_title'] ?? null;
    $seoDescription = $project['seo_description'] ?? null;
    $seoImage = $project['seo_image'] ?? null;
    $projectName = $project['name'] ?? 'Meu Site';

    $title = htmlspecialchars($seoTitle ?: $projectName, ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars($seoDescription ?: 'Site gerado pela plataforma Sites da Fábrica', ENT_QUOTES, 'UTF-8');
    $content = $project['html_content'] ?? '';

    // 1. Identifica a URL principal do SaaS para buscar as imagens de forma absoluta
    $appUrl = getenv('APP_URL') ?: ($_ENV['APP_URL'] ?? '');
    
    if (empty($appUrl)) {
        // Fallback automático inteligente caso a variável de ambiente não esteja definida
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $appUrl = $protocol . $host;
    }
    // Remove qualquer barra final para evitar caminhos duplicados (ex: //uploads)
    $appUrl = rtrim($appUrl, '/');

    // 🔥 CORREÇÃO CRÍTICA: Transforma caminhos relativos de imagens em URLs absolutas fixas
    // Funciona para tags <img> e propriedades CSS url() comuns em builders visuais
    $content = str_replace('src="/uploads/', 'src="' . $appUrl . '/uploads/', $content);
    $content = str_replace("src='/uploads/", "src='" . $appUrl . "/uploads/", $content);
    $content = str_replace('url("/uploads/', 'url("' . $appUrl . '/uploads/', $content);
    $content = str_replace("url('/uploads/", "url('" . $appUrl . "/uploads/", $content);
    $content = str_replace('url(&quot;/uploads/', 'url(&quot;' . $appUrl . '/uploads/', $content);
    $content = str_replace('url(&#039;/uploads/', 'url(&#039;' . $appUrl . '/uploads/', $content);
    // ✅ Substitui qualquer imagem de capa ou thumbnail do template pela imagem definida no SEO
    if (!empty($seoImage)) {
        $content = preg_replace('/<img[^>]*src="[^"\\]*\/template_assets\/[^"\\]*"[^>]*>/i', '', $content);
    }
    
    $ogImageTag = '';
    if (!empty($seoImage)) {
        $imageUrl = htmlspecialchars($seoImage, ENT_QUOTES, 'UTF-8');
        // Garantir URL absoluta para o WhatsApp/Facebook conseguir ler a imagem
        if (strpos($imageUrl, 'http') !== 0) {
            $imageUrl = $appUrl . $imageUrl;
        }
        $ogImageTag = "<meta property=\"og:image\" content=\"{$imageUrl}\">\n    <meta name=\"twitter:image\" content=\"{$imageUrl}\">";
    }
    // Monta a estrutura HTML final
    $html = <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <meta name="description" content="{$description}">
    {$ogImageTag}
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-white text-gray-900 font-sans">
    
    {$content}

</body>
</html>
HTML;

    return $html;
}

/**
 * Despublicar projeto
 * 
 * @param PDO $pdo
 * @param int $projectId
 * @param int $userId
 * @return array
 */
function unpublishProject($pdo, $projectId, $userId)
{
    try {
        // Buscar projeto
        $stmt = $pdo->prepare("
            SELECT id, subdomain 
            FROM projects 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$projectId, $userId]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$project) {
            return [
                'success' => false,
                'message' => 'Projeto não encontrado'
            ];
        }

        // Remover subdomínio do servidor
        if (!empty($project['subdomain'])) {
            $deleteResult = deleteSubdomain($project['subdomain']);
            
            if (!$deleteResult['success']) {
                // Log do erro mas continua (falha silenciosa)
                error_log("Erro ao deletar subdomínio: " . ($deleteResult['message'] ?? 'Desconhecido'));
            }
        }

        // Atualizar banco de dados
        $stmt = $pdo->prepare("
            UPDATE projects 
            SET 
                is_published = 0,
                published_url = NULL,
                status = 'draft',
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$projectId, $userId]);

        return [
            'success' => true,
            'message' => 'Projeto despublicado com sucesso!'
        ];

    } catch (Exception $e) {
        error_log("Erro em unpublishProject: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Erro ao despublicar: ' . $e->getMessage()
        ];
    }
}

/**
 * Gerar nome de subdomínio único
 * 
 * @param string $projectName
 * @param int $projectId
 * @return string
 */
function generateSubdomain($projectName, $projectId)
{
    // Sanitizar nome do projeto
    $subdomain = strtolower($projectName);
    $subdomain = preg_replace('/[^a-z0-9\-]/', '', $subdomain);
    $subdomain = preg_replace('/-+/', '-', $subdomain);
    $subdomain = trim($subdomain, '-');
    
    // Limitar tamanho
    if (strlen($subdomain) > 20) {
        $subdomain = substr($subdomain, 0, 20);
    }
    
    // Se ficou vazio, usar default
    if (empty($subdomain)) {
        $subdomain = 'site';
    }
    
    // Adicionar ID para garantir unicidade
    $subdomain .= '-' . $projectId;
    
    return $subdomain;
}

/**
 * Criar subdomínio no servidor WHM/cPanel
 * 
 * @param string $subdomain
 * @param string $htmlContent
 * @return array
 */
function createSubdomain($subdomain, $htmlContent)
{
    try {
        // Verificar se as credenciais WHM estão configuradas
        $whmHost = getenv('WHM_HOST') ?: ($_ENV['WHM_HOST'] ?? null);
        $whmUser = getenv('WHM_USER') ?: ($_ENV['WHM_USER'] ?? null);
        $whmToken = getenv('WHM_TOKEN') ?: ($_ENV['WHM_TOKEN'] ?? null);

        // Se não tiver credenciais WHM, usar modo de simulação (desenvolvimento)
        if (empty($whmHost) || empty($whmUser) || empty($whmToken)) {
            error_log("WHM não configurado - Modo simulação ativado");
            return simulateSubdomainCreation($subdomain, $htmlContent);
        }

        // Criar subdomínio via API WHM
        $domain = $subdomain . '.sitesdafabrica.com.br';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://{$whmHost}:2087/json-api/cpanel");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: whm {$whmUser}:{$whmToken}"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'cpanel_jsonapi_module' => 'SubDomain',
            'cpanel_jsonapi_func' => 'addsubdomain',
            'domain' => $subdomain,
            'rootdomain' => 'sitesdafabrica.com.br',
            'dir' => "/public_html/sites/{$subdomain}"
        ]));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("Erro na API WHM: HTTP {$httpCode}");
        }

        $data = json_decode($response, true);
        
        if (!isset($data['cpanelresult']['data'][0]['result']) || $data['cpanelresult']['data'][0]['result'] != 1) {
            $reason = $data['cpanelresult']['data'][0]['reason'] ?? 'Erro desconhecido';
            throw new Exception($reason);
        }

        // Criar arquivo index.html no diretório
        $docRoot = "/home/fabricadanet/public_html/sites/{$subdomain}";
        if (!is_dir($docRoot)) {
            mkdir($docRoot, 0755, true);
        }
        file_put_contents($docRoot . '/index.html', $htmlContent);

        return [
            'success' => true,
            'message' => 'Subdomínio criado com sucesso',
            'url' => "https://{$domain}"
        ];

    } catch (Exception $e) {
        error_log("Erro em createSubdomain: " . $e->getMessage());
        
        // Fallback: tentar modo simulação
        return simulateSubdomainCreation($subdomain, $htmlContent);
    }
}

/**
 * Remover subdomínio do servidor WHM/cPanel
 * 
 * @param string $subdomain
 * @return array
 */
function deleteSubdomain($subdomain)
{
    try {
        $whmHost = getenv('WHM_HOST') ?: ($_ENV['WHM_HOST'] ?? null);
        $whmUser = getenv('WHM_USER') ?: ($_ENV['WHM_USER'] ?? null);
        $whmToken = getenv('WHM_TOKEN') ?: ($_ENV['WHM_TOKEN'] ?? null);

        // Modo simulação
        if (empty($whmHost) || empty($whmUser) || empty($whmToken)) {
            error_log("WHM não configurado - Simulando remoção de subdomínio");
            return simulateSubdomainDeletion($subdomain);
        }

        // Deletar via API WHM
        $domain = $subdomain . '.sitesdafabrica.com.br';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://{$whmHost}:2087/json-api/cpanel");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: whm {$whmUser}:{$whmToken}"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'cpanel_jsonapi_module' => 'SubDomain',
            'cpanel_jsonapi_func' => 'delsubdomain',
            'domain' => $domain
        ]));

        $response = curl_exec($ch);
        curl_close($ch);

        // Remover diretório
        $docRoot = "/home/fabricadanet/public_html/sites/{$subdomain}";
        if (is_dir($docRoot)) {
            removeDirectory($docRoot);
        }

        return [
            'success' => true,
            'message' => 'Subdomínio removido com sucesso'
        ];

    } catch (Exception $e) {
        error_log("Erro em deleteSubdomain: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Modo simulação: criar subdomínio localmente (para desenvolvimento)
 * 
 * @param string $subdomain
 * @param string $htmlContent
 * @return array
 */
function simulateSubdomainCreation($subdomain, $htmlContent)
{
    try {
        // Criar diretório local para simular publicação
        // __DIR__ = app/helpers, então subimos 2 níveis para chegar na raiz
        $projectRoot = dirname(dirname(__DIR__));
        $docRoot = $projectRoot . '/public/sites/' . $subdomain;
        
        if (!is_dir($docRoot)) {
            mkdir($docRoot, 0755, true);
        }
        
        file_put_contents($docRoot . '/index.html', $htmlContent);
        
        error_log("Subdomínio simulado criado: {$subdomain} em {$docRoot}");
        
        return [
            'success' => true,
            'message' => 'Projeto publicado em modo local',
            'url' => "/sites/{$subdomain}/index.html",
            'mode' => 'simulation'
        ];
        
    } catch (Exception $e) {
        error_log("Erro na simulação: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Erro ao criar diretório local: ' . $e->getMessage()
        ];
    }
}

/**
 * Modo simulação: remover subdomínio local
 * 
 * @param string $subdomain
 * @return array
 */
function simulateSubdomainDeletion($subdomain)
{
    try {
        $projectRoot = dirname(dirname(__DIR__));
        $docRoot = $projectRoot . '/public/sites/' . $subdomain;
        
        if (is_dir($docRoot)) {
            removeDirectory($docRoot);
        }
        
        error_log("Subdomínio simulado removido: {$subdomain}");
        
        return [
            'success' => true,
            'message' => 'Projeto despublicado (modo local)'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Remover diretório recursivamente
 * 
 * @param string $dir
 * @return void
 */
function removeDirectory($dir)
{
    if (!is_dir($dir)) {
        return;
    }
    
    $files = array_diff(scandir($dir), ['.', '..']);
    
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? removeDirectory($path) : unlink($path);
    }
    
    rmdir($dir);
}

/**
 * Adicionar domínio personalizado
 * 
 * @param PDO $pdo
 * @param int $projectId
 * @param int $userId
 * @param string $customDomain
 * @return array
 */
function addCustomDomain($pdo, $projectId, $userId, $customDomain)
{
    try {
        // Validar domínio
        $customDomain = strtolower(trim($customDomain));
        $customDomain = preg_replace('/^https?:\/\//', '', $customDomain);
        $customDomain = preg_replace('/\/.*$/', '', $customDomain);
        
        if (!filter_var('http://' . $customDomain, FILTER_VALIDATE_URL)) {
            return [
                'success' => false,
                'message' => 'Domínio inválido'
            ];
        }

        // Verificar se projeto existe
        $stmt = $pdo->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$projectId, $userId]);
        if (!$stmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Projeto não encontrado'
            ];
        }

        // Verificar se domínio já existe
        $stmt = $pdo->prepare("SELECT id FROM user_domains WHERE domain_name = ?");
        $stmt->execute([$customDomain]);
        if ($stmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Este domínio já está cadastrado'
            ];
        }

        // Inserir domínio
        $stmt = $pdo->prepare("
            INSERT INTO user_domains (user_id, project_id, domain_name, dns_verified)
            VALUES (?, ?, ?, 0)
        ");
        $stmt->execute([$userId, $projectId, $customDomain]);

        return [
            'success' => true,
            'message' => 'Domínio adicionado! Configure o DNS conforme as instruções.',
            'instructions' => "Aponte o registro A do domínio {$customDomain} para o IP: 192.168.1.100 (substitua pelo IP real do servidor)"
        ];

    } catch (Exception $e) {
        error_log("Erro em addCustomDomain: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Erro ao adicionar domínio: ' . $e->getMessage()
        ];
    }
}

/**
 * Remover domínio personalizado
 * 
 * @param PDO $pdo
 * @param int $domainId
 * @param int $userId
 * @return array
 */
function removeCustomDomain($pdo, $domainId, $userId)
{
    try {
        // Verificar propriedade
        $stmt = $pdo->prepare("SELECT id FROM user_domains WHERE id = ? AND user_id = ?");
        $stmt->execute([$domainId, $userId]);
        if (!$stmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Domínio não encontrado'
            ];
        }

        // Remover
        $stmt = $pdo->prepare("DELETE FROM user_domains WHERE id = ? AND user_id = ?");
        $stmt->execute([$domainId, $userId]);

        return [
            'success' => true,
            'message' => 'Domínio removido com sucesso'
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Erro ao remover domínio: ' . $e->getMessage()
        ];
    }
}

/**
 * Verificar DNS do domínio
 * 
 * @param PDO $pdo
 * @param int $domainId
 * @return array
 */
function verifyDomainDNS($pdo, $domainId)
{
    try {
        $stmt = $pdo->prepare("SELECT domain_name FROM user_domains WHERE id = ?");
        $stmt->execute([$domainId]);
        $domain = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$domain) {
            return [
                'success' => false,
                'message' => 'Domínio não encontrado'
            ];
        }

        // Verificar DNS (simplificado)
        $ip = gethostbyname($domain['domain_name']);
        
        if ($ip === $domain['domain_name']) {
            return [
                'success' => false,
                'message' => 'DNS ainda não propagado. Aguarde até 48h.'
            ];
        }

        // Atualizar como verificado
        $stmt = $pdo->prepare("UPDATE user_domains SET dns_verified = 1 WHERE id = ?");
        $stmt->execute([$domainId]);

        return [
            'success' => true,
            'message' => 'DNS verificado com sucesso!'
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Erro ao verificar DNS: ' . $e->getMessage()
        ];
    }
}