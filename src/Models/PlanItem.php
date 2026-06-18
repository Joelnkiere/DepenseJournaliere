<?php
namespace App\Models;

use Config\Database;
use PDO;

class PlanItem
{
    public static function create(int $planId, string $type, string $label, float $montant, int $priorite = 5, ?int $goalId = null): int
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('INSERT INTO plan_items (plan_id, type, goal_id, label, montant, priorite) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$planId, $type, $goalId, $label, $montant, $priorite]);
        return (int) $db->lastInsertId();
    }

    public static function getByPlan(int $planId): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM plan_items WHERE plan_id = ? ORDER BY priorite ASC');
        $stmt->execute([$planId]);
        return $stmt->fetchAll();
    }

    public static function markExecuted(int $id): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE plan_items SET execute = 1, execute_at = NOW() WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
