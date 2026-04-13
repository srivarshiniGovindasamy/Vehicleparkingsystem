<?php

declare(strict_types=1);

namespace ParkingSystem\Repositories;

use ParkingSystem\Core\Database;
use PDO;

final class PasswordResetRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function createToken(int $adminId): string
    {
        $token = bin2hex(random_bytes(24));
        $stmt = $this->db->prepare(
            'INSERT INTO password_resets (admin_id, token, expires_at, created_at, used_at) VALUES (:admin_id, :token, DATE_ADD(NOW(), INTERVAL 30 MINUTE), NOW(), NULL)'
        );

        $stmt->execute([
            'admin_id' => $adminId,
            'token' => $token,
        ]);

        return $token;
    }

    public function findValidToken(string $token): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, admin_id, token, expires_at, used_at FROM password_resets WHERE token = :token AND used_at IS NULL AND expires_at >= NOW() LIMIT 1'
        );
        $stmt->execute(['token' => $token]);

        return $stmt->fetch() ?: null;
    }

    public function markUsed(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
