<?php
// app/helpers/whm_deploy.php

/**
 * ===================================================================
 *  HELPER DE DEPLOY - WHM/cPanel Integration
 * ===================================================================
 */

// ===== CONFIGURAÇÕES =====
define('WHM_HOST', getenv('WHM_HOST') ?: 'seu-servidor.com.br');
define('WHM_USER', getenv('WHM_USER') ?: 'root');
define('WHM_TOKEN', getenv('WHM_TOKEN') ?: 'SEU_TOKEN_WHM');
define('CPANEL_USER', getenv('CPANEL_USER') ?: 'sitesdafabrica');
define('MAIN_DOMAIN', getenv('MAIN_DOMAIN') ?: 'seusitesdafabrica.com.br');

/**
 * 🚀 PUBLICAR PROJETO
 */
function deployProject($pdo, $projectId, $userId) {
    
    // Buscar projeto
    $stmt = $pdo->prepare("
        SELECT p.*, u.email, u.name 
        FROM projects p 
        JOIN users u ON u.id = p.user_id 
        WHERE p.id = ? AND p.user_id = ?
    ");
    $stmt->execute([$projectId, $userId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$project) {
        return ['success' => false, 'message' => 'Projeto não encontrado'];
    }
    
    // Validar se já está publicado
    if ($project['is_published'] == 1) {
        return ['success' => false, 'message' => 'Projeto já está publicado'];
    }
    
    // Validar plano do usuário
    $canPublish = userCanPublish($pdo, $userId);
    if (!$canPublish['allowed']) {
        return ['success' => false, 'message' => $canPublish['message']];
    }
    
    // Gerar subdomínio único
    $subdomain = generateSubdomain($project['name'], $projectId);
    $fullDomain = $subdomain . '.' . MAIN_DOMAIN;
    
    // Criar subdomínio via WHM API
    $whmResult = createSubdomainWHM($subdomain, MAIN_DOMAIN);
    
    if (!$whmResult['success']) {
        return ['success' => false, 'message' => 'Erro ao criar subdomínio: ' . $whmResult['message']];
    }
    
    // Caminho onde o HTML será salvo
    $publicPath = $whmResult['document_root'];
    
    // Injetar Google Analytics antes de salvar (se configurado)
    $htmlContent = injectAnalytics($project['html_content'], $userId, $pdo);
    
    // Salvar HTML do projeto
    if (!file_exists($publicPath)) {
        mkdir($publicPath, 0755, true);
    }
    
    $indexPath = $publicPath . '/index.html';
    file_put_contents($indexPath, $htmlContent);
    
    // Criar arquivo .htaccess para cache
    createHtaccessCache($publicPath);
    
    // Atualizar projeto no banco
    $stmt = $pdo->prepare("
        UPDATE projects 
        SET 
            subdomain = ?,
            published_url = ?,
            is_published = 1,
            status = 'published',
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    $stmt->execute([$fullDomain, 'https://' . $fullDomain, $projectId]);
    
    // Forçar SSL via AutoSSL (assíncrono)
    requestAutoSSL($fullDomain);
    
    // Adicionar ao Cloudflare (se configurado)
    addToCloudflare($fullDomain);
    
    return [
        'success' => true,
        'url' => 'https://' . $fullDomain,
        'message' => 'Projeto publicado com sucesso!'
    ];
}

/**
 * 🗑️ DESPUBLICAR PROJETO
 */
function unpublishProject($pdo, $projectId, $userId) {
    
    // Buscar projeto
    $stmt = $pdo->prepare("
        SELECT p.* FROM projects p 
        WHERE p.id = ? AND p.user_id = ?
    ");
    $stmt->execute([$projectId, $userId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$project) {
        return ['success' => false, 'message' => 'Projeto não encontrado'];
    }
    
    if ($project['is_published'] != 1) {
        return ['success' => false, 'message' => 'Projeto não está publicado'];
    }
    
    // Extrair subdomínio
    $subdomain = str_replace('.' . MAIN_DOMAIN, '', $project['subdomain']);
    
    // Remover subdomínio do WHM
    $whmResult = removeSubdomainWHM($subdomain, MAIN_DOMAIN);
    
    if (!$whmResult['success']) {
        // Continua mesmo se falhar no WHM (para poder despublicar manualmente)
        error_log('Falha ao remover subdomínio WHM: ' . $whmResult['message']);
    }
    
    // Remover do Cloudflare
    removeFromCloudflare($project['subdomain']);
    
    // Remover domínios personalizados vinculados
    $stmt = $pdo->prepare("DELETE FROM user_domains WHERE project_id = ?");
    $stmt->execute([$projectId]);
    
    // Atualizar projeto no banco
    $stmt = $pdo->prepare("
        UPDATE projects 
        SET 
            subdomain = NULL,
            published_url = NULL,
            custom_domain = NULL,
            is_published = 0,
            status = 'draft',
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    $stmt->execute([$projectId]);
    
    return [
        'success' => true,
        'message' => 'Projeto despublicado com sucesso!'
    ];
}

/**
 * 🌐 ADICIONAR DOMÍNIO PERSONALIZADO
 */
function addCustomDomain($pdo, $projectId, $userId, $customDomain) {
    
    // Validar formato do domínio
    if (!filter_var($customDomain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
        return ['success' => false, 'message' => 'Domínio inválido'];
    }
    
    // Buscar projeto
    $stmt = $pdo->prepare("
        SELECT p.* FROM projects p 
        WHERE p.id = ? AND p.user_id = ?
    ");
    $stmt->execute([$projectId, $userId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$project) {
        return ['success' => false, 'message' => 'Projeto não encontrado'];
    }
    
    if ($project['is_published'] != 1) {
        return ['success' => false, 'message' => 'Publique o projeto antes de adicionar domínio personalizado'];
    }
    
    // Verificar limite de domínios do plano
    $canAddDomain = userCanAddDomain($pdo, $userId, $projectId);
    if (!$canAddDomain['allowed']) {
        return ['success' => false, 'message' => $canAddDomain['message']];
    }
    
    // Verificar se domínio já está em uso
    $stmt = $pdo->prepare("SELECT id FROM user_domains WHERE domain_name = ?");
    $stmt->execute([$customDomain]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Domínio já está em uso'];
    }
    
    // Adicionar domínio addon no cPanel
    $whmResult = addAddonDomain($customDomain, $project['subdomain']);
    
    if (!$whmResult['success']) {
        return ['success' => false, 'message' => 'Erro ao configurar domínio: ' . $whmResult['message']];
    }
    
    // Gerar registro CNAME para o usuário configurar no DNS
    $cnameRecord = $project['subdomain'];
    
    // Salvar no banco
    $stmt = $pdo->prepare("
        INSERT INTO user_domains 
        (user_id, project_id, domain_name, domain_type, dns_cname_record, status)
        VALUES (?, ?, ?, 'custom', ?, 'pending')
    ");
    $stmt->execute([$userId, $projectId, $customDomain, $cnameRecord]);
    $domainId = $pdo->lastInsertId();
    
    // Adicionar ao Cloudflare
    addToCloudflare($customDomain);
    
    return [
        'success' => true,
        'domain_id' => $domainId,
        'cname_record' => $cnameRecord,
        'message' => 'Domínio adicionado! Configure o CNAME no seu DNS.',
        'instructions' => "Adicione este registro no seu DNS:\nTipo: CNAME\nNome: @ (ou www)\nValor: {$cnameRecord}"
    ];
}

/**
 * 🗑️ REMOVER DOMÍNIO PERSONALIZADO
 */
function removeCustomDomain($pdo, $domainId, $userId) {
    
    $stmt = $pdo->prepare("
        SELECT d.*, p.subdomain 
        FROM user_domains d 
        JOIN projects p ON p.id = d.project_id 
        WHERE d.id = ? AND d.user_id = ?
    ");
    $stmt->execute([$domainId, $userId]);
    $domain = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$domain) {
        return ['success' => false, 'message' => 'Domínio não encontrado'];
    }
    
    // Remover do cPanel
    removeAddonDomain($domain['domain_name']);
    
    // Remover do Cloudflare
    removeFromCloudflare($domain['domain_name']);
    
    // Deletar do banco
    $stmt = $pdo->prepare("DELETE FROM user_domains WHERE id = ?");
    $stmt->execute([$domainId]);
    
    return [
        'success' => true,
        'message' => 'Domínio removido com sucesso!'
    ];
}

/**
 * ✅ VERIFICAR DNS DO DOMÍNIO
 */
function verifyDomainDNS($pdo, $domainId) {
    
    $stmt = $pdo->prepare("
        SELECT d.*, p.subdomain 
        FROM user_domains d 
        JOIN projects p ON p.id = d.project_id 
        WHERE d.id = ?
    ");
    $stmt->execute([$domainId]);
    $domain = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$domain) {
        return ['success' => false, 'message' => 'Domínio não encontrado'];
    }
    
    // Verificar CNAME
    $dnsRecords = dns_get_record($domain['domain_name'], DNS_CNAME);
    $verified = false;
    
    foreach ($dnsRecords as $record) {
        if (isset($record['target']) && strpos($record['target'], $domain['dns_cname_record']) !== false) {
            $verified = true;
            break;
        }
    }
    
    if ($verified) {
        // Atualizar status no banco
        $stmt = $pdo->prepare("
            UPDATE user_domains 
            SET dns_verified = 1, 
                dns_verified_at = CURRENT_TIMESTAMP, 
                status = 'active' 
            WHERE id = ?
        ");
        $stmt->execute([$domainId]);
        
        // Solicitar SSL
        requestAutoSSL($domain['domain_name']);
        
        return [
            'success' => true,
            'verified' => true,
            'message' => 'DNS verificado com sucesso! SSL sendo configurado...'
        ];
    }
    
    return [
        'success' => true,
        'verified' => false,
        'message' => 'DNS ainda não propagado. Aguarde alguns minutos e tente novamente.'
    ];
}

// ===== FUNÇÕES AUXILIARES WHM =====

function createSubdomainWHM($subdomain, $mainDomain) {
    
    $url = "https://" . WHM_HOST . ":2087/json-api/cpanel";
    
    $params = http_build_query([
        'cpanel_jsonapi_user' => CPANEL_USER,
        'cpanel_jsonapi_module' => 'SubDomain',
        'cpanel_jsonapi_func' => 'addsubdomain',
        'domain' => $subdomain,
        'rootdomain' => $mainDomain,
        'dir' => "/home/" . CPANEL_USER . "/public_html/sites/{$subdomain}"
    ]);
    
    $response = whmApiRequest($url . '?' . $params);
    
    if (!empty($response['cpanelresult']['data'][0]['result'])) {
        return [
            'success' => true,
            'document_root' => "/home/" . CPANEL_USER . "/public_html/sites/{$subdomain}"
        ];
    }
    
    return [
        'success' => false,
        'message' => $response['cpanelresult']['error'] ?? 'Erro desconhecido'
    ];
}

function removeSubdomainWHM($subdomain, $mainDomain) {
    
    $url = "https://" . WHM_HOST . ":2087/json-api/cpanel";
    
    $params = http_build_query([
        'cpanel_jsonapi_user' => CPANEL_USER,
        'cpanel_jsonapi_module' => 'SubDomain',
        'cpanel_jsonapi_func' => 'delsubdomain',
        'domain' => $subdomain . '.' . $mainDomain
    ]);
    
    $response = whmApiRequest($url . '?' . $params);
    
    if (!empty($response['cpanelresult']['data'][0]['result'])) {
        return ['success' => true];
    }
    
    return [
        'success' => false,
        'message' => $response['cpanelresult']['error'] ?? 'Erro desconhecido'
    ];
}

function addAddonDomain($customDomain, $targetSubdomain) {
    
    $url = "https://" . WHM_HOST . ":2087/json-api/cpanel";
    
    $params = http_build_query([
        'cpanel_jsonapi_user' => CPANEL_USER,
        'cpanel_jsonapi_module' => 'AddonDomain',
        'cpanel_jsonapi_func' => 'addaddondomain',
        'newdomain' => $customDomain,
        'subdomain' => str_replace('.' . MAIN_DOMAIN, '', $targetSubdomain),
        'dir' => "/home/" . CPANEL_USER . "/public_html/sites/" . str_replace('.' . MAIN_DOMAIN, '', $targetSubdomain)
    ]);
    
    $response = whmApiRequest($url . '?' . $params);
    
    if (!empty($response['cpanelresult']['data'][0]['result'])) {
        return ['success' => true];
    }
    
    return [
        'success' => false,
        'message' => $response['cpanelresult']['error'] ?? 'Erro ao adicionar domínio addon'
    ];
}

function removeAddonDomain($customDomain) {
    
    $url = "https://" . WHM_HOST . ":2087/json-api/cpanel";
    
    $params = http_build_query([
        'cpanel_jsonapi_user' => CPANEL_USER,
        'cpanel_jsonapi_module' => 'AddonDomain',
        'cpanel_jsonapi_func' => 'deladdondomain',
        'domain' => $customDomain
    ]);
    
    $response = whmApiRequest($url . '?' . $params);
    
    return ['success' => true]; // Não falha se não conseguir remover
}

function whmApiRequest($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: whm " . WHM_USER . ":" . WHM_TOKEN
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

function requestAutoSSL($domain) {
    // Executa em background
    $url = "https://" . WHM_HOST . ":2087/json-api/start_autossl_check?domain=" . urlencode($domain);
    exec("curl -k -H 'Authorization: whm " . WHM_USER . ":" . WHM_TOKEN . "' '{$url}' > /dev/null 2>&1 &");
}

// ===== VALIDAÇÕES =====

function userCanPublish($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as published 
        FROM projects 
        WHERE user_id = ? AND is_published = 1
    ");
    $stmt->execute([$userId]);
    $published = $stmt->fetch(PDO::FETCH_ASSOC)['published'];
    
    $stmt = $pdo->prepare("
        SELECT p.max_projects 
        FROM subscriptions s 
        JOIN plans p ON p.id = s.plan_id 
        WHERE s.user_id = ? AND s.status = 'active' 
        ORDER BY s.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $maxProjects = $plan['max_projects'] ?? 1;
    
    if ($published >= $maxProjects) {
        return [
            'allowed' => false,
            'message' => "Você atingiu o limite de {$maxProjects} projetos publicados. Faça upgrade do seu plano."
        ];
    }
    
    return ['allowed' => true];
}

function userCanAddDomain($pdo, $userId, $projectId) {
    
    // Contar domínios do usuário
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_domains 
        FROM user_domains 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $totalDomains = $stmt->fetch(PDO::FETCH_ASSOC)['total_domains'];
    
    // Contar domínios do projeto
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as project_domains 
        FROM user_domains 
        WHERE project_id = ?
    ");
    $stmt->execute([$projectId]);
    $projectDomains = $stmt->fetch(PDO::FETCH_ASSOC)['project_domains'];
    
    // Buscar limites do plano
    $stmt = $pdo->prepare("
        SELECT p.max_domains, p.max_domains_per_project 
        FROM subscriptions s 
        JOIN plans p ON p.id = s.plan_id 
        WHERE s.user_id = ? AND s.status = 'active' 
        ORDER BY s.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $maxDomains = $plan['max_domains'] ?? 1;
    $maxDomainsPerProject = $plan['max_domains_per_project'] ?? 1;
    
    // Validar limite geral
    if ($totalDomains >= $maxDomains) {
        return [
            'allowed' => false,
            'message' => "Você atingiu o limite de {$maxDomains} domínios. Faça upgrade do seu plano."
        ];
    }
    
    // Validar limite por projeto
    if ($maxDomainsPerProject && $projectDomains >= $maxDomainsPerProject) {
        return [
            'allowed' => false,
            'message' => "Este projeto atingiu o limite de {$maxDomainsPerProject} domínios."
        ];
    }
    
    return ['allowed' => true];
}

// ===== UTILITÁRIOS =====

function generateSubdomain($projectName, $projectId) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $projectName)));
    $slug = substr($slug, 0, 20);
    return $slug . '-' . $projectId;
}

function createHtaccessCache($publicPath) {
    $htaccess = <<<HTACCESS
# Cache Control
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType text/html "access plus 1 hour"
</IfModule>

# Compressão Gzip
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css application/javascript
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>
HTACCESS;
    
    file_put_contents($publicPath . '/.htaccess', $htaccess);
}