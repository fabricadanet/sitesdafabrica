<?php
// app/Controllers/UploadController.php

namespace App\Controllers;

class UploadController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->requireAuth();
    }

    private function requireAuth()
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Não autorizado']);
            exit;
        }
    }

    public function uploadImage()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            return;
        }

        header('Content-Type: application/json');

        // Validação CSRF
        $clientToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'], $clientToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Sessão inválida (CSRF).']);
            return;
        }

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Nenhuma imagem recebida ou erro no upload.']);
            return;
        }

        $file = $_FILES['image'];
        $userId = $_SESSION['user_id'];

        // Validação básica de tipo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Apenas JPG, PNG e WebP são permitidos.']);
            return;
        }

        // Criar diretório do utilizador se não existir
        $uploadDir = __DIR__ . '/../../public/uploads/users/' . $userId . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Gerar nome único
        $fileName = 'img_' . time() . '_' . bin2hex(random_bytes(4)) . '.webp';
        $destination = $uploadDir . $fileName;

        // Processar, redimensionar e converter para WebP
        try {
            $this->processAndConvertToWebp($file['tmp_name'], $mimeType, $destination);
            
            $publicUrl = '/uploads/users/' . $userId . '/' . $fileName;
            
            echo json_encode([
                'success' => true, 
                'url' => $publicUrl,
                'message' => 'Imagem processada com sucesso!'
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao processar imagem: ' . $e->getMessage()]);
        }
    }

    private function processAndConvertToWebp($sourcePath, $mimeType, $destinationPath, $maxWidth = 1920)
    {
        // Carregar a imagem baseada no mime type
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($sourcePath);
                // Preservar transparência no PNG antes da conversão
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($sourcePath);
                break;
            default:
                throw new \Exception('Formato não suportado para processamento.');
        }

        if (!$image) {
            throw new \Exception('Falha ao processar a imagem na memória.');
        }

        $width = imagesx($image);
        $height = imagesy($image);

        // Redimensionar apenas se for maior que o máximo permitido
        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = floor($height * ($maxWidth / $width));
            
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Manter transparência se existir
            if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $newImage;
        }

        // Guardar como WebP com 80% de qualidade (excelente rácio tamanho/qualidade)
        imagewebp($image, $destinationPath, 80);
        imagedestroy($image);
    }
}