<?php
// database/migrations/2025_01_01_000006_create_templates_library_table.php

namespace Database\Migrations;

class CreateTemplatesLibraryTable
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
            CREATE TABLE IF NOT EXISTS templates_library (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT UNIQUE NOT NULL,
                title TEXT,
                description TEXT,
                category TEXT,
                html_file TEXT,
                thumb_file TEXT,
                order_position INTEGER DEFAULT 0,
                status TEXT DEFAULT 'active' CHECK(status IN ('active', 'inactive', 'deprecated')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                deleted_at DATETIME
            );
            CREATE INDEX IF NOT EXISTS idx_name ON templates_library(name);
            CREATE INDEX IF NOT EXISTS idx_category ON templates_library(category);
            CREATE INDEX IF NOT EXISTS idx_status ON templates_library(status);
            CREATE INDEX IF NOT EXISTS idx_order_position ON templates_library(order_position);
            ";
        } else {
            // MySQL
            return "
            CREATE TABLE IF NOT EXISTS templates_library (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL UNIQUE,
                title VARCHAR(255),
                description TEXT,
                category VARCHAR(100),
                html_file VARCHAR(255),
                thumb_file VARCHAR(255),
                order_position INT DEFAULT 0,
                status ENUM('active', 'inactive', 'deprecated') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            
            CREATE INDEX idx_name ON templates_library(name);
            CREATE INDEX idx_category ON templates_library(category);
            CREATE INDEX idx_status ON templates_library(status);
            CREATE INDEX idx_order_position ON templates_library(order_position);
            ";
        }
    }

    public function down()
    {
        return "DROP TABLE IF EXISTS templates_library";
    }
}
