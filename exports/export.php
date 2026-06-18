<?php
require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Session;

if (!Session::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = Session::get('user_id');
$mois = $_GET['mois'] ?? date('Y-m');
$format = $_GET['format'] ?? 'csv';
$budget = \App\Models\Budget::getCurrent($userId, $mois);

if (!$budget) {
    die('Aucun budget pour ce mois.');
}

$expenses = \App\Models\Expense::getByBudget($budget['id'], false);

$data = [];
$headers = ['Date', 'Catégorie', 'Type', 'Montant', 'Description'];

foreach ($expenses as $e) {
    $data[] = [
        'Date' => $e['date_depense'],
        'Catégorie' => $e['category_nom'],
        'Type' => $e['category_type'],
        'Montant' => number_format($e['montant'], 2, ',', ' ') . '€',
        'Description' => $e['description'] ?? '',
    ];
}

$data[] = ['', '', '', '', ''];
$data[] = ['TOTAL', '', '', number_format(\App\Models\Expense::getTotalByBudget($budget['id']), 2, ',', ' ') . '€', ''];

if ($format === 'csv') {
    \App\Helpers\ExportHelper::generateCSV($headers, $data, "budget_{$mois}");
} else {
    \App\Helpers\ExportHelper::generatePDF("Bilan budgétaire - {$mois}", $data, "budget_{$mois}");
}