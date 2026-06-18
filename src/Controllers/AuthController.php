<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;

class AuthController extends Controller
{
    public function login(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect('index.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $user = User::verifyPassword($email, $password);

            if ($user) {
                Session::set('user_id', $user['id']);
                Session::set('user_nom', $user['nom']);
                Session::set('user_email', $user['email']);
                $this->redirect('index.php');
            }
            $error = 'Email ou mot de passe incorrect.';
        }

        $this->view('auth/login', ['error' => $error ?? null]);
    }

    public function register(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect('index.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (empty($nom) || empty($email) || empty($password)) {
                $error = 'Tous les champs sont obligatoires.';
            } elseif ($password !== $confirm) {
                $error = 'Les mots de passe ne correspondent pas.';
            } elseif (User::findByEmail($email)) {
                $error = 'Cet email est déjà utilisé.';
            } else {
                User::create($nom, $email, $password);
                Session::setFlash('success', 'Compte créé avec succès. Connectez-vous.');
                $this->redirect('login.php');
            }
        }

        $this->view('auth/register', ['error' => $error ?? null]);
    }

    public function logout(): void
    {
        Session::destroy();
        $this->redirect('login.php');
    }
}