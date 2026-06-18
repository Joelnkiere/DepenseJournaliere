<?php
namespace App\Models;

use Config\Database;
use PDO;

class Category
{
    public static function getAll(): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query('SELECT * FROM categories ORDER BY type, nom');
        return $stmt->fetchAll();
    }

    public static function getById(int $id): ?array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function getByType(string $type): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM categories WHERE type = ? ORDER BY nom');
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }

    public static function create(string $nom, string $type): int
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('INSERT INTO categories (nom, type) VALUES (?, ?)');
        $stmt->execute([$nom, $type]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, string $nom, string $type): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE categories SET nom = ?, type = ? WHERE id = ?');
        return $stmt->execute([$nom, $type, $id]);
    }

    public static function delete(int $id): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('DELETE FROM categories WHERE id = ?');
        return $stmt->execute([$id]);
    }
}