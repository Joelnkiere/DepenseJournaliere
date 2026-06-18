<?php
require_once __DIR__ . '/../config/bootstrap.php';
\App\Core\Session::startIfNot();
header('Content-Type: application/json');

$userId = \App\Core\Session::get('user_id');
if (!$userId) { echo json_encode(['error' => 'Non connecté']); exit; }

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        $nom = $_POST['nom'] ?? '';
        $type = $_POST['type'] ?? 'courant';
        $soldeInitial = (float)($_POST['solde_initial'] ?? 0);
        if (!$nom) { echo json_encode(['error' => 'Nom requis']); exit; }
        $id = \App\Models\Account::create($userId, $nom, $type, $soldeInitial);
        echo json_encode(['success' => true, 'id' => $id]);
        break;

    case 'transaction':
        $accountId = (int)($_POST['account_id'] ?? 0);
        $type = $_POST['type'] ?? 'depot';
        $montant = (float)($_POST['montant'] ?? 0);
        $description = $_POST['description'] ?? '';
        $dateTransaction = $_POST['date_transaction'] ?? date('Y-m-d');
        if (!$accountId || $montant <= 0) { echo json_encode(['error' => 'Données invalides']); exit; }
        $txId = \App\Models\Account::addTransaction($accountId, $type, $montant, $description, $dateTransaction);
        // Ajuster solde
        $signe = in_array($type, ['depot', 'virement_in']) ? 1 : -1;
        \App\Models\Account::adjustSolde($accountId, $montant * $signe);
        echo json_encode(['success' => true, 'id' => $txId]);
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        \App\Models\Account::delete($id);
        echo json_encode(['success' => true]);
        break;

    case 'delete_transaction':
        $id = (int)($_POST['id'] ?? 0);
        \App\Models\Account::deleteTransaction($id);
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['error' => 'Action inconnue']);
}
