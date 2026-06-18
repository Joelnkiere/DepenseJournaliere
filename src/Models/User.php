<?php
namespace App\Models;

use Config\Database;
use PDO;

class User
{
    public static function findById(int $id): ?array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT id, nom, email, theme, created_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function getTheme(int $id): string
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT theme FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row['theme'] ?? 'dark';
    }

    public static function updateTheme(int $id, string $theme): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE users SET theme = ? WHERE id = ?');
        return $stmt->execute([$theme, $id]);
    }

    public static function setResetToken(string $email, string $token, DateTime $expires): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?');
        return $stmt->execute([$token, $expires->format('Y-m-d H:i:s'), $email]);
    }

    public static function findByResetToken(string $token): ?array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()');
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    public static function clearResetToken(int $id): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE users SET reset_token = NULL, reset_expires = NULL WHERE id = ?');
        return $stmt->execute([$id]);
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