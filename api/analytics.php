<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Core\Session;
Session::start();
header('Content-Type: application/json');

$userId = Session::get('user_id');
if (!$userId) { http_response_code(401); echo json_encode([]); exit; }

$budgets = \App\Models\Budget::getAllByUser($userId);
$data = [];
foreach ($budgets as $b) {
    $expenses = \App\Models\Expense::getByBudget($b['id'], false);
    $totalDep = array_sum(array_column($expenses, 'montant'));
    $previsions = \App\Models\Budget::getPrevisions($b['id']);
    $totalPrev = array_sum(array_column($previsions, 'montant_prevu'));

    $daysInMonth = date('t', strtotime($b['mois'] . '-01'));
    $budgetDays = min((int)date('j'), $daysInMonth);

    $data[] = [
        'mois' => $b['mois'],
        'revenu' => (float)$b['revenu_mensuel'],
        'depense' => $totalDep,
        'prevu' => $totalPrev,
        'cloture' => (bool)$b['cloture'],
        'jour_actuel' => $budgetDays,
    ];
}
echo json_encode($data);