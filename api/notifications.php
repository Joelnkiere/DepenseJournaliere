<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Core\Session;
header('Content-Type: application/json');
if (!Session::isLoggedIn()) { http_response_code(401); echo json_encode(['error'=>'Non authentifié']); exit; }

$userId = Session::get('user_id');
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            $nonLu = !empty($_GET['unread']);
            echo json_encode(\App\Models\Notification::getByUser($userId, $nonLu));
            break;
        case 'mark_read':
            $id = (int)($_POST['id']??0);
            \App\Models\Notification::markAsRead($id, $userId);
            echo json_encode(['success'=>true]);
            break;
        case 'mark_all_read':
            \App\Models\Notification::markAllAsRead($userId);
            echo json_encode(['success'=>true]);
            break;
        case 'count_unread':
            echo json_encode(['count'=>\App\Models\Notification::countUnread($userId)]);
            break;
        default: http_response_code(400); echo json_encode(['error'=>'Action inconnue']);
    }
} catch (Exception $e) { http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }