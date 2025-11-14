<?php
// app/helpers/cloudflare_cdn.php

/**
 * ===================================================================
 *  HELPER CLOUDFLARE - CDN Integration
 * ===================================================================
 */

define('CF_ENABLED', getenv('CLOUDFLARE_ENABLED') === 'true');
define('CF_API_KEY', getenv('CLOUDFLARE_API_KEY') ?: '');
define('CF_ZONE_ID', getenv('CLOUDFLARE_ZONE_ID') ?: '');
define('CF_EMAIL', getenv('CLOUDFLARE_EMAIL') ?: '');

function addToCloudflare($domain) {
    
    if (!CF_ENABLED || !CF_API_KEY || !CF_ZONE_ID) {
        return ['success' => false, 'message' => 'Cloudflare não configurado'];
    }
    
    // Extrair domínio raiz e subdomínio
    $parts = explode('.', $domain);
    
    if (count($parts) < 2) {
        return ['success' => false, 'message' => 'Domínio inválido'];
    }
    
    $name = $domain;
    $rootDomain = $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
    
    // Adicionar registro DNS no Cloudflare
    $url = "https://api.cloudflare.com/client/v4/zones/" . CF_ZONE_ID . "/dns_records";
    
    $data = [
        'type' => 'CNAME',
        'name' => $name,
        'content' => $rootDomain,
        'ttl' => 1, // Auto
        'proxied' => true // CDN ativado
    ];
    
    $response = cloudflareApiRequest($url, 'POST', $data);
    
    if ($response['success']) {
        return ['success' => true, 'message' => 'Domínio adicionado ao Cloudflare CDN'];
    }
    
    return [
        'success' => false,
        'message' => $response['errors'][0]['message'] ?? 'Erro ao adicionar ao Cloudflare'
    ];
}

function removeFromCloudflare($domain) {
    
    if (!CF_ENABLED || !CF_API_KEY || !CF_ZONE_ID) {
        return ['success' => false];
    }
    
    // Buscar ID do registro DNS
    $url = "https://api.cloudflare.com/client/v4/zones/" . CF_ZONE_ID . "/dns_records?name=" . urlencode($domain);
    $response = cloudflareApiRequest($url, 'GET');
    
    if (!empty($response['result'][0]['id'])) {
        $recordId = $response['result'][0]['id'];
        
        // Deletar registro
        $deleteUrl = "https://api.cloudflare.com/client/v4/zones/" . CF_ZONE_ID . "/dns_records/{$recordId}";
        cloudflareApiRequest($deleteUrl, 'DELETE');
    }
    
    return ['success' => true];
}

function purgeCloudflareCache($domain) {
    
    if (!CF_ENABLED) {
        return ['success' => false];
    }
    
    $url = "https://api.cloudflare.com/client/v4/zones/" . CF_ZONE_ID . "/purge_cache";
    
    $data = [
        'hosts' => [$domain]
    ];
    
    $response = cloudflareApiRequest($url, 'POST', $data);
    
    return ['success' => $response['success'] ?? false];
}

function cloudflareApiRequest($url, $method = 'GET', $data = null) {
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-Auth-Email: ' . CF_EMAIL,
        'X-Auth-Key: ' . CF_API_KEY,
        'Content-Type: application/json'
    ]);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}