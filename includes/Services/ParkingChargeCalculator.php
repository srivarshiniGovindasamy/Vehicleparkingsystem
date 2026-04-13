<?php

declare(strict_types=1);

namespace ParkingSystem\Services;

use DateTimeImmutable;

final class ParkingChargeCalculator
{
    public function calculate(array $record): array
    {
        $entry = new DateTimeImmutable((string) $record['entry_time']);
        $exit = new DateTimeImmutable();
        $minutes = max(1, (int) ceil(($exit->getTimestamp() - $entry->getTimestamp()) / 60));
        $hours = (float) ceil($minutes / 60);
        $rate = (float) $record['hourly_rate'];

        return [
            'parked_minutes' => $minutes,
            'parked_hours' => $hours,
            'parking_charge' => $hours * $rate,
        ];
    }
}
