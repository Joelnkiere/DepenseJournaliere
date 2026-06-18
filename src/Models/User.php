<?php
namespace App\Models;

use Config\Database;
use PDO;

class User
{
    public static function findById(int $id): ?array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT id, nom, email, created_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public static function create(string $nom, string $email, string $password): int
    {
        $db = Database::getInstance()->getConnection();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare('INSERT INTO users (nom, email, password) VALUES (?, ?, ?)');
        $stmt->execute([$nom, $email, $hash]);
        return (int) $db->lastInsertId();
    }

    public static function verifyPassword(string $email, string $password): ?array
    {
        $user = self::findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return null;
    }

    public static function updateProfile(int $id, string $nom, string $email): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE users SET nom = ?, email = ? WHERE id = ?');
        return $stmt->execute([$nom, $email, $id]);
    }

    public static function updatePassword(int $id, string $newPassword): bool
    {
        $db = Database::getInstance()->getConnection();
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
        return $stmt->execute([$hash, $id]);
    }
}