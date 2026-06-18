<?php
namespace App\Models;

use Config\Database;
use PDO;

class Revenue
{
    public static function create(int $userId, int $categoryId, float $montant, ?string $source = null, string $dateRevenue = '', ?int $budgetId = null, ?int $accountId = null, bool $estRecurrent = false, ?string $frequence = null, string $description = ''): int
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            INSERT INTO revenues (user_id, category_id, budget_id, account_id, montant, description, date_revenu, est_recurrent, frequence, source)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$userId, $categoryId, $budgetId, $accountId, $montant, $description, $dateRevenue ?: date('Y-m-d'), $estRecurrent ? 1 : 0, $frequence, $source]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, float $montant, ?string $source = null, ?int $categoryId = null, string $dateRevenue = '', string $description = ''): bool
    {
        $db = Database::getInstance()->getConnection();
        $fields = [];
        $params = [];
        if ($montant) { $fields[] = 'montant = ?'; $params[] = $montant; }
        if ($source !== null) { $fields[] = 'source = ?'; $params[] = $source; }
        if ($categoryId) { $fields[] = 'category_id = ?'; $params[] = $categoryId; }
        if ($dateRevenue) { $fields[] = 'date_revenu = ?'; $params[] = $dateRevenue; }
        if ($description) { $fields[] = 'description = ?'; $params[] = $description; }
        if (empty($fields)) return false;
        $params[] = $id;
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE revenues SET ' . implode(', ', $fields) . ' WHERE id = ?');
        return $stmt->execute($params);
    }

    public static function delete(int $id): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('DELETE FROM revenues WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function getById(int $id): ?array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            SELECT r.*, rc.nom as category_nom, rc.type as category_type
            FROM revenues r
            JOIN revenue_categories rc ON rc.id = r.category_id
            WHERE r.id = ?
        ');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function getByUser(int $userId, ?int $budgetId = null): array
    {
        $db = Database::getInstance()->getConnection();
        $sql = '
            SELECT r.*, rc.nom as category_nom, rc.type as category_type
            FROM revenues r
            JOIN revenue_categories rc ON rc.id = r.category_id
            WHERE r.user_id = ?
        ';
        $params = [$userId];
        if ($budgetId) {
            $sql .= ' AND (r.budget_id = ? OR r.budget_id IS NULL)';
            $params[] = $budgetId;
        }
        $sql .= ' ORDER BY r.date_revenu DESC';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getTotalByBudget(int $budgetId): float
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT COALESCE(SUM(montant), 0) as total FROM revenues WHERE budget_id = ?');
        $stmt->execute([$budgetId]);
        return (float) $stmt->fetch()['total'];
    }

    public static function getTotalByDateRange(int $userId, string $startDate, string $endDate): float
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT COALESCE(SUM(montant), 0) as total FROM revenues WHERE user_id = ? AND date_revenu BETWEEN ? AND ?');
        $stmt->execute([$userId, $startDate, $endDate]);
        return (float) $stmt->fetch()['total'];
    }

    public static function getCategoryTotals(int $userId, string $startDate, string $endDate): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            SELECT rc.id, rc.nom, rc.type, SUM(r.montant) as total
            FROM revenues r
            JOIN revenue_categories rc ON rc.id = r.category_id
            WHERE r.user_id = ? AND r.date_revenu BETWEEN ? AND ?
            GROUP BY rc.id, rc.nom, rc.type
            ORDER BY total DESC
        ');
        $stmt->execute([$userId, $startDate, $endDate]);
        return $stmt->fetchAll();
    }

    public static function getYearlyTotal(int $userId): float
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(
                CASE
                    WHEN frequence = 'mensuel' THEN montant * 12
                    WHEN frequence = 'trimestriel' THEN montant * 4
                    WHEN frequence = 'annuel' THEN montant
                    ELSE montant
                END
            ), 0) as total
            FROM revenues
            WHERE user_id = ? AND est_recurrent = 1
        ");
        $stmt->execute([$userId]);
        $recurrent = (float) $stmt->fetch()['total'];
        // Ajouter les revenus non récurrents de l'année en cours
        $stmt2 = $db->prepare("
            SELECT COALESCE(SUM(montant), 0) as total
            FROM revenues
            WHERE user_id = ? AND est_recurrent = 0 AND YEAR(date_revenu) = YEAR(CURDATE())
        ");
        $stmt2->execute([$userId]);
        $nonRecurrent = (float) $stmt2->fetch()['total'];
        return $recurrent + $nonRecurrent;
    }

    public static function getMonthlyRecurring(int $userId): float
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(
                CASE
                    WHEN frequence = 'mensuel' THEN montant
                    WHEN frequence = 'trimestriel' THEN montant / 3
                    WHEN frequence = 'annuel' THEN montant / 12
                    ELSE montant
                END
            ), 0) as total
            FROM revenues
            WHERE user_id = ? AND est_recurrent = 1
        ");
        $stmt->execute([$userId]);
        return (float) $stmt->fetch()['total'];
    }

    public static function getRevenueCategories(): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query('SELECT * FROM revenue_categories ORDER BY type, nom');
        return $stmt->fetchAll();
    }
}