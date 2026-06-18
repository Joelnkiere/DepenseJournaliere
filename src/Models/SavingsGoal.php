<?php
namespace App\Models;

use Config\Database;
use PDO;

class SavingsGoal
{
    public static function create(int $userId, string $titre, float $montantCible, ?string $dateLimite = null, ?int $accountId = null, string $autoSaveType = 'none', float $autoSaveValue = 0, ?string $autoSaveFrequence = null): int
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            INSERT INTO savings_goals (user_id, account_id, titre, montant_cible, date_limite, auto_save_type, auto_save_value, auto_save_frequence)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$userId, $accountId, $titre, $montantCible, $dateLimite, $autoSaveType, $autoSaveValue, $autoSaveFrequence]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, string $titre, float $montantCible, ?string $dateLimite = null, ?int $accountId = null, string $autoSaveType = 'none', float $autoSaveValue = 0, ?string $autoSaveFrequence = null): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            UPDATE savings_goals SET titre = ?, montant_cible = ?, date_limite = ?,
                account_id = ?, auto_save_type = ?, auto_save_value = ?, auto_save_frequence = ?
            WHERE id = ?
        ');
        return $stmt->execute([$titre, $montantCible, $dateLimite, $accountId, $autoSaveType, $autoSaveValue, $autoSaveFrequence, $id]);
    }

    public static function delete(int $id): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('DELETE FROM savings_goals WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function getById(int $id): ?array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            SELECT g.*, a.nom as account_nom, a.solde_actuel as account_solde
            FROM savings_goals g
            LEFT JOIN accounts a ON a.id = g.account_id
            WHERE g.id = ?
        ');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function getByUser(int $userId): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            SELECT g.*, a.nom as account_nom, a.solde_actuel as account_solde
            FROM savings_goals g
            LEFT JOIN accounts a ON a.id = g.account_id
            WHERE g.user_id = ?
            ORDER BY g.date_limite ASC
        ');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function addFunds(int $id, float $montant): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE savings_goals SET montant_actuel = montant_actuel + ? WHERE id = ?');
        return $stmt->execute([$montant, $id]);
    }

    public static function getTotalEpargne(int $userId): float
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT COALESCE(SUM(montant_actuel), 0) as total FROM savings_goals WHERE user_id = ?');
        $stmt->execute([$userId]);
        return (float) $stmt->fetch()['total'];
    }

    public static function processAutoSave(int $userId, float $revenuMois): array
    {
        $goals = self::getByUser($userId);
        $processed = [];
        foreach ($goals as $goal) {
            if ($goal['auto_save_type'] === 'none' || (float)$goal['auto_save_value'] <= 0) continue;
            $montant = 0;
            if ($goal['auto_save_type'] === 'percentage') {
                $montant = round($revenuMois * ((float)$goal['auto_save_value'] / 100), 2);
            } elseif ($goal['auto_save_type'] === 'fixed') {
                $montant = (float)$goal['auto_save_value'];
            }
            if ($montant > 0) {
                self::addFunds($goal['id'], $montant);
                if ($goal['account_id']) {
                    Account::adjustSolde($goal['account_id'], -$montant);
                }
                $processed[] = ['goal' => $goal['titre'], 'montant' => $montant];
            }
        }
        return $processed;
    }
}