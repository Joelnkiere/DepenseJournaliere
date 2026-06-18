<?php
namespace App\Models;

use Config\Database;
use PDO;

class BudgetTemplate
{
    public static function create(int $userId, string $nom, string $description = null): int
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('INSERT INTO budget_templates (user_id, nom, description) VALUES (?, ?, ?)');
        $stmt->execute([$userId, $nom, $description]);
        return (int) $db->lastInsertId();
    }

    public static function delete(int $id): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('DELETE FROM budget_templates WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function getById(int $id): ?array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM budget_templates WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function getByUser(int $userId): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM budget_templates WHERE user_id = ? ORDER BY nom');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function setItem(int $templateId, int $categoryId, float $montant): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            INSERT INTO budget_template_items (template_id, category_id, montant_prevu)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE montant_prevu = VALUES(montant_prevu)
        ');
        return $stmt->execute([$templateId, $categoryId, $montant]);
    }

    public static function getItems(int $templateId): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('
            SELECT ti.*, c.nom as category_nom, c.type as category_type
            FROM budget_template_items ti
            JOIN categories c ON c.id = ti.category_id
            WHERE ti.template_id = ?
        ');
        $stmt->execute([$templateId]);
        return $stmt->fetchAll();
    }

    public static function applyToBudget(int $templateId, int $budgetId): bool
    {
        $items = self::getItems($templateId);
        foreach ($items as $item) {
            Budget::setPrevision($budgetId, $item['category_id'], $item['montant_prevu']);
        }
        return true;
    }

    public static function saveFromBudget(int $userId, string $nom, string $description, int $budgetId): int
    {
        $id = self::create($userId, $nom, $description);
        $previsions = Budget::getPrevisions($budgetId);
        foreach ($previsions as $p) {
            if ((float)$p['montant_prevu'] > 0) {
                self::setItem($id, $p['category_id'], $p['montant_prevu']);
            }
        }
        return $id;
    }
}