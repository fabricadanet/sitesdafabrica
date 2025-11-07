<?php
// app/models/Project.php
namespace App\Models;
use PDO;

class Project {
  private $pdo;
  public function __construct() {
    $this->pdo = require __DIR__ . '/../../config/database.php';
  }

  public function save($userId, $title, $content) {
    $stmt = $this->pdo->prepare("INSERT INTO projects (user_id,title,content) VALUES (?,?,?)");
    $stmt->execute([$userId, $title, $content]);
  }

  public function allByUser($userId) {
    $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
