<?php

declare(strict_types=1);

namespace ParkingSystem\Repositories;

use ParkingSystem\Core\Database;
use PDO;

final class VehicleRepository
{
    private PDO $db;
    private ParkingSlotRepository $slots;

    public function __construct()
    {
        $this->db = Database::connection();
        $this->slots = new ParkingSlotRepository();
    }

    public function all(): array
    {
        return $this->db->query($this->baseSelect() . ' ORDER BY pr.id DESC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE pr.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function createEntry(array $payload): array
    {
        $stmt = $this->db->prepare(
            'INSERT INTO parking_records (
                receipt_number,
                vehicle_category_id,
                parking_slot_id,
                vehicle_type,
                vehicle_company,
                registration_number,
                owner_name,
                owner_contact,
                status,
                notes,
                entry_time,
                created_at,
                updated_at
            ) VALUES (
                :receipt_number,
                :vehicle_category_id,
                :parking_slot_id,
                :vehicle_type,
                :vehicle_company,
                :registration_number,
                :owner_name,
                :owner_contact,
                :status,
                :notes,
                NOW(),
                NOW(),
                NOW()
            )'
        );

        $receipt = 'VPMS-' . date('YmdHis') . '-' . random_int(100, 999);
        $slotId = isset($payload['parking_slot_id']) && $payload['parking_slot_id'] !== '' ? (int) $payload['parking_slot_id'] : null;

        $stmt->execute([
            'receipt_number' => $receipt,
            'vehicle_category_id' => (int) $payload['vehicle_category_id'],
            'parking_slot_id' => $slotId,
            'vehicle_type' => trim((string) ($payload['vehicle_type'] ?? '')),
            'vehicle_company' => trim((string) ($payload['vehicle_company'] ?? '')),
            'registration_number' => strtoupper(trim((string) ($payload['registration_number'] ?? ''))),
            'owner_name' => trim((string) ($payload['owner_name'] ?? '')),
            'owner_contact' => trim((string) ($payload['owner_contact'] ?? '')),
            'status' => 'IN',
            'notes' => trim((string) ($payload['notes'] ?? '')),
        ]);

        $id = (int) $this->db->lastInsertId();
        $this->slots->occupy($slotId);

        return $this->find($id);
    }

    public function markExit(int $id, array $charge): array
    {
        $record = $this->find($id);
        $stmt = $this->db->prepare(
            'UPDATE parking_records
             SET status = :status,
                 exit_time = NOW(),
                 parked_minutes = :parked_minutes,
                 parked_hours = :parked_hours,
                 parking_charge = :parking_charge,
                 updated_at = NOW()
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'status' => 'EXITED',
            'parked_minutes' => $charge['parked_minutes'],
            'parked_hours' => $charge['parked_hours'],
            'parking_charge' => $charge['parking_charge'],
        ]);

        $this->slots->release($record['parking_slot_id'] !== null ? (int) $record['parking_slot_id'] : null);

        return $this->find($id);
    }

    public function search(string $query): array
    {
        if (trim($query) === '') {
            return [];
        }

        $stmt = $this->db->prepare(
            $this->baseSelect() . ' WHERE
                pr.receipt_number LIKE :search OR
                pr.registration_number LIKE :search OR
                pr.owner_name LIKE :search OR
                pr.owner_contact LIKE :search
             ORDER BY pr.id DESC'
        );

        $stmt->execute(['search' => '%' . trim($query) . '%']);

        return $stmt->fetchAll();
    }

    public function betweenDates(string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            $this->baseSelect() . ' WHERE DATE(pr.entry_time) BETWEEN :from AND :to ORDER BY pr.entry_time DESC'
        );
        $stmt->execute([
            'from' => $from,
            'to' => $to,
        ]);

        return $stmt->fetchAll();
    }

    public function reportSummary(string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                COUNT(*) AS total_records,
                SUM(CASE WHEN status = "IN" THEN 1 ELSE 0 END) AS vehicles_in,
                SUM(CASE WHEN status = "EXITED" THEN 1 ELSE 0 END) AS vehicles_exited,
                COALESCE(SUM(parking_charge), 0) AS revenue
             FROM parking_records
             WHERE DATE(entry_time) BETWEEN :from AND :to'
        );
        $stmt->execute([
            'from' => $from,
            'to' => $to,
        ]);

        return $stmt->fetch() ?: [];
    }

    public function dashboardStats(): array
    {
        $stats = [];

        $stats['total_categories'] = (int) $this->db->query('SELECT COUNT(*) FROM vehicle_categories')->fetchColumn();
        $stats['total_slots'] = (int) $this->db->query('SELECT COUNT(*) FROM parking_slots')->fetchColumn();
        $stats['available_slots'] = (int) $this->db->query("SELECT COUNT(*) FROM parking_slots WHERE status = 'AVAILABLE'")->fetchColumn();
        $stats['occupied_slots'] = (int) $this->db->query("SELECT COUNT(*) FROM parking_slots WHERE status = 'OCCUPIED'")->fetchColumn();
        $stats['vehicles_in'] = (int) $this->db->query("SELECT COUNT(*) FROM parking_records WHERE status = 'IN'")->fetchColumn();
        $stats['vehicles_exited_today'] = (int) $this->db->query("SELECT COUNT(*) FROM parking_records WHERE status = 'EXITED' AND DATE(exit_time) = CURDATE()")->fetchColumn();
        $stats['today_revenue'] = (float) $this->db->query("SELECT COALESCE(SUM(parking_charge), 0) FROM parking_records WHERE status = 'EXITED' AND DATE(exit_time) = CURDATE()")->fetchColumn();

        return $stats;
    }

    private function baseSelect(): string
    {
        return 'SELECT
                pr.id,
                pr.receipt_number,
                pr.vehicle_category_id,
                vc.name AS category_name,
                vc.hourly_rate,
                pr.parking_slot_id,
                ps.slot_number,
                ps.lane_name,
                pr.vehicle_type,
                pr.vehicle_company,
                pr.registration_number,
                pr.owner_name,
                pr.owner_contact,
                pr.status,
                pr.notes,
                pr.entry_time,
                pr.exit_time,
                pr.parked_minutes,
                pr.parked_hours,
                pr.parking_charge,
                pr.created_at,
                pr.updated_at
            FROM parking_records pr
            INNER JOIN vehicle_categories vc ON vc.id = pr.vehicle_category_id
            LEFT JOIN parking_slots ps ON ps.id = pr.parking_slot_id';
    }
}
