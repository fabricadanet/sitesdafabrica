<?php
// app/Controllers/DownloadController.php

namespace App\Controllers;

use ZipArchive;

class DownloadController
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = require __DIR__ . '/../../config/database.php';
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->requireAuth();
    }

    private function requireAuth()
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            die('Acesso não autorizado.');
        }
    }

 public function exportProject()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            die('Método não permitido.');
        }

        $userId = $_SESSION['user_id'];
        $projectId = $_GET['id'] ?? null;

        if (!$projectId) {
            die("ID do projeto não fornecido.");
        }

        // 1. Buscar os dados do projeto no banco (Agora incluindo os campos de SEO)
        $stmt = $this->pdo->prepare("SELECT name, html_content, seo_title, seo_description, seo_image FROM projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$projectId, $userId]);
        $project = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$project) {
            die("Projeto não encontrado ou sem permissão de acesso.");
        }

        $html = $project['html_content'] ?? '';
        $projectName = preg_replace('/[^a-zA-Z0-9\-_]/', '_', strtolower(trim($project['name'])));
        if (empty($projectName)) $projectName = 'meu_site';

        $zipFileName = tempnam(sys_get_temp_dir(), 'site_') . '.zip';
        $zip = new ZipArchive();
        
        if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            die("Erro crítico ao gerar o pacote ZIP.");
        }

        $zip->addEmptyDir('assets');

        // === NOVA LÓGICA: TRATAR A IMAGEM DE SEO ===
        $seoImage = $project['seo_image'] ?? '';
        $localSeoImage = '';

        if (!empty($seoImage) && strpos($seoImage, '/uploads/') === 0) {
            $physicalSeoPath = realpath(__DIR__ . '/../../public' . $seoImage);
            if ($physicalSeoPath && file_exists($physicalSeoPath)) {
                $filename = basename($seoImage);
                $zip->addFile($physicalSeoPath, 'assets/' . $filename);
                // Caminho relativo para a tag Open Graph no HTML exportado
                $localSeoImage = './assets/' . $filename;
            } else {
                $localSeoImage = $seoImage;
            }
        } else {
            // Se for vazia ou uma URL externa (ex: https://unsplash.com/...)
            $localSeoImage = $seoImage; 
        }
        // ===========================================

        // 3. Procurar por todas as imagens do conteúdo HTML
        $pattern = '/(\/uploads\/users\/' . $userId . '\/[a-zA-Z0-9_\-\.]+)/i';
        if (preg_match_all($pattern, $html, $matches)) {
            $imagePaths = array_unique($matches[1]);
            
            foreach ($imagePaths as $relativePath) {
                $physicalPath = realpath(__DIR__ . '/../../public' . $relativePath);
                if ($physicalPath && file_exists($physicalPath)) {
                    $filename = basename($relativePath);
                    $zip->addFile($physicalPath, 'assets/' . $filename);
                    $html = str_replace($relativePath, './assets/' . $filename, $html);
                }
            }
        }

        // 4. Empacotar o ficheiro index.html passando os dados de SEO
        $finalHtml = $this->buildExportHtml($project, $html, $localSeoImage);
        $zip->addFromString('index.html', $finalHtml);
        
        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $projectName . '.zip"');
        header('Content-Length: ' . filesize($zipFileName));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        readfile($zipFileName);
        unlink($zipFileName);
        exit;
    }

private function buildExportHtml($project, $content, $localSeoImage)
    {
        $seoTitle = $project['seo_title'] ?? null;
        $seoDescription = $project['seo_description'] ?? null;
        $projectName = $project['name'] ?? 'Meu Site';

        $title = htmlspecialchars($seoTitle ?: $projectName, ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars($seoDescription ?: 'Site gerado pela plataforma', ENT_QUOTES, 'UTF-8');
        
        // Se o HTML salvo já for um documento completo (contém a tag <head>), atualizamos as tags de SEO direto nele
        if (preg_match('/<head[^>]*>/i', $content)) {
            // Atualizar ou injetar Title no <head>
            if (preg_match('/<title>(.*?)<\/title>/i', $content)) {
                $content = preg_replace('/<title>(.*?)<\/title>/i', "<title>{$title}</title>", $content);
            } else {
                $content = preg_replace('/<\/head>/i', "    <title>{$title}</title>\n</head>", $content);
            }

            // Atualizar ou injetar Meta Description no <head>
            if (preg_match('/<meta[^>]*name="description"[^>]*>/i', $content)) {
                $content = preg_replace('/<meta[^>]*name="description"[^>]*>/i', "<meta name=\"description\" content=\"{$description}\">", $content);
            } else {
                $content = preg_replace('/<\/head>/i', "    <meta name=\"description\" content=\"{$description}\">\n</head>", $content);
            }

            // Injetar OG Image se definida
            if (!empty($localSeoImage)) {
                // Remove og:image e twitter:image antigos se existirem
                $content = preg_replace('/<meta[^>]*property="og:image"[^>]*>/i', '', $content);
                $content = preg_replace('/<meta[^>]*name="twitter:image"[^>]*>/i', '', $content);
                
                $safeImage = htmlspecialchars($localSeoImage, ENT_QUOTES, 'UTF-8');
                $ogTags = "<meta property=\"og:image\" content=\"{$safeImage}\">\n    <meta name=\"twitter:image\" content=\"{$safeImage}\">";
                $content = preg_replace('/<\/head>/i', "    {$ogTags}\n</head>", $content);
            }

            return $content;
        }

        $ogImageTag = '';
        if (!empty($localSeoImage)) {
            $safeImage = htmlspecialchars($localSeoImage, ENT_QUOTES, 'UTF-8');
            $ogImageTag = "<meta property=\"og:image\" content=\"{$safeImage}\">\n    <meta name=\"twitter:image\" content=\"{$safeImage}\">";
        }
        
        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <meta name="description" content="{$description}">
    
    <meta property="og:type" content="website">
    <meta property="og:title" content="{$title}">
    <meta property="og:description" content="{$description}">
    {$ogImageTag}
    <meta name="twitter:card" content="summary_large_image">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] } } } }
    </script>
    
    <style>
        body { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-white text-gray-900 font-sans">
    {$content}
</body>
</html>
HTML;
    }
}