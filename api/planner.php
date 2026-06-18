<?php
require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Session;
use App\Planners\PlannerFactory;
use App\Models\FinancialPlan;

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
        case 'generate':
            $type = $_POST['type'] ?? 'rule';
            $planner = PlannerFactory::create($type);
            $planData = $planner->generatePlan($userId);

            $periodId = $planData['period']['id'] ?? null;
            $planId = FinancialPlan::create($userId, $periodId, $type, $planData);
            FinancialPlan::desactivateOthers($userId, $planId);

            echo json_encode(['success' => true, 'plan_id' => $planId, 'plan' => $planData]);
            break;

        case 'current':
            $plan = FinancialPlan::getActive($userId);
            if (!$plan) {
                echo json_encode(['plan' => null]);
                exit;
            }
            echo json_encode(['plan' => $plan]);
            break;

        case 'list':
            $plans = FinancialPlan::getAllByUser($userId);
            echo json_encode(['plans' => $plans]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Action inconnue']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
