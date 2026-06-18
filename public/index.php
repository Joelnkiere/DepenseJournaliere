<?php
require_once __DIR__ . '/../config/bootstrap.php';

if (!\App\Core\Session::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$controller = new \App\Controllers\DashboardController();
$controller->index();