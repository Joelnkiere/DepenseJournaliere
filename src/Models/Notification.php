<?php
namespace App\Models;

use Config\Database;
use PDO;

class Notification
{
    public static function create(int $userId, string $type, string $titre, string $message): int
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('INSERT INTO notifications (user_id, type, titre, message) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $type, $titre, $message]);
        return (int) $db->lastInsertId();
    }

    public static function getByUser(int $userId, bool $nonLu = false, int $limit = 20): array
    {
        $db = Database::getInstance()->getConnection();
        $sql = 'SELECT * FROM notifications WHERE user_id = ?';
        $params = [$userId];
        if ($nonLu) {
            $sql .= ' AND lu = 0';
        }
        $sql .= ' ORDER BY created_at DESC LIMIT ?';
        $params[] = $limit;
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function markAsRead(int $id, int $userId): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE notifications SET lu = 1 WHERE id = ? AND user_id = ?');
        return $stmt->execute([$id, $userId]);
    }

    public static function markAllAsRead(int $userId): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE notifications SET lu = 1 WHERE user_id = ? AND lu = 0');
        return $stmt->execute([$userId]);
    }

    public static function countUnread(int $userId): int
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND lu = 0');
        $stmt->execute([$userId]);
        return (int) $stmt->fetch()['cnt'];
    }

    public static function deleteOld(int $days = 90): int
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)');
        $stmt->execute([$days]);
        return $stmt->rowCount();
    }
}