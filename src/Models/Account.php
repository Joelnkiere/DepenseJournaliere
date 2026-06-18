<?php
namespace App\Models;

use Config\Database;
use PDO;

class Account
{
    public static function create(int $userId, string $nom, string $type, float $soldeInitial, string $devise = 'EUR', string $couleur = '#0d6efd', string $icone = 'wallet2'): int
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            INSERT INTO accounts (user_id, nom, type, solde_initial, solde_actuel, devise, couleur, icone)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$userId, $nom, $type, $soldeInitial, $soldeInitial, $devise, $couleur, $icone]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, string $nom, string $type, string $couleur): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE accounts SET nom = ?, type = ?, couleur = ? WHERE id = ?');
        return $stmt->execute([$nom, $type, $couleur, $id]);
    }

    public static function delete(int $id): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('DELETE FROM accounts WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function getById(int $id): ?array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM accounts WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function getByUser(int $userId): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM accounts WHERE user_id = ? ORDER BY type, nom');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function updateSolde(int $id, float $newSolde): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE accounts SET solde_actuel = ? WHERE id = ?');
        return $stmt->execute([$newSolde, $id]);
    }

    public static function adjustSolde(int $id, float $montant): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE accounts SET solde_actuel = solde_actuel + ? WHERE id = ?');
        return $stmt->execute([$montant, $id]);
    }

    public static function getTotalByUser(int $userId): float
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT COALESCE(SUM(solde_actuel), 0) as total FROM accounts WHERE user_id = ?');
        $stmt->execute([$userId]);
        return (float) $stmt->fetch()['total'];
    }

    public static function getTotalsByType(int $userId): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            SELECT type, SUM(solde_actuel) as total, COUNT(*) as count
            FROM accounts WHERE user_id = ?
            GROUP BY type ORDER BY total DESC
        ');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function addTransaction(int $accountId, string $type, float $montant, string $description, string $date, ?int $destAccountId = null): int
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            INSERT INTO account_transactions (account_id, type, montant, description, date_transaction, destination_account_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$accountId, $type, $montant, $description, $date, $destAccountId]);
        $txId = (int) $db->lastInsertId();

        if ($type === 'depot') {
            self::adjustSolde($accountId, $montant);
        } elseif ($type === 'retrait') {
            self::adjustSolde($accountId, -$montant);
        } elseif ($type === 'virement' && $destAccountId) {
            self::adjustSolde($accountId, -$montant);
            self::adjustSolde($destAccountId, $montant);
        }
        return $txId;
    }

    public static function getTransactions(int $accountId, int $limit = 20): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            SELECT t.*, a.nom as account_nom, d.nom as dest_nom
            FROM account_transactions t
            LEFT JOIN accounts a ON a.id = t.account_id
            LEFT JOIN accounts d ON d.id = t.destination_account_id
            WHERE t.account_id = ?
            ORDER BY t.date_transaction DESC, t.created_at DESC
            LIMIT ?
        ');
        $stmt->execute([$accountId, $limit]);
        return $stmt->fetchAll();
    }

    public static function getAllTransactions(int $userId, int $limit = 30): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            SELECT t.*, a.nom as account_nom
            FROM account_transactions t
            JOIN accounts a ON a.id = t.account_id
            WHERE a.user_id = ?
            ORDER BY t.date_transaction DESC, t.created_at DESC
            LIMIT ?
        ');
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    public static function deleteTransaction(int $id): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('DELETE FROM account_transactions WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function getTotalNetWorth(int $userId): float
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT COALESCE(SUM(solde_actuel), 0) as total FROM accounts WHERE user_id = ?');
        $stmt->execute([$userId]);
        return (float) $stmt->fetch()['total'];
    }

    public static function getAccountTypes(): array
    {
        return [
            'compte_courant' => ['label' => 'Compte courant', 'icone' => 'wallet2', 'couleur' => '#0d6efd'],
            'epargne' => ['label' => 'Livret épargne', 'icone' => 'piggy-bank', 'couleur' => '#198754'],
            'investissement' => ['label' => 'Investissement', 'icone' => 'graph-up-arrow', 'couleur' => '#6f42c1'],
            'immobilier' => ['label' => 'Immobilier', 'icone' => 'house-door', 'couleur' => '#fd7e14'],
            'crypto' => ['label' => 'Cryptomonnaie', 'icone' => 'currency-bitcoin', 'couleur' => '#ffc107'],
            'especes' => ['label' => 'Espèces', 'icone' => 'cash', 'couleur' => '#20c997'],
            'autre' => ['label' => 'Autre', 'icone' => 'bank', 'couleur' => '#adb5bd'],
        ];
    }
}