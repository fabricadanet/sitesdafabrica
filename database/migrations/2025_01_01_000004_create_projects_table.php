<?php
// database/migrations/2025_01_01_000004_create_projects_table.php

namespace Database\Migrations;

class CreateProjectsTable
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
            CREATE TABLE IF NOT EXISTS projects (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                template_id INTEGER,
                name TEXT NOT NULL,
                description TEXT,
                custom_domain TEXT UNIQUE,
                subdomain TEXT UNIQUE,
                preview_url TEXT,
                published_url TEXT,
                html_content LONGTEXT,
                downloads_count INTEGER DEFAULT 0,
                views_count INTEGER DEFAULT 0,
                status TEXT DEFAULT 'draft' CHECK(status IN ('draft', 'published', 'archived', 'deleted')),
                is_published INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                deleted_at DATETIME,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (template_id) REFERENCES templates_library(id) ON DELETE SET NULL
            );
            CREATE INDEX IF NOT EXISTS idx_user_id ON projects(user_id);
            CREATE INDEX IF NOT EXISTS idx_template_id ON projects(template_id);
            CREATE INDEX IF NOT EXISTS idx_custom_domain ON projects(custom_domain);
            CREATE INDEX IF NOT EXISTS idx_subdomain ON projects(subdomain);
            CREATE INDEX IF NOT EXISTS idx_status ON projects(status);
            CREATE INDEX IF NOT EXISTS idx_is_published ON projects(is_published);
            CREATE INDEX IF NOT EXISTS idx_created_at ON projects(created_at);
            CREATE INDEX IF NOT EXISTS idx_user_status ON projects(user_id, status);
            ";
        } else {
            // MySQL
            return "
            CREATE TABLE IF NOT EXISTS projects (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                template_id INT,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                custom_domain VARCHAR(255) UNIQUE,
                subdomain VARCHAR(255) UNIQUE,
                preview_url VARCHAR(500),
                published_url VARCHAR(500),
                html_content LONGTEXT,
                downloads_count INT DEFAULT 0,
                views_count INT DEFAULT 0,
                status ENUM('draft', 'published', 'archived', 'deleted') DEFAULT 'draft',
                is_published TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (template_id) REFERENCES templates_library(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            
            CREATE INDEX idx_user_id ON projects(user_id);
            CREATE INDEX idx_template_id ON projects(template_id);
            CREATE INDEX idx_custom_domain ON projects(custom_domain);
            CREATE INDEX idx_subdomain ON projects(subdomain);
            CREATE INDEX idx_status ON projects(status);
            CREATE INDEX idx_is_published ON projects(is_published);
            CREATE INDEX idx_created_at ON projects(created_at);
            CREATE INDEX idx_user_status ON projects(user_id, status);
            ";
        }
    }

    public function down()
    {
        return "DROP TABLE IF EXISTS projects";
    }
}
