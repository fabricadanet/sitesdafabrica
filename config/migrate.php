<?php
// config/migrate.php
// ⚙️ Script de migração automática para SQLite

$dbFile = __DIR__ . '/database.sqlite';

if (!file_exists($dbFile)) {
    touch($dbFile);
    echo "📦 Novo banco criado: {$dbFile}<br>";
}

try {
    $pdo = new \PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    echo "<h3>🚀 Executando migrações...</h3>";

    // === USERS ===
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );
    ");
    echo "✅ Tabela 'users' verificada.<br>";

    // === PROJECTS ===
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS projects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            content_html TEXT,
            template TEXT DEFAULT '',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(user_id) REFERENCES users(id)
        );
    ");
    echo "✅ Tabela 'projects' verificada.<br>";

    // === Verifica se há admin padrão ===
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE email='admin@admin.com'");
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)")
            ->execute(['Administrador', 'admin@admin.com', $hash]);
        echo "👤 Usuário admin criado (login: admin@admin.com / senha: 123456)<br>";
    } else {
        echo "ℹ️ Usuário admin já existe.<br>";
    }

    echo "<br><strong>✅ Migração concluída com sucesso!</strong>";

} catch (\PDOException $e) {
    echo "<br><b>Erro:</b> " . $e->getMessage();
}
