<?php
namespace App\Models;

use Config\Database;
use PDO;

class BudgetPeriod
{
    public static function create(int $userId, string $type, string $startDate, string $endDate, ?string $nom = null): int
    {
        $db = Database::getInstance()->getConnection();
        if (!$nom) {
            $nom = match($type) {
                'daily' => "Journée du {$startDate}",
                'weekly' => "Semaine du {$startDate}",
                'monthly' => substr($startDate, 0, 7),
                'yearly' => substr($startDate, 0, 4),
                default => "Période {$startDate} - {$endDate}",
            };
        }
        $stmt = $db->prepare('INSERT INTO budget_periods (user_id, nom, type, start_date, end_date) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $nom, $type, $startDate, $endDate]);
        return (int) $db->lastInsertId();
    }

    public static function getActive(int $userId): ?array
    {
        $today = date('Y-m-d');
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            SELECT * FROM budget_periods
            WHERE user_id = ? AND start_date <= ? AND end_date >= ? AND cloture = 0
            ORDER BY start_date DESC LIMIT 1
        ');
        $stmt->execute([$userId, $today, $today]);
        return $stmt->fetch() ?: null;
    }

    public static function getOrCreateActive(int $userId): array
    {
        $active = self::getActive($userId);
        if ($active) return $active;

        $today = date('Y-m-d');
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM budget_periods WHERE user_id = ? ORDER BY end_date DESC LIMIT 1');
        $stmt->execute([$userId]);
        $last = $stmt->fetch();

        if ($last && $last['end_date'] >= $today) {
            return $last;
        }

        // Default: monthly period
        $start = date('Y-m-01');
        $end = date('Y-m-t');
        $id = self::create($userId, 'monthly', $start, $end);
        return self::getById($id);
    }

    public static function getById(int $id): ?array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM budget_periods WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function getAllByUser(int $userId): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM budget_periods WHERE user_id = ? ORDER BY start_date DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function updateRevenue(int $id, float $revenu): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE budget_periods SET revenu_total = ? WHERE id = ?');
        return $stmt->execute([$revenu, $id]);
    }

    public static function cloturer(int $id): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE budget_periods SET cloture = 1 WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function delete(int $id): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('DELETE FROM budget_periods WHERE id = ?');
        return $stmt->execute([$id]);
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

    public static function getDaysElapsed(int $budgetId): int
    {
        $period = self::getById($budgetId);
        if (!$period) return 0;
        $start = new \DateTime($period['start_date']);
        $end = new \DateTime($period['end_date']);
        $now = new \DateTime();
        if ($now < $start) return 0;
        if ($now > $end) return (int) $start->diff($end)->days + 1;
        return (int) $start->diff($now)->days + 1;
    }

    public static function getTotalDays(int $budgetId): int
    {
        $period = self::getById($budgetId);
        if (!$period) return 1;
        $start = new \DateTime($period['start_date']);
        $end = new \DateTime($period['end_date']);
        return (int) $start->diff($end)->days + 1;
    }

    public static function createForCurrent(int $userId, string $type, ?string $customStart = null, ?string $customEnd = null): array
    {
        $today = new \DateTime();
        $start = clone $today;
        $end = clone $today;

        switch ($type) {
            case 'daily':
                break;
            case 'weekly':
                $start->modify('monday this week');
                $end->modify('sunday this week');
                break;
            case 'monthly':
                $start->modify('first day of this month');
                $end->modify('last day of this month');
                break;
            case 'yearly':
                $start->modify('first day of january');
                $end->modify('last day of december');
                break;
            case 'custom':
                $start = new \DateTime($customStart ?? $today->format('Y-m-d'));
                $end = new \DateTime($customEnd ?? $today->modify('+30 days')->format('Y-m-d'));
                break;
        }

        $id = self::create($userId, $type, $start->format('Y-m-d'), $end->format('Y-m-d'));
        return self::getById($id);
    }
}