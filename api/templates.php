<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Core\Session;
header('Content-Type: application/json');
if (!Session::isLoggedIn()) { http_response_code(401); echo json_encode(['error'=>'Non authentifié']); exit; }

$userId = Session::get('user_id');
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            echo json_encode(\App\Models\BudgetTemplate::getByUser($userId));
            break;
        case 'save':
            $mois = $_POST['mois']??date('Y-m');
            $nom = trim($_POST['nom']??'');
            if (empty($nom)) { http_response_code(400); echo json_encode(['error'=>'Nom requis']); exit; }
            $budget = \App\Models\Budget::getCurrent($userId, $mois);
            if (!$budget) { http_response_code(400); echo json_encode(['error'=>'Aucun budget pour ce mois']); exit; }
            $id = \App\Models\BudgetTemplate::saveFromBudget($userId, $nom, null, $budget['id']);
            echo json_encode(['success'=>true, 'id'=>$id]);
            break;
        case 'apply':
            $id = (int)($_POST['id']??0);
            $mois = $_POST['mois']??date('Y-m');
            $budget = \App\Models\Budget::getOrCreate($userId, $mois);
            \App\Models\BudgetTemplate::applyToBudget($id, $budget['id']);
            echo json_encode(['success'=>true]);
            break;
        case 'delete':
            $id = (int)($_POST['id']??0);
            \App\Models\BudgetTemplate::delete($id);
            echo json_encode(['success'=>true]);
            break;
        default: http_response_code(400); echo json_encode(['error'=>'Action inconnue']);
    }
} catch (Exception $e) { http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }