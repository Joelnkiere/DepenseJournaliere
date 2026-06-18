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
$mois = $_POST['mois'] ?? $_GET['mois'] ?? date('Y-m');

try {
    switch ($action) {
        case 'list':
            $type = $_GET['type'] ?? 'reel';
            $budget = \App\Models\Budget::getCurrent($userId, $mois);
            if (!$budget) { echo json_encode([]); exit; }
            $filters = [];
            if (!empty($_GET['category_id'])) $filters['category_id'] = (int)$_GET['category_id'];
            if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
            if (!empty($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
            if (!empty($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];
            $expenses = \App\Models\Expense::getByBudget($budget['id'], $type === 'prevu', $filters);
            echo json_encode($expenses);
            break;

        case 'add':
            $budget = \App\Models\Budget::getOrCreate($userId, $mois);
            $periodId = !empty($_POST['period_id']) ? (int)$_POST['period_id'] : null;
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $montant = (float) ($_POST['montant'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            $dateDepense = $_POST['date_depense'] ?? date('Y-m-d');
            $estPrevu = !empty($_POST['est_prevu']);
            $accountId = !empty($_POST['account_id']) ? (int)$_POST['account_id'] : null;
            $imagePath = null;

            if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION);
                $filename = 'receipt_' . uniqid() . '.' . $ext;
                $dest = __DIR__ . '/../uploads/receipts/' . $filename;
                if (move_uploaded_file($_FILES['receipt']['tmp_name'], $dest)) {
                    $imagePath = 'uploads/receipts/' . $filename;
                }
            }

            if ($categoryId <= 0 || $montant <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Catégorie et montant requis.']);
                exit;
            }

            $id = \App\Models\Expense::create($budget['id'], $categoryId, $montant, $description, $dateDepense, $estPrevu, $imagePath, $periodId, $accountId);
            $expense = \App\Models\Expense::getById($id);
            echo json_encode(['success' => true, 'expense' => $expense]);
            break;

        case 'update':
            $id = (int) ($_POST['id'] ?? 0);
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $montant = (float) ($_POST['montant'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            $dateDepense = $_POST['date_depense'] ?? date('Y-m-d');

            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'ID requis.']);
                exit;
            }

            \App\Models\Expense::update($id, $categoryId, $montant, $description, $dateDepense);
            $expense = \App\Models\Expense::getById($id);
            echo json_encode(['success' => true, 'expense' => $expense]);
            break;

        case 'delete':
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'ID requis.']);
                exit;
            }
            \App\Models\Expense::delete($id);
            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Action inconnue']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}