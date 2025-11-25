<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

class DatabaseSeeder
{
    private $pdo;
    private $dbDriver;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->dbDriver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    /**
     * Executa todos os seeders
     */
    public function run()
    {
        echo "▶ Populando tabela de planos...\n";
        $this->seedPlans();

        echo "✅ Dados iniciais criados com sucesso!\n";
    }

    /**
     * Popula planos padrão
     */
    private function seedPlans()
    {
        $plans = [
            [
                'name' => 'Gratuito',
                'description' => 'Perfeito para começar',
                'price' => 0,
                'max_projects' => 3,
                'max_storage_mb' => 100,
                'max_downloads' => 100,
                'max_domains' => 0,
                'max_subdomains' => 1,
                'max_domains_per_project' => null,
                'is_featured' => 0,
                'is_visible' => 1,
                'display_order' => 1,
                'status' => 'active'
            ],
            [
                'name' => 'Profissional',
                'description' => 'Para pequenos negócios',
                'price' => 29.90,
                'max_projects' => 10,
                'max_storage_mb' => 1024,
                'max_downloads' => 1000,
                'max_domains' => 2,
                'max_subdomains' => 5,
                'max_domains_per_project' => 1,
                'is_featured' => 1,
                'is_visible' => 1,
                'display_order' => 2,
                'status' => 'active'
            ],
            [
                'name' => 'Empresarial',
                'description' => 'Para empresas em crescimento',
                'price' => 99.90,
                'max_projects' => 50,
                'max_storage_mb' => 10240,
                'max_downloads' => 10000,
                'max_domains' => 10,
                'max_subdomains' => 20,
                'max_domains_per_project' => 3,
                'is_featured' => 1,
                'is_visible' => 1,
                'display_order' => 3,
                'status' => 'active'
            ],
            [
                'name' => 'Premium',
                'description' => 'Tudo ilimitado',
                'price' => 299.90,
                'max_projects' => 500,
                'max_storage_mb' => 102400,
                'max_downloads' => 100000,
                'max_domains' => 100,
                'max_subdomains' => 200,
                'max_domains_per_project' => 50,
                'is_featured' => 1,
                'is_visible' => 1,
                'display_order' => 4,
                'status' => 'active'
            ]
        ];

        foreach ($plans as $plan) {
            try {
                // Verifica se o plano já existe
                $checkStmt = $this->pdo->prepare("SELECT id FROM plans WHERE name = ?");
                $checkStmt->execute([$plan['name']]);
                $exists = $checkStmt->fetch();

                if ($exists) {
                    // Atualizar se já existe
                    $updateSql = "
                        UPDATE plans SET
                            description = :description,
                            price = :price,
                            max_projects = :max_projects,
                            max_storage_mb = :max_storage_mb,
                            max_downloads = :max_downloads,
                            max_domains = :max_domains,
                            max_subdomains = :max_subdomains,
                            max_domains_per_project = :max_domains_per_project,
                            is_featured = :is_featured,
                            is_visible = :is_visible,
                            display_order = :display_order,
                            status = :status
                        WHERE name = :name
                    ";
                    $stmt = $this->pdo->prepare($updateSql);
                    $stmt->execute($plan);
                    echo "  ✓ Plano '{$plan['name']}' atualizado\n";
                } else {
                    // Inserir se não existe
                    $insertSql = "
                        INSERT INTO plans 
                        (name, description, price, max_projects, max_storage_mb, max_downloads, 
                         max_domains, max_subdomains, max_domains_per_project, is_featured, 
                         is_visible, display_order, status)
                        VALUES 
                        (:name, :description, :price, :max_projects, :max_storage_mb, :max_downloads,
                         :max_domains, :max_subdomains, :max_domains_per_project, :is_featured,
                         :is_visible, :display_order, :status)
                    ";
                    $stmt = $this->pdo->prepare($insertSql);
                    $stmt->execute($plan);
                    echo "  ✓ Plano '{$plan['name']}' criado\n";
                }
            } catch (\Exception $e) {
                echo "  ❌ Erro ao criar plano '{$plan['name']}': " . $e->getMessage() . "\n";
            }
        }
    }
}

// Se executado via CLI
if (php_sapi_name() === 'cli' && basename(__FILE__) === 'DatabaseSeeder.php') {
    require_once __DIR__ . '/../../config/database.php';
    $seeder = new DatabaseSeeder($pdo);
    $seeder->run();
}
