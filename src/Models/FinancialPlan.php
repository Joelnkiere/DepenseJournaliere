<?php
namespace App\Models;

use Config\Database;
use PDO;

class FinancialPlan
{
    public static function create(int $userId, ?int $periodId, string $typePlanner, array $planData): int
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('INSERT INTO financial_plans (user_id, period_id, type_planner, plan_data) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $periodId, $typePlanner, json_encode($planData)]);
        return (int) $db->lastInsertId();
    }

    public static function getActive(int $userId): ?array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM financial_plans WHERE user_id = ? AND actif = 1 ORDER BY created_at DESC LIMIT 1');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        if ($row) $row['plan_data'] = json_decode($row['plan_data'], true);
        return $row ?: null;
    }

    public static function getAllByUser(int $userId, int $limit = 10): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM financial_plans WHERE user_id = ? ORDER BY created_at DESC LIMIT ?');
        $stmt->execute([$userId, $limit]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) $r['plan_data'] = json_decode($r['plan_data'], true);
        return $rows;
    }

    public static function desactivateOthers(int $userId, int $exceptId): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE financial_plans SET actif = 0 WHERE user_id = ? AND id != ?');
        return $stmt->execute([$userId, $exceptId]);
    }
}
