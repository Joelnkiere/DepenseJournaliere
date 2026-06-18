<?php
namespace App\Models;

use Config\Database;
use PDO;

class Expense
{
    public static function create(int $budgetId, int $categoryId, float $montant, string $description, string $dateDepense, bool $estPrevu = false): int
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            INSERT INTO expenses (budget_id, category_id, montant, description, date_depense, est_prevu)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$budgetId, $categoryId, $montant, $description, $dateDepense, $estPrevu ? 1 : 0]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, int $categoryId, float $montant, string $description, string $dateDepense): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            UPDATE expenses SET category_id = ?, montant = ?, description = ?, date_depense = ?
            WHERE id = ?
        ');
        return $stmt->execute([$categoryId, $montant, $description, $dateDepense, $id]);
    }

    public static function delete(int $id): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('DELETE FROM expenses WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function getById(int $id): ?array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            SELECT e.*, c.nom as category_nom, c.type as category_type
            FROM expenses e
            JOIN categories c ON c.id = e.category_id
            WHERE e.id = ?
        ');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function getByBudget(int $budgetId, bool $estPrevu = false): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            SELECT e.*, c.nom as category_nom, c.type as category_type
            FROM expenses e
            JOIN categories c ON c.id = e.category_id
            WHERE e.budget_id = ? AND e.est_prevu = ?
            ORDER BY e.date_depense DESC, e.created_at DESC
        ');
        $stmt->execute([$budgetId, $estPrevu ? 1 : 0]);
        return $stmt->fetchAll();
    }

    public static function getTotalByBudget(int $budgetId): float
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT COALESCE(SUM(montant), 0) as total FROM expenses WHERE budget_id = ? AND est_prevu = 0');
        $stmt->execute([$budgetId]);
        return (float) $stmt->fetch()['total'];
    }

    public static function getTotalPrevuByBudget(int $budgetId): float
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT COALESCE(SUM(montant), 0) as total FROM expenses WHERE budget_id = ? AND est_prevu = 1');
        $stmt->execute([$budgetId]);
        return (float) $stmt->fetch()['total'];
    }

    public static function getDailyTotals(int $budgetId): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            SELECT date_depense, SUM(montant) as total
            FROM expenses
            WHERE budget_id = ? AND est_prevu = 0
            GROUP BY date_depense
            ORDER BY date_depense
        ');
        $stmt->execute([$budgetId]);
        return $stmt->fetchAll();
    }

    public static function getCategoryTotals(int $budgetId): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            SELECT c.id, c.nom, c.type, SUM(e.montant) as total, COUNT(*) as count
            FROM expenses e
            JOIN categories c ON c.id = e.category_id
            WHERE e.budget_id = ? AND e.est_prevu = 0
            GROUP BY c.id, c.nom, c.type
            ORDER BY total DESC
        ');
        $stmt->execute([$budgetId]);
        return $stmt->fetchAll();
    }
}