<?php
// config/migrate_admin.php
// Adiciona tabelas para admin: plans, subscriptions, templates_library

$dbFile = __DIR__ . '/database.sqlite';
try {
    echo "<h3>🚀 Executando migrações de admin...</h3>";
    $pdo = new \PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    // Adiciona coluna role em users (se não existir)
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN role TEXT DEFAULT 'user'");
        echo "✅ Coluna 'role' adicionada em users.\n";
    } catch (\PDOException $e) {
        echo "ℹ️ Coluna 'role' já existe em users.\n";
    }

    // Tabela de planos
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS plans (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            description TEXT,
            price REAL DEFAULT 0,
            max_projects INTEGER DEFAULT 5,
            max_storage_mb INTEGER DEFAULT 100,
            features TEXT DEFAULT '{}',
            status TEXT DEFAULT 'active',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP
        );
    ");
    echo "✅ Tabela 'plans' verificada.\n";

    // Tabela de assinaturas
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS subscriptions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            plan_id INTEGER NOT NULL,
            status TEXT DEFAULT 'active',
            started_at TEXT NOT NULL,
            renews_at TEXT,
            canceled_at TEXT,
            payment_method TEXT,
            transaction_id TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY(plan_id) REFERENCES plans(id) ON DELETE RESTRICT
        );
    ");
    echo "✅ Tabela 'subscriptions' verificada.\n";

    // Tabela de biblioteca de templates
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS templates_library (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            title TEXT NOT NULL,
            description TEXT,
            category TEXT DEFAULT 'geral',
            html_file TEXT NOT NULL,
            thumb_file TEXT,
            status TEXT DEFAULT 'active',
            order_position INTEGER DEFAULT 0,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP
        );
    ");
    echo "✅ Tabela 'templates_library' verificada.\n";

    // Planos padrão
    $stmt = $pdo->query("SELECT COUNT(*) FROM plans");
    if ($stmt->fetchColumn() == 0) {
        $plans = [
            ['Gratuito', 'Plano básico para testes', 0, 3, 50],
            ['Profissional', 'Para pequenos negócios', 29.90, 15, 500],
            ['Empresarial', 'Solução completa', 99.90, 100, 2000],
        ];
        
        foreach ($plans as [$name, $desc, $price, $projects, $storage]) {
            $pdo->prepare("
                INSERT INTO plans (name, description, price, max_projects, max_storage_mb, features)
                VALUES (?, ?, ?, ?, ?, ?)
            ")->execute([
                $name,
                $desc,
                $price,
                $projects,
                $storage,
                json_encode(['suporte' => 'email', 'analytics' => true])
            ]);
        }
        echo "👤 Planos padrão criados.\n";
    } else {
        echo "ℹ️ Planos já existem.\n";
    }

    // Admin user
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'");
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("
            INSERT INTO users (name, email, password, role)
            VALUES (?, ?, ?, ?)
        ")->execute(['Administrador', 'admin@sitesfabrica.com', $hash, 'admin']);
        echo "👤 Usuário admin criado (email: admin@sitesfabrica.com / senha: admin123)\n";
    }

    echo "<br><strong>✅ Migração de admin concluída!</strong>";

} catch (\PDOException $e) {
    echo "<br><b>Erro:</b> " . $e->getMessage();
}