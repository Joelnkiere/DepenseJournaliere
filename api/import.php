<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Core\Session;
header('Content-Type: application/json');
if (!Session::isLoggedIn()) { http_response_code(401); echo json_encode(['error'=>'Non authentifié']); exit; }

$userId = Session::get('user_id');
$action = $_GET['action'] ?? '';

if ($action !== 'csv') { http_response_code(400); echo json_encode(['error'=>'Action inconnue']); exit; }

try {
    if (empty($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400); echo json_encode(['error'=>'Fichier requis']); exit;
    }
    $tmp = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($tmp, 'r');
    if (!$handle) { http_response_code(400); echo json_encode(['error'=>'Impossible de lire le fichier']); exit; }

    $colDate = (int)($_POST['col_date']??0);
    $colDesc = (int)($_POST['col_desc']??1);
    $colMontant = (int)($_POST['col_montant']??2);
    $defaultCategory = (int)($_POST['default_category']??1);
    $mois = $_POST['mois']??date('Y-m');

    $budget = \App\Models\Budget::getOrCreate($userId, $mois);
    $imported = 0; $skipped = 0;
    $first = true;

    while (($row = fgetcsv($handle)) !== false) {
        if ($first) { $first = false; continue; }
        if (count($row) <= max($colDate, $colDesc, $colMontant)) { $skipped++; continue; }

        $date = trim($row[$colDate]??'');
        $desc = trim($row[$colDesc]??'');
        $montantStr = trim(str_replace(['€', ' '], '', $row[$colMontant]??'0'));
        $montant = (float) str_replace(',', '.', $montantStr);

        if (empty($date) || $montant == 0) { $skipped++; continue; }

        if ($montant > 0) { $montant = -$montant; }

        $dateFormatted = date('Y-m-d', strtotime($date));
        if (!$dateFormatted) { $skipped++; continue; }

        \App\Models\Expense::create($budget['id'], $defaultCategory, abs($montant), $desc, $dateFormatted, false);
        $imported++;
    }
    fclose($handle);

    echo json_encode(['success'=>true, 'message'=>"Import terminé", 'imported'=>$imported, 'skipped'=>$skipped]);
} catch (Exception $e) {
    http_response_code(500); echo json_encode(['error'=>$e->getMessage()]);
}