<?php
require_once __DIR__ . '/../config/bootstrap.php';
\App\Core\Session::startIfNot();
if (!\App\Core\Session::get('user_id')) {
    header('Location: login.php'); exit;
}
include __DIR__ . '/../src/Views/revenus/index.php';