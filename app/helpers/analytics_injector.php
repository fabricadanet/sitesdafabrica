<?php
// app/helpers/analytics_injector.php

/**
 * ===================================================================
 *  HELPER ANALYTICS - Google Analytics Auto Inject
 * ===================================================================
 */

function injectAnalytics($html, $userId, $pdo) {
    
    // Buscar Google Analytics ID do usuário (se configurado)
    $stmt = $pdo->prepare("
        SELECT ga_tracking_id 
        FROM user_settings 
        WHERE user_id = ? AND ga_tracking_id IS NOT NULL
    ");
    $stmt->execute([$userId]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $gaId = $settings['ga_tracking_id'] ?? getenv('DEFAULT_GA_ID');
    
    if (!$gaId) {
        return $html; // Sem analytics configurado
    }
    
    // Script Google Analytics 4 (GA4)
    $analyticsScript = <<<ANALYTICS

<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$gaId}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{$gaId}');
</script>
<!-- End Google Analytics -->

ANALYTICS;
    
    // Injetar antes do </head>
    $html = str_ireplace('</head>', $analyticsScript . '</head>', $html);
    
    return $html;
}

function saveUserAnalyticsId($pdo, $userId, $gaTrackingId) {
    
    // Validar formato GA4 (G-XXXXXXXXXX)
    if (!preg_match('/^G-[A-Z0-9]{10}$/', $gaTrackingId)) {
        return ['success' => false, 'message' => 'ID do Google Analytics inválido (formato: G-XXXXXXXXXX)'];
    }
    
    // Verificar se tabela user_settings existe
    $stmt = $pdo->query("
        CREATE TABLE IF NOT EXISTS user_settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL UNIQUE,
            ga_tracking_id TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    
    // Inserir ou atualizar
    $stmt = $pdo->prepare("
        INSERT INTO user_settings (user_id, ga_tracking_id, updated_at)
        VALUES (?, ?, CURRENT_TIMESTAMP)
        ON CONFLICT(user_id) DO UPDATE SET
            ga_tracking_id = excluded.ga_tracking_id,
            updated_at = CURRENT_TIMESTAMP
    ");
    
    $stmt->execute([$userId, $gaTrackingId]);
    
    return ['success' => true, 'message' => 'Google Analytics configurado com sucesso!'];
}