<?php
// database/migrations/2025_01_02_000001_add_is_premium_to_templates.php

namespace Database\Migrations;

class AddIsPremiumToTemplates
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
            return [
                "ALTER TABLE templates_library ADD COLUMN is_premium INTEGER DEFAULT 0"
            ];
        } else {
            // MySQL
            return [
                "ALTER TABLE templates_library ADD COLUMN is_premium TINYINT(1) DEFAULT 0 COMMENT 'Se o template é exclusivo para planos premium'"
            ];
        }
    }

    public function down()
    {
        return "ALTER TABLE templates_library DROP COLUMN is_premium";
    }
}
