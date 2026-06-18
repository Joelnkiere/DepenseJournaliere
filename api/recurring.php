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
            echo json_encode(\App\Models\RecurringTransaction::getByUser($userId));
            break;
        case 'add':
            $categoryId = (int)($_POST['category_id']??0);
            $montant = (float)($_POST['montant']??0);
            $description = trim($_POST['description']??'');
            $frequence = $_POST['frequence']??'mensuel';
            $jourExecution = (int)($_POST['jour_execution']??1);
            if ($categoryId<=0||$montant<=0) { http_response_code(400); echo json_encode(['error'=>'Champs requis']); exit; }
            $prochaine = date('Y-m-d', strtotime("+{$jourExecution} days"));
            $id = \App\Models\RecurringTransaction::create($userId, $categoryId, $montant, $description, $frequence, $jourExecution, $prochaine);
            echo json_encode(['success'=>true, 'id'=>$id]);
            break;
        case 'toggle':
            $id = (int)($_POST['id']??0); $actif = !empty($_POST['actif']);
            \App\Models\RecurringTransaction::update($id, 0, 0, '', 'mensuel', 1, $actif);
            echo json_encode(['success'=>true]);
            break;
        case 'delete':
            $id = (int)($_POST['id']??0);
            \App\Models\RecurringTransaction::delete($id);
            echo json_encode(['success'=>true]);
            break;
        case 'process_recurring':
            $today = date('Y-m-d');
            $due = \App\Models\RecurringTransaction::getDueTransactions($today);
            $processed = 0;
            foreach ($due as $t) {
                \App\Models\Expense::create($t['budget_id'], $t['category_id'], $t['montant'], '[Auto] '.($t['description']??'Récurrent'), $today, false);
                \App\Models\RecurringTransaction::updateNextExecution($t['id'], $t['frequence']);
                \App\Models\Notification::create($userId, 'info', 'Transaction récurrente exécutée', "{$t['description']} : {$t['montant']}€");
                $processed++;
            }
            echo json_encode(['success'=>true, 'processed'=>$processed]);
            break;
        default: http_response_code(400); echo json_encode(['error'=>'Action inconnue']);
    }
} catch (Exception $e) { http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }