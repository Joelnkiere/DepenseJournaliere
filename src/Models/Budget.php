<?php
namespace App\Models;

use Config\Database;
use PDO;

class Budget
{
    public static function getCurrent(int $userId, string $mois = null): ?array
    {
        $mois = $mois ?? date('Y-m');
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM monthly_budgets WHERE user_id = ? AND mois = ?');
        $stmt->execute([$userId, $mois]);
        return $stmt->fetch() ?: null;
    }

    public static function getOrCreate(int $userId, string $mois): array
    {
        $budget = self::getCurrent($userId, $mois);
        if (!$budget) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare('INSERT INTO monthly_budgets (user_id, mois, revenu_mensuel) VALUES (?, ?, 0)');
            $stmt->execute([$userId, $mois]);
            $budgetId = (int) $db->lastInsertId();
            return [
                'id' => $budgetId,
                'user_id' => $userId,
                'mois' => $mois,
                'revenu_mensuel' => 0,
                'epargne_auto' => 0,
                'cloture' => 0,
            ];
        }
        return $budget;
    }

    public static function updateRevenue(int $budgetId, float $revenu): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE monthly_budgets SET revenu_mensuel = ? WHERE id = ?');
        return $stmt->execute([$revenu, $budgetId]);
    }

    public static function updateEpargneAuto(int $budgetId, float $montant): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE monthly_budgets SET epargne_auto = ? WHERE id = ?');
        return $stmt->execute([$montant, $budgetId]);
    }

    public static function cloturer(int $budgetId): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE monthly_budgets SET cloture = 1 WHERE id = ?');
        return $stmt->execute([$budgetId]);
    }

    public static function getAllByUser(int $userId): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM monthly_budgets WHERE user_id = ? ORDER BY mois DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function setPrevision(int $budgetId, int $categoryId, float $montant): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            INSERT INTO budget_previsions (budget_id, category_id, montant_prevu)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE montant_prevu = VALUES(montant_prevu)
        ');
        return $stmt->execute([$budgetId, $categoryId, $montant]);
    }

    public static function getPrevisions(int $budgetId): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            SELECT bp.*, c.nom as category_nom, c.type as category_type
            FROM budget_previsions bp
            JOIN categories c ON c.id = bp.category_id
            WHERE bp.budget_id = ?
            ORDER BY c.type, c.nom
        ');
        $stmt->execute([$budgetId]);
        return $stmt->fetchAll();
    }

    public static function getComparison(int $userId, string $mois1, string $mois2): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            SELECT b.mois, COALESCE(SUM(e.montant), 0) as total_depense,
                   b.revenu_mensuel, (b.revenu_mensuel - COALESCE(SUM(e.montant), 0)) as reste
            FROM monthly_budgets b
            LEFT JOIN expenses e ON e.budget_id = b.id AND e.est_prevu = 0
            WHERE b.user_id = ? AND b.mois IN (?, ?)
            GROUP BY b.id, b.mois, b.revenu_mensuel
            ORDER BY b.mois
        ');
        $stmt->execute([$userId, $mois1, $mois2]);
        return $stmt->fetchAll();
    }

    public static function getReelByCategory(int $budgetId): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            SELECT e.category_id, c.nom as category_nom, c.type as category_type,
                   SUM(e.montant) as total_reel
            FROM expenses e
            JOIN categories c ON c.id = e.category_id
            WHERE e.budget_id = ? AND e.est_prevu = 0
            GROUP BY e.category_id, c.nom, c.type
        ');
        $stmt->execute([$budgetId]);
        return $stmt->fetchAll();
    }
}