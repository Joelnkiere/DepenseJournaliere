<?php
require_once __DIR__ . '/../config/bootstrap.php';
\App\Core\Session::startIfNot();
header('Content-Type: application/json');

$userId = \App\Core\Session::get('user_id');
if (!$userId) { echo json_encode(['error' => 'Non connecté']); exit; }

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create_period':
        $type = $_POST['type'] ?? '';
        $startDate = $_POST['start_date'] ?? null;
        $endDate = $_POST['end_date'] ?? null;
        $nom = $_POST['nom'] ?? '';
        if (!$type) { echo json_encode(['error' => 'Type requis']); exit; }
        if ($type === 'custom') {
            if (!$startDate || !$endDate) { echo json_encode(['error' => 'Dates requises pour personnalisé']); exit; }
            $id = \App\Models\BudgetPeriod::create($userId, $type, $startDate, $endDate, $nom ?: null);
        } else {
            $period = \App\Models\BudgetPeriod::createForCurrent($userId, $type);
            $id = $period['id'];
        }
        echo json_encode(['success' => true, 'id' => $id]);
        break;

    case 'save_previsions_period':
        $periodId = (int)($_POST['period_id'] ?? 0);
        $previsionsJson = $_POST['previsions'] ?? '[]';
        $previsions = json_decode($previsionsJson, true) ?: [];
        foreach ($previsions as $p) {
            $cat = (int)($p['category_id'] ?? 0);
            $montant = (float)($p['montant'] ?? 0);
            if ($cat > 0 && $montant > 0) {
                \App\Models\BudgetPeriod::setPrevision($periodId, $cat, $montant);
            }
        }
        echo json_encode(['success' => true]);
        break;

    case 'cloturer_period':
        $id = (int)($_POST['id'] ?? 0);
        \App\Models\BudgetPeriod::cloturer($id);
        echo json_encode(['success' => true]);
        break;

    case 'delete_period':
        $id = (int)($_POST['id'] ?? 0);
        \App\Models\BudgetPeriod::delete($id);
        echo json_encode(['success' => true]);
        break;

    case 'add_revenue':
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $montant = (float)($_POST['montant'] ?? 0);
        $source = $_POST['source'] ?? '';
        $dateRevenu = $_POST['date_revenu'] ?? date('Y-m-d');
        $periodId = !empty($_POST['period_id']) ? (int)$_POST['period_id'] : null;
        $accountId = !empty($_POST['account_id']) ? (int)$_POST['account_id'] : null;
        $estRecurrent = !empty($_POST['est_recurrent']) ? 1 : 0;
        $frequence = $_POST['frequence'] ?? null;
        if (!$categoryId || $montant <= 0) { echo json_encode(['error' => 'Données invalides']); exit; }
        $id = \App\Models\Revenue::create($userId, $categoryId, $montant, $source, $dateRevenu, $periodId, $accountId, $estRecurrent, $frequence);
        if ($accountId) {
            \App\Models\Account::adjustSolde($accountId, $montant);
        }
        echo json_encode(['success' => true, 'id' => $id]);
        break;

    case 'delete_revenue':
        $id = (int)($_POST['id'] ?? 0);
        \App\Models\Revenue::delete($id);
        echo json_encode(['success' => true]);
        break;

    case 'update_revenue':
        $id = (int)($_POST['id'] ?? 0);
        $montant = (float)($_POST['montant'] ?? 0);
        $source = $_POST['source'] ?? '';
        \App\Models\Revenue::update($id, $montant, $source);
        echo json_encode(['success' => true]);
        break;

    case 'get_revenue':
        $id = (int)($_GET['id'] ?? 0);
        $rev = \App\Models\Revenue::getById($id);
        echo json_encode(['revenue' => $rev]);
        break;

    default:
        echo json_encode(['error' => 'Action inconnue']);
}
