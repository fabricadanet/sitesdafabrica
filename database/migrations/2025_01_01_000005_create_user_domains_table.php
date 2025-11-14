<?php
// database/migrations/2025_01_01_000005_create_user_domains_table.php

namespace Database\Migrations;

class CreateUserDomainsTable
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
            CREATE TABLE IF NOT EXISTS user_domains (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                project_id INTEGER,
                domain_name TEXT UNIQUE NOT NULL,
                domain_type TEXT DEFAULT 'custom' CHECK(domain_type IN ('custom', 'subdomain')),
                dns_verified INTEGER DEFAULT 0,
                dns_cname_record TEXT,
                dns_verified_at DATETIME,
                ssl_enabled INTEGER DEFAULT 0,
                ssl_certificate TEXT,
                ssl_expires_at DATETIME,
                status TEXT DEFAULT 'pending' CHECK(status IN ('pending', 'active', 'inactive', 'expired', 'error')),
                error_message TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                deleted_at DATETIME,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
            );
            CREATE INDEX IF NOT EXISTS idx_user_id ON user_domains(user_id);
            CREATE INDEX IF NOT EXISTS idx_project_id ON user_domains(project_id);
            CREATE INDEX IF NOT EXISTS idx_domain_name ON user_domains(domain_name);
            CREATE INDEX IF NOT EXISTS idx_dns_verified ON user_domains(dns_verified);
            CREATE INDEX IF NOT EXISTS idx_status ON user_domains(status);
            CREATE INDEX IF NOT EXISTS idx_user_status ON user_domains(user_id, status);
            ";
        } else {
            // MySQL
            return "
            CREATE TABLE IF NOT EXISTS user_domains (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                project_id INT,
                domain_name VARCHAR(255) NOT NULL UNIQUE,
                domain_type ENUM('custom', 'subdomain') DEFAULT 'custom',
                dns_verified TINYINT(1) DEFAULT 0,
                dns_cname_record VARCHAR(255),
                dns_verified_at TIMESTAMP NULL,
                ssl_enabled TINYINT(1) DEFAULT 0,
                ssl_certificate VARCHAR(500),
                ssl_expires_at TIMESTAMP NULL,
                status ENUM('pending', 'active', 'inactive', 'expired', 'error') DEFAULT 'pending',
                error_message TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            
            CREATE INDEX idx_user_id ON user_domains(user_id);
            CREATE INDEX idx_project_id ON user_domains(project_id);
            CREATE INDEX idx_domain_name ON user_domains(domain_name);
            CREATE INDEX idx_dns_verified ON user_domains(dns_verified);
            CREATE INDEX idx_status ON user_domains(status);
            CREATE INDEX idx_user_status ON user_domains(user_id, status);
            ";
        }
    }

    public function down()
    {
        return "DROP TABLE IF EXISTS user_domains";
    }
}
