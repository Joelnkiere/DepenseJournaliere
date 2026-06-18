<?php
namespace App\Models;

use Config\Database;
use PDO;

class RecurringTransaction
{
    public static function create(int $userId, int $categoryId, float $montant, string $description, string $frequence, int $jourExecution, ?string $prochaineExecution = null): int
    {
        $db = Database::getInstance()->getConnection();
        if (!$prochaineExecution) {
            $prochaineExecution = date('Y-m-d', strtotime("+{$jourExecution} days"));
        }
        $stmt = $db->prepare('
            INSERT INTO recurring_transactions (user_id, category_id, montant, description, frequence, jour_execution, prochaine_execution)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$userId, $categoryId, $montant, $description, $frequence, $jourExecution, $prochaineExecution]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, int $categoryId, float $montant, string $description, string $frequence, int $jourExecution, bool $actif): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            UPDATE recurring_transactions SET category_id = ?, montant = ?, description = ?, frequence = ?, jour_execution = ?, actif = ?
            WHERE id = ?
        ');
        return $stmt->execute([$categoryId, $montant, $description, $frequence, $jourExecution, $actif ? 1 : 0, $id]);
    }

    public static function delete(int $id): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('DELETE FROM recurring_transactions WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function getById(int $id): ?array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            SELECT r.*, c.nom as category_nom FROM recurring_transactions r
            JOIN categories c ON c.id = r.category_id WHERE r.id = ?
        ');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function getByUser(int $userId): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            SELECT r.*, c.nom as category_nom FROM recurring_transactions r
            JOIN categories c ON c.id = r.category_id
            WHERE r.user_id = ? ORDER BY r.prochaine_execution ASC
        ');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function getDueTransactions(string $date): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            SELECT r.*, c.nom as category_nom, mb.id as budget_id
            FROM recurring_transactions r
            JOIN categories c ON c.id = r.category_id
            JOIN monthly_budgets mb ON mb.user_id = r.user_id AND mb.mois = ?
            WHERE r.actif = 1 AND r.prochaine_execution <= ? AND mb.cloture = 0
        ');
        $mois = substr($date, 0, 7);
        $stmt->execute([$mois, $date]);
        return $stmt->fetchAll();
    }

    public static function updateNextExecution(int $id, string $frequence): void
    {
        $db = Database::getInstance()->getConnection();
        $add = match ($frequence) {
            'mensuel' => '+1 month',
            'bimestriel' => '+2 months',
            'trimestriel' => '+3 months',
            'annuel' => '+1 year',
            default => '+1 month',
        };
        $stmt = $db->prepare('UPDATE recurring_transactions SET prochaine_execution = DATE_ADD(CURDATE(), INTERVAL 1 DAY) WHERE id = ?');
        $stmt->execute([$id]);
    }
}