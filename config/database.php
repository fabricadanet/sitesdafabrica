<?php
// config/database.php
// ⚙️ Conexão SQLite + criação automática de tabelas

$dbFile = __DIR__ . '/database.sqlite'; // 🔹 Corrigido: caminho direto e consistente

// Cria a pasta se não existir
if (!file_exists(dirname($dbFile))) {
    mkdir(dirname($dbFile), 0777, true);
}

try {
    // ⚠️ Sempre use a classe global \PDO
    $pdo = new \PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    // Cria tabelas, se ainda não existirem
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

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

    // ✅ Garante que exista um usuário admin
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE email='admin@admin.com'");
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)")
            ->execute(['Administrador', 'admin@admin.com', $hash]);
    }

    return $pdo;

} catch (\PDOException $e) {
    die('Erro ao conectar ao banco SQLite: ' . $e->getMessage());
}

