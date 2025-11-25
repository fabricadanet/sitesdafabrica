<?php
// database/migrations/2025_01_01_000007_create_downloads_log_table.php

namespace Database\Migrations;

class CreateDownloadsLogTable
{
    private $pdo;
    private $dbDriver;

    public function __construct($pdo, $dbDriver = 'mysql')
    {
        $this->pdo = $pdo;
        $this->dbDriver = $dbDriver;
    }

    public function up()
    {
        if ($this->dbDriver === 'sqlite') {
            return "
            CREATE TABLE IF NOT EXISTS downloads_log (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                project_id INTEGER NOT NULL,
                file_type TEXT,
                file_size INTEGER,
                ip_address TEXT,
                user_agent TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
            );
            CREATE INDEX IF NOT EXISTS idx_user_id ON downloads_log(user_id);
            CREATE INDEX IF NOT EXISTS idx_project_id ON downloads_log(project_id);
            CREATE INDEX IF NOT EXISTS idx_created_at ON downloads_log(created_at);
            CREATE INDEX IF NOT EXISTS idx_user_date ON downloads_log(user_id, created_at);
            ";
        } else {
            // MySQL
            return "
            CREATE TABLE IF NOT EXISTS downloads_log (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                project_id INT NOT NULL,
                file_type VARCHAR(50),
                file_size INT COMMENT 'Tamanho do arquivo em bytes',
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            
            CREATE INDEX idx_user_id ON downloads_log(user_id);
            CREATE INDEX idx_project_id ON downloads_log(project_id);
            CREATE INDEX idx_created_at ON downloads_log(created_at);
            CREATE INDEX idx_user_date ON downloads_log(user_id, created_at);
            ";
        }
    }

    public function down()
    {
        return "DROP TABLE IF EXISTS downloads_log";
    }
}
