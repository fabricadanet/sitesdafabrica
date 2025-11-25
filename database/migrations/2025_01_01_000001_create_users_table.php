<?php
// database/migrations/2025_01_01_000001_create_users_table.php

namespace Database\Migrations;

class CreateUsersTable
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
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                role TEXT DEFAULT 'user' CHECK(role IN ('user', 'admin')),
                domains_used INTEGER DEFAULT 0,
                subdomains_used INTEGER DEFAULT 0,
                status TEXT DEFAULT 'active' CHECK(status IN ('active', 'inactive', 'suspended')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                deleted_at DATETIME
            );
            CREATE INDEX IF NOT EXISTS idx_email ON users(email);
            CREATE INDEX IF NOT EXISTS idx_role ON users(role);
            CREATE INDEX IF NOT EXISTS idx_status ON users(status);
            CREATE INDEX IF NOT EXISTS idx_created_at ON users(created_at);
            ";
        } else {
            // MySQL
            return "
            CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role ENUM('user', 'admin') DEFAULT 'user',
                domains_used INT DEFAULT 0 COMMENT 'Número de domínios já utilizados',
                subdomains_used INT DEFAULT 0 COMMENT 'Número de subdomínios já utilizados',
                status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            
            CREATE INDEX idx_email ON users(email);
            CREATE INDEX idx_role ON users(role);
            CREATE INDEX idx_status ON users(status);
            CREATE INDEX idx_created_at ON users(created_at);
            ";
        }
    }

    public function down()
    {
        return "DROP TABLE IF EXISTS users";
    }
}
