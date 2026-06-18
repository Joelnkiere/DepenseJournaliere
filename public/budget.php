<?php
require_once __DIR__ . '/../config/bootstrap.php';

if (!\App\Core\Session::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = \App\Core\Session::get('user_id');

include __DIR__ . '/../src/Views/budget/index.php';