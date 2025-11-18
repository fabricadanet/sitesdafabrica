<?php
// database/migrations/2025_01_01_000003_create_subscriptions_table.php

namespace Database\Migrations;

class CreateSubscriptionsTable
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
            CREATE TABLE IF NOT EXISTS subscriptions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                plan_id INTEGER NOT NULL,
                current_downloads INTEGER DEFAULT 0,
                current_domains_count INTEGER DEFAULT 0,
                current_subdomains_count INTEGER DEFAULT 0,
                started_at DATE NOT NULL,
                renews_at DATE NOT NULL,
                canceled_at DATETIME,
                status TEXT DEFAULT 'active' CHECK(status IN ('active', 'inactive', 'canceled', 'paused', 'expired')),
                payment_method TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                deleted_at DATETIME,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE RESTRICT
            );
            CREATE INDEX IF NOT EXISTS idx_user_id ON subscriptions(user_id);
            CREATE INDEX IF NOT EXISTS idx_plan_id ON subscriptions(plan_id);
            CREATE INDEX IF NOT EXISTS idx_status ON subscriptions(status);
            CREATE INDEX IF NOT EXISTS idx_renews_at ON subscriptions(renews_at);
            CREATE INDEX IF NOT EXISTS idx_user_status ON subscriptions(user_id, status);
            CREATE INDEX IF NOT EXISTS idx_created_at ON subscriptions(created_at);
            ";
        } else {
            // MySQL
            return "
            CREATE TABLE IF NOT EXISTS subscriptions (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                plan_id INT NOT NULL,
                current_downloads INT DEFAULT 0 COMMENT 'Downloads utilizados neste mês',
                current_domains_count INT DEFAULT 0 COMMENT 'Domínios utilizados',
                current_subdomains_count INT DEFAULT 0 COMMENT 'Subdomínios utilizados',
                started_at DATE NOT NULL,
                renews_at DATE NOT NULL,
                canceled_at TIMESTAMP NULL,
                status ENUM('active', 'inactive', 'canceled', 'paused', 'expired') DEFAULT 'active',
                payment_method VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            
            CREATE INDEX idx_user_id ON subscriptions(user_id);
            CREATE INDEX idx_plan_id ON subscriptions(plan_id);
            CREATE INDEX idx_status ON subscriptions(status);
            CREATE INDEX idx_renews_at ON subscriptions(renews_at);
            CREATE INDEX idx_user_status ON subscriptions(user_id, status);
            CREATE INDEX idx_created_at ON subscriptions(created_at);
            ";
        }
    }

    public function down()
    {
        return "DROP TABLE IF EXISTS subscriptions";
    }
}
