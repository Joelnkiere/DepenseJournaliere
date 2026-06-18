<?php
require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Session;

header('Content-Type: application/json');

if (!Session::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

$userId = Session::get('user_id');
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'update_profile':
            $nom = trim($_POST['nom'] ?? '');
            $email = trim($_POST['email'] ?? '');

            if (empty($nom) || empty($email)) {
                http_response_code(400);
                echo json_encode(['error' => 'Tous les champs sont requis.']);
                exit;
            }

            $existing = \App\Models\User::findByEmail($email);
            if ($existing && (int)$existing['id'] !== $userId) {
                http_response_code(400);
                echo json_encode(['error' => 'Cet email est déjà utilisé.']);
                exit;
            }

            \App\Models\User::updateProfile($userId, $nom, $email);
            Session::set('user_nom', $nom);
            Session::set('user_email', $email);
            echo json_encode(['success' => true, 'nom' => $nom, 'email' => $email]);
            break;

        case 'update_password':
            $current = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            $user = \App\Models\User::findById($userId);
            if (!$user || !password_verify($current, $user['password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Mot de passe actuel incorrect.']);
                exit;
            }

            if (strlen($newPassword) < 6) {
                http_response_code(400);
                echo json_encode(['error' => 'Le mot de passe doit faire au moins 6 caractères.']);
                exit;
            }

            if ($newPassword !== $confirm) {
                http_response_code(400);
                echo json_encode(['error' => 'Les mots de passe ne correspondent pas.']);
                exit;
            }

            \App\Models\User::updatePassword($userId, $newPassword);
            echo json_encode(['success' => true, 'message' => 'Mot de passe mis à jour.']);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Action inconnue']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}