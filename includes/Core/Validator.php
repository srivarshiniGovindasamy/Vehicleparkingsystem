<?php

declare(strict_types=1);

namespace ParkingSystem\Core;

final class Validator
{
    public static function required(array $data, array $fields): array
    {
        $errors = [];

        foreach ($fields as $field) {
            $value = $data[$field] ?? null;
            if ($value === null || (is_string($value) && trim($value) === '')) {
                $errors[$field][] = 'This field is required.';
            }
        }

        return $errors;
    }
}
