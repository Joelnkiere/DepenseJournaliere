<?php
namespace App\Models;

use Config\Database;
use PDO;

class SavingsGoal
{
    public static function create(int $userId, string $titre, float $montantCible, string $dateLimite = null): int
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            INSERT INTO savings_goals (user_id, titre, montant_cible, date_limite)
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([$userId, $titre, $montantCible, $dateLimite]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, string $titre, float $montantCible, string $dateLimite = null): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            UPDATE savings_goals SET titre = ?, montant_cible = ?, date_limite = ?
            WHERE id = ?
        ');
        return $stmt->execute([$titre, $montantCible, $dateLimite, $id]);
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
        $stmt = $db->prepare('SELECT * FROM savings_goals WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function getByUser(int $userId): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM savings_goals WHERE user_id = ? ORDER BY date_limite ASC');
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
}