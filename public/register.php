<?php
require_once __DIR__ . '/../config/bootstrap.php';

$controller = new \App\Controllers\AuthController();
$controller->register();