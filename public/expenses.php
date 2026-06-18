<?php
require_once __DIR__ . '/../config/bootstrap.php';

if (!\App\Core\Session::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = \App\Core\Session::get('user_id');
$mois = $_GET['mois'] ?? date('Y-m');

include __DIR__ . '/../src/Views/expenses/index.php';