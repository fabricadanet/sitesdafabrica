<?php
namespace Database\Migrations;

class AddSeoToProjectsTable
{
    private $pdo;
    private $dbDriver;

    public function __construct($pdo, $dbDriver)
    {
        $this->pdo = $pdo;
        $this->dbDriver = $dbDriver;
    }

    public function up()
    {
        if ($this->dbDriver === 'sqlite') {
            return [
                "ALTER TABLE projects ADD COLUMN seo_title TEXT DEFAULT NULL",
                "ALTER TABLE projects ADD COLUMN seo_description TEXT DEFAULT NULL",
                "ALTER TABLE projects ADD COLUMN seo_image TEXT DEFAULT NULL"
            ];
        } else {
            return "ALTER TABLE projects 
                    ADD COLUMN seo_title VARCHAR(255) DEFAULT NULL, 
                    ADD COLUMN seo_description TEXT DEFAULT NULL, 
                    ADD COLUMN seo_image VARCHAR(255) DEFAULT NULL";
        }
    }

    public function down()
    {
        if ($this->dbDriver === 'sqlite') {
            // SQLite suporta DROP COLUMN a partir da versão 3.35.0
            return [
                "ALTER TABLE projects DROP COLUMN seo_title",
                "ALTER TABLE projects DROP COLUMN seo_description",
                "ALTER TABLE projects DROP COLUMN seo_image"
            ];
        } else {
            return "ALTER TABLE projects 
                    DROP COLUMN seo_title, 
                    DROP COLUMN seo_description, 
                    DROP COLUMN seo_image";
        }
    }
}