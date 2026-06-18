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
        case 'list':
            $goals = \App\Models\SavingsGoal::getByUser($userId);
            echo json_encode($goals);
            break;

        case 'get':
            $id = (int) ($_GET['id'] ?? 0);
            $goal = \App\Models\SavingsGoal::getById($id);
            echo json_encode($goal ?: ['error' => 'Not found']);
            break;

        case 'add':
            $titre = trim($_POST['titre'] ?? '');
            $montantCible = (float) ($_POST['montant_cible'] ?? 0);
            $dateLimite = $_POST['date_limite'] ?? null;
            $accountId = !empty($_POST['account_id']) ? (int)$_POST['account_id'] : null;
            $autoSaveType = $_POST['auto_save_type'] ?? 'none';
            $autoSaveValue = (float) ($_POST['auto_save_value'] ?? 0);
            $autoSaveFrequence = $_POST['auto_save_frequence'] ?? null;

            if (empty($titre) || $montantCible <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Titre et montant cible requis.']);
                exit;
            }

            $id = \App\Models\SavingsGoal::create($userId, $titre, $montantCible, $dateLimite ?: null, $accountId, $autoSaveType, $autoSaveValue, $autoSaveFrequence);
            $goal = \App\Models\SavingsGoal::getById($id);
            echo json_encode(['success' => true, 'goal' => $goal]);
            break;

        case 'update':
            $id = (int) ($_POST['id'] ?? 0);
            $titre = trim($_POST['titre'] ?? '');
            $montantCible = (float) ($_POST['montant_cible'] ?? 0);
            $dateLimite = $_POST['date_limite'] ?? null;
            $accountId = !empty($_POST['account_id']) ? (int)$_POST['account_id'] : null;
            $autoSaveType = $_POST['auto_save_type'] ?? 'none';
            $autoSaveValue = (float) ($_POST['auto_save_value'] ?? 0);
            $autoSaveFrequence = $_POST['auto_save_frequence'] ?? null;

            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'ID requis.']);
                exit;
            }

            \App\Models\SavingsGoal::update($id, $titre, $montantCible, $dateLimite ?: null, $accountId, $autoSaveType, $autoSaveValue, $autoSaveFrequence);
            $goal = \App\Models\SavingsGoal::getById($id);
            echo json_encode(['success' => true, 'goal' => $goal]);
            break;

        case 'delete':
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'ID requis.']);
                exit;
            }
            \App\Models\SavingsGoal::delete($id);
            echo json_encode(['success' => true]);
            break;

        case 'add_funds':
            $id = (int) ($_POST['id'] ?? 0);
            $montant = (float) ($_POST['montant'] ?? 0);
            if ($id <= 0 || $montant <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'ID et montant requis.']);
                exit;
            }
            \App\Models\SavingsGoal::addFunds($id, $montant);
            $goal = \App\Models\SavingsGoal::getById($id);
            echo json_encode(['success' => true, 'goal' => $goal]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Action inconnue']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}