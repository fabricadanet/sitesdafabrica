<?php
// database/migrations/2025_01_01_000002_create_plans_table.php

namespace Database\Migrations;

class CreatePlansTable
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
            CREATE TABLE IF NOT EXISTS plans (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT UNIQUE NOT NULL,
                description TEXT,
                price REAL NOT NULL DEFAULT 0,
                max_projects INTEGER NOT NULL DEFAULT 5,
                max_storage_mb INTEGER NOT NULL DEFAULT 100,
                max_downloads INTEGER NOT NULL DEFAULT 1000,
                max_domains INTEGER NOT NULL DEFAULT 1,
                max_subdomains INTEGER NOT NULL DEFAULT 3,
                max_domains_per_project INTEGER,
                is_featured INTEGER DEFAULT 0,
                is_visible INTEGER DEFAULT 1,
                display_order INTEGER DEFAULT 0,
                status TEXT DEFAULT 'active' CHECK(status IN ('active', 'inactive', 'deprecated')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                deleted_at DATETIME
            );
            CREATE INDEX IF NOT EXISTS idx_name ON plans(name);
            CREATE INDEX IF NOT EXISTS idx_status ON plans(status);
            CREATE INDEX IF NOT EXISTS idx_featured ON plans(is_featured);
            CREATE INDEX IF NOT EXISTS idx_price ON plans(price);
            CREATE INDEX IF NOT EXISTS idx_display_order ON plans(display_order);
            ";
        } else {
            // MySQL
            return "
            CREATE TABLE IF NOT EXISTS plans (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL UNIQUE,
                description TEXT,
                price DECIMAL(10, 2) NOT NULL DEFAULT 0,
                max_projects INT NOT NULL DEFAULT 5 COMMENT 'Número máximo de projetos',
                max_storage_mb INT NOT NULL DEFAULT 100 COMMENT 'Armazenamento máximo em MB',
                max_downloads INT NOT NULL DEFAULT 1000 COMMENT 'Número máximo de downloads por mês',
                max_domains INT NOT NULL DEFAULT 1 COMMENT 'Número máximo de domínios personalizados',
                max_subdomains INT NOT NULL DEFAULT 3 COMMENT 'Número máximo de subdomínios',
                max_domains_per_project INT DEFAULT NULL COMMENT 'Limite de domínios por projeto',
                is_featured TINYINT(1) DEFAULT 0 COMMENT 'Se o plano é destaque',
                is_visible TINYINT(1) DEFAULT 1 COMMENT 'Se o plano é visível',
                display_order INT DEFAULT 0 COMMENT 'Ordem de exibição',
                status ENUM('active', 'inactive', 'deprecated') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            
            CREATE INDEX idx_name ON plans(name);
            CREATE INDEX idx_status ON plans(status);
            CREATE INDEX idx_featured ON plans(is_featured);
            CREATE INDEX idx_price ON plans(price);
            CREATE INDEX idx_display_order ON plans(display_order);
            ";
        }
    }

    public function down()
    {
        return "DROP TABLE IF EXISTS plans";
    }
}
