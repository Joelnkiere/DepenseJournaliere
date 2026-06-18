<?php
require_once __DIR__ . '/../config/bootstrap.php';
if (!\App\Core\Session::isLoggedIn()) { header('Location: login.php'); exit; }
include __DIR__ . '/../src/Views/archive/index.php';