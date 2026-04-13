<?php

declare(strict_types=1);

namespace ParkingSystem\Repositories;

use ParkingSystem\Core\Database;
use PDO;

final class ParkingSlotRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function all(): array
    {
        return $this->db->query('SELECT id, slot_number, lane_name, status, remarks, created_at, updated_at FROM parking_slots ORDER BY slot_number ASC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, slot_number, lane_name, status, remarks, created_at, updated_at FROM parking_slots WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function create(array $payload): array
    {
        $stmt = $this->db->prepare(
            'INSERT INTO parking_slots (slot_number, lane_name, status, remarks, created_at, updated_at) VALUES (:slot_number, :lane_name, :status, :remarks, NOW(), NOW())'
        );

        $stmt->execute([
            'slot_number' => trim((string) ($payload['slot_number'] ?? '')),
            'lane_name' => trim((string) ($payload['lane_name'] ?? '')),
            'status' => trim((string) ($payload['status'] ?? 'AVAILABLE')) ?: 'AVAILABLE',
            'remarks' => trim((string) ($payload['remarks'] ?? '')),
        ]);

        return $this->find((int) $this->db->lastInsertId());
    }

    public function update(int $id, array $payload): ?array
    {
        if ($this->find($id) === null) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE parking_slots SET slot_number = :slot_number, lane_name = :lane_name, status = :status, remarks = :remarks, updated_at = NOW() WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'slot_number' => trim((string) ($payload['slot_number'] ?? '')),
            'lane_name' => trim((string) ($payload['lane_name'] ?? '')),
            'status' => trim((string) ($payload['status'] ?? 'AVAILABLE')) ?: 'AVAILABLE',
            'remarks' => trim((string) ($payload['remarks'] ?? '')),
        ]);

        return $this->find($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM parking_slots WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    public function occupy(?int $id): void
    {
        if ($id === null) {
            return;
        }

        $stmt = $this->db->prepare('UPDATE parking_slots SET status = :status, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'status' => 'OCCUPIED',
        ]);
    }

    public function release(?int $id): void
    {
        if ($id === null) {
            return;
        }

        $stmt = $this->db->prepare('UPDATE parking_slots SET status = :status, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'status' => 'AVAILABLE',
        ]);
    }
}
