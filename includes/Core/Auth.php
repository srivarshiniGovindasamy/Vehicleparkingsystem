<?php

declare(strict_types=1);

namespace ParkingSystem\Core;

use ParkingSystem\Repositories\AdminRepository;

final class Auth
{
    public static function id(): ?int
    {
        return isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null;
    }

    public static function user(): ?array
    {
        $id = self::id();
        if ($id === null) {
            return null;
        }

        return (new AdminRepository())->findById($id);
    }

    public static function attempt(string $username, string $password): ?array
    {
        $repository = new AdminRepository();
        $admin = $repository->findByUsername($username);

        if ($admin === null || !password_verify($password, $admin['password'])) {
            return null;
        }

        $_SESSION['admin_id'] = (int) $admin['id'];
        unset($admin['password']);

        return $admin;
    }

    public static function requireAdmin(): array
    {
        $user = self::user();
        if ($user === null) {
            Response::error('Authentication required.', 401);
        }

        unset($user['password']);

        return $user;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (session_id() !== '') {
            session_destroy();
        }
    }
}
