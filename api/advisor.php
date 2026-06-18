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
$mois = $_GET['mois'] ?? date('Y-m');
$budget = \App\Models\Budget::getCurrent($userId, $mois);

if (!$budget || $budget['revenu_mensuel'] <= 0) {
    echo json_encode(['error' => 'Configurez votre revenu mensuel.']);
    exit;
}

$previsions = \App\Models\Budget::getPrevisions($budget['id']);
$depensesReelles = \App\Models\Budget::getReelByCategory($budget['id']);

$advisor = new \App\Helpers\FinancialAdvisor($budget['revenu_mensuel'], $depensesReelles, $previsions);
$conseils = $advisor->getAdvice();

echo json_encode($conseils);