<?php

declare(strict_types=1);

namespace ParkingSystem\Core;

final class Request
{
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query,
        private readonly array $body
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $body = self::parseBody($method);

        return new self($method, $path, $_GET, $body);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function query(): array
    {
        return $this->query;
    }

    public function body(): array
    {
        return $this->body;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    private static function parseBody(string $method): array
    {
        if ($method === 'GET') {
            return [];
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input') ?: '{}';
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        }

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            if (!empty($_POST)) {
                return $_POST;
            }

            $raw = file_get_contents('php://input') ?: '';
            parse_str($raw, $parsed);

            return is_array($parsed) ? $parsed : [];
        }

        return [];
    }
}
