<?php
require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Session;

header('Content-Type: application/json');

if (!Session::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            $categories = \App\Models\Category::getAll();
            echo json_encode($categories);
            break;

        case 'add':
            $nom = trim($_POST['nom'] ?? '');
            $type = $_POST['type'] ?? 'besoin';
            if (empty($nom)) {
                http_response_code(400);
                echo json_encode(['error' => 'Nom requis.']);
                exit;
            }
            $id = \App\Models\Category::create($nom, $type);
            echo json_encode(['success' => true, 'category' => \App\Models\Category::getById($id)]);
            break;

        case 'update':
            $id = (int) ($_POST['id'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $type = $_POST['type'] ?? 'besoin';
            if ($id <= 0 || empty($nom)) {
                http_response_code(400);
                echo json_encode(['error' => 'ID et nom requis.']);
                exit;
            }
            \App\Models\Category::update($id, $nom, $type);
            echo json_encode(['success' => true, 'category' => \App\Models\Category::getById($id)]);
            break;

        case 'delete':
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'ID requis.']);
                exit;
            }
            \App\Models\Category::delete($id);
            echo json_encode(['success' => true]);
            break;

        default:
            echo json_encode(\App\Models\Category::getAll());
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}