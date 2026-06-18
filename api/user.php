<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Core\Session;
header('Content-Type: application/json');
if (!Session::isLoggedIn()) { http_response_code(401); echo json_encode(['error'=>'Non authentifié']); exit; }

$userId = Session::get('user_id');
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'update_profile':
            $nom = trim($_POST['nom']??''); $email = trim($_POST['email']??'');
            if (empty($nom)||empty($email)) { http_response_code(400); echo json_encode(['error'=>'Champs requis']); exit; }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { http_response_code(400); echo json_encode(['error'=>'Email invalide']); exit; }
            $existing = \App\Models\User::findByEmail($email);
            if ($existing && $existing['id'] !== $userId) { http_response_code(400); echo json_encode(['error'=>'Email déjà utilisé']); exit; }
            \App\Models\User::updateProfile($userId, $nom, $email);
            Session::set('user_nom', $nom);
            echo json_encode(['success'=>true]);
            break;

        case 'update_password':
            $current = $_POST['current_password']??'';
            $newPass = $_POST['new_password']??'';
            $user = \App\Models\User::findById($userId);
            if (!password_verify($current, $user['password'])) { http_response_code(400); echo json_encode(['error'=>'Mot de passe actuel incorrect']); exit; }
            if (strlen($newPass) < 6) { http_response_code(400); echo json_encode(['error'=>'Minimum 6 caractères']); exit; }
            \App\Models\User::updatePassword($userId, $newPass);
            echo json_encode(['success'=>true]);
            break;

        case 'update_theme':
            $theme = $_POST['theme']??'dark';
            if (!in_array($theme, ['dark','light'])) $theme = 'dark';
            \App\Models\User::updateTheme($userId, $theme);
            echo json_encode(['success'=>true, 'theme'=>$theme]);
            break;

        default: http_response_code(400); echo json_encode(['error'=>'Action inconnue']);
    }
} catch (Exception $e) { http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }