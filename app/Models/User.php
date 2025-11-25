<?php
namespace App\Models;

class User
{
    private $pdo;

    public function __construct()
    {
        // 🔹 Garante o caminho certo do banco (usando \PDO global)
        $this->pdo = require __DIR__ . '/../../config/database.php';
    }

    /**
     * 🔍 Buscar usuário por e-mail
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * ➕ Registrar novo usuário
     */
    public function register(string $name, string $email, string $password): bool
    {
        // Garante que o e-mail não existe
        if ($this->findByEmail($email)) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$name, $email, $hash]);
    }

    /**
     * 🔑 Validar login
     */
    public function login(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        if (!$user) return null;

        if (password_verify($password, $user['password'])) {
            return $user;
        }

        return null;
    }

    /**
     * 🔄 Atualizar senha
     */
    public function updatePassword(int $id, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hash, $id]);
    }

    /**
     * 🔒 Buscar usuário por ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $user ?: null;
    }
}
