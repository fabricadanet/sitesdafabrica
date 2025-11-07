<?php
namespace App\Controllers;

use App\Models\User;
use \PDO;

class AuthController
{
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            $userModel = new User();
            $user = $userModel->login($email, $password);

            if ($user) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];

                header('Location: /projects');
                exit;
            } else {
                echo "<script>alert('❌ Usuário ou senha inválidos!');history.back();</script>";
            }
        } else {
            include __DIR__ . '/../views/auth/login.php';
        }
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            $userModel = new User();
            if ($userModel->register($name, $email, $password)) {
                echo "<script>alert('✅ Usuário cadastrado com sucesso!');window.location='/login';</script>";
            } else {
                echo "<script>alert('⚠️ E-mail já cadastrado!');history.back();</script>";
            }
        } else {
            include __DIR__ . '/../views/auth/register.php';
        }
    }

    public function logout()
    {
        session_start();
        session_destroy();
        header('Location: /login');
        exit;
    }
}

