<?php
// app/helpers/analytics_injector.php
// Helper para injetar Google Analytics nos projetos

/**
 * Salvar ID do Google Analytics do usuário
 * 
 * @param PDO $pdo
 * @param int $userId
 * @param string $gaId
 * @return array
 */
function saveUserAnalyticsId($pdo, $userId, $gaId)
{
    try {
        // Validar formato do GA ID
        if (!preg_match('/^(G-|UA-|GT-)[A-Z0-9\-]+$/i', $gaId)) {
            return [
                'success' => false,
                'message' => 'ID do Google Analytics inválido. Use o formato G-XXXXXXXXXX ou UA-XXXXXXXXX'
            ];
        }

        // Atualizar ou criar registro
        $stmt = $pdo->prepare("
            UPDATE users 
            SET ga_tracking_id = ? 
            WHERE id = ?
        ");
        $stmt->execute([$gaId, $userId]);

        return [
            'success' => true,
            'message' => 'Google Analytics configurado! Será aplicado em todos os seus projetos publicados.'
        ];

    } catch (Exception $e) {
        error_log("Erro em saveUserAnalyticsId: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Erro ao salvar: ' . $e->getMessage()
        ];
    }
}

/**
 * Injetar código do Google Analytics no HTML
 * 
 * @param string $html
 * @param string $gaId
 * @return string
 */
function injectGoogleAnalytics($html, $gaId)
{
    if (empty($gaId)) {
        return $html;
    }

    // Código do Google Analytics (GA4)
    $analyticsCode = "
    <!-- Google Analytics -->
    <script async src=\"https://www.googletagmanager.com/gtag/js?id={$gaId}\"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{$gaId}');
    </script>
    <!-- End Google Analytics -->
    ";

    // Tentar injetar antes do </head>
    if (stripos($html, '</head>') !== false) {
        $html = str_ireplace('</head>', $analyticsCode . '</head>', $html);
    } 
    // Se não tiver </head>, tentar antes do </body>
    elseif (stripos($html, '</body>') !== false) {
        $html = str_ireplace('</body>', $analyticsCode . '</body>', $html);
    } 
    // Se não tiver nenhum, adicionar no início
    else {
        $html = $analyticsCode . $html;
    }

    return $html;
}

/**
 * Obter ID do Google Analytics do usuário
 * 
 * @param PDO $pdo
 * @param int $userId
 * @return string|null
 */
function getUserAnalyticsId($pdo, $userId)
{
    try {
        $stmt = $pdo->prepare("SELECT ga_tracking_id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['ga_tracking_id'] ?? null;
    } catch (Exception $e) {
        error_log("Erro em getUserAnalyticsId: " . $e->getMessage());
        return null;
    }
}