<?php

declare(strict_types=1);

namespace ParkingSystem\Repositories;

use ParkingSystem\Core\Database;
use PDO;

final class AdminRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name, username, email, phone, password, created_at, updated_at FROM admins WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name, username, email, phone, password, created_at, updated_at FROM admins WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);

        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name, username, email, phone, password, created_at, updated_at FROM admins WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);

        return $stmt->fetch() ?: null;
    }

    public function updateProfile(int $id, array $payload): array
    {
        $stmt = $this->db->prepare(
            'UPDATE admins SET name = :name, email = :email, phone = :phone, updated_at = NOW() WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'name' => trim((string) ($payload['name'] ?? '')),
            'email' => trim((string) ($payload['email'] ?? '')),
            'phone' => trim((string) ($payload['phone'] ?? '')),
        ]);

        $admin = $this->findById($id);
        unset($admin['password']);

        return $admin;
    }

    public function updatePassword(int $id, string $password): void
    {
        $stmt = $this->db->prepare(
            'UPDATE admins SET password = :password, updated_at = NOW() WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }
}
