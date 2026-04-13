<?php

declare(strict_types=1);

namespace ParkingSystem\Repositories;

use ParkingSystem\Core\Database;
use PDO;

final class VehicleCategoryRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function all(): array
    {
        return $this->db->query('SELECT id, name, description, hourly_rate, created_at, updated_at FROM vehicle_categories ORDER BY id DESC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name, description, hourly_rate, created_at, updated_at FROM vehicle_categories WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function create(array $payload): array
    {
        $stmt = $this->db->prepare(
            'INSERT INTO vehicle_categories (name, description, hourly_rate, created_at, updated_at) VALUES (:name, :description, :hourly_rate, NOW(), NOW())'
        );

        $stmt->execute([
            'name' => trim((string) ($payload['name'] ?? '')),
            'description' => trim((string) ($payload['description'] ?? '')),
            'hourly_rate' => (float) ($payload['hourly_rate'] ?? 0),
        ]);

        return $this->find((int) $this->db->lastInsertId());
    }

    public function update(int $id, array $payload): ?array
    {
        if ($this->find($id) === null) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE vehicle_categories SET name = :name, description = :description, hourly_rate = :hourly_rate, updated_at = NOW() WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'name' => trim((string) ($payload['name'] ?? '')),
            'description' => trim((string) ($payload['description'] ?? '')),
            'hourly_rate' => (float) ($payload['hourly_rate'] ?? 0),
        ]);

        return $this->find($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM vehicle_categories WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }
}
