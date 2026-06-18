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
        case 'save_revenue':
            $mois = $_POST['mois'] ?? date('Y-m');
            $revenu = (float) ($_POST['revenu'] ?? 0);
            $budget = \App\Models\Budget::getOrCreate($userId, $mois);
            \App\Models\Budget::updateRevenue($budget['id'], $revenu);
            echo json_encode(['success' => true, 'revenu' => $revenu]);
            break;

        case 'save_previsions':
            $mois = $_POST['mois'] ?? date('Y-m');
            $budget = \App\Models\Budget::getOrCreate($userId, $mois);
            $previsions = json_decode($_POST['previsions'] ?? '[]', true);

            foreach ($previsions as $prev) {
                \App\Models\Budget::setPrevision($budget['id'], (int)$prev['category_id'], (float)$prev['montant']);
            }
            echo json_encode(['success' => true]);
            break;

        case 'cloturer':
            $mois = $_POST['mois'] ?? '';
            if (!$mois) {
                http_response_code(400);
                echo json_encode(['error' => 'Mois requis']);
                exit;
            }
            $budget = \App\Models\Budget::getCurrent($userId, $mois);
            if ($budget) {
                \App\Models\Budget::cloturer($budget['id']);
                echo json_encode(['success' => true, 'message' => "Budget de {$mois} clôturé."]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Budget introuvable']);
            }
            break;

        case 'get_kpi':
            $mois = $_GET['mois'] ?? date('Y-m');
            $budget = \App\Models\Budget::getCurrent($userId, $mois);
            if (!$budget) {
                echo json_encode(['revenu' => 0, 'total_depense' => 0, 'reste_a_vivre' => 0, 'total_epargne' => 0]);
                exit;
            }
            $totalDepense = \App\Models\Expense::getTotalByBudget($budget['id']);
            $totalEpargne = \App\Models\SavingsGoal::getTotalEpargne($userId);
            echo json_encode([
                'revenu' => (float) $budget['revenu_mensuel'],
                'total_depense' => $totalDepense,
                'reste_a_vivre' => $budget['revenu_mensuel'] - $totalDepense,
                'total_epargne' => $totalEpargne,
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Action inconnue']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}