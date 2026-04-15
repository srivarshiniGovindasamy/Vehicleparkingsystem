<?php

declare(strict_types=1);

namespace ParkingSystem\Core;

use ParkingSystem\Repositories\AdminRepository;
use ParkingSystem\Repositories\ParkingSlotRepository;
use ParkingSystem\Repositories\PasswordResetRepository;
use ParkingSystem\Repositories\VehicleCategoryRepository;
use ParkingSystem\Repositories\VehicleRepository;
use ParkingSystem\Services\ParkingChargeCalculator;

final class ApiController
{
    private AdminRepository $admins;
    private PasswordResetRepository $passwordResets;
    private VehicleCategoryRepository $categories;
    private ParkingSlotRepository $slots;
    private VehicleRepository $vehicles;
    private ParkingChargeCalculator $calculator;

    public function __construct(private readonly Request $request)
    {
        $this->admins = new AdminRepository();
        $this->passwordResets = new PasswordResetRepository();
        $this->categories = new VehicleCategoryRepository();
        $this->slots = new ParkingSlotRepository();
        $this->vehicles = new VehicleRepository();
        $this->calculator = new ParkingChargeCalculator();
    }

    public function dispatch(): void
    {
        if ($this->request->path() === '/') {
            Response::success([
                'name' => 'Vehicle Parking System Backend',
                'version' => '1.0.0',
            ]);
        }

        if (!str_starts_with($this->request->path(), '/api/')) {
            Response::error('Route not found.', 404);
        }

        $method = $this->request->method();
        $path = trim($this->request->path(), '/');
        $segments = explode('/', $path);
        array_shift($segments);

        $resource = $segments[0] ?? '';
        $id = isset($segments[1]) && ctype_digit($segments[1]) ? (int) $segments[1] : null;
        $action = $segments[2] ?? null;

        match ($resource) {
            'auth' => $this->handleAuth($method, $segments[1] ?? null),
            'admin' => $this->handleAdmin($method, $segments[1] ?? null),
            'dashboard' => $this->handleDashboard($method, $segments[1] ?? null),
            'categories' => $this->handleCategories($method, $id),
            'slots' => $this->handleSlots($method, $id),
            'vehicles' => $this->handleVehicles($method, $id, $action, $segments),
            'reports' => $this->handleReports($method, $segments[1] ?? null),
            default => Response::error('Route not found.', 404),
        };
    }

    private function handleAuth(string $method, ?string $action): void
    {
        if ($method === 'POST' && $action === 'login') {
            $errors = Validator::required($this->request->body(), ['username', 'password']);
            if ($errors !== []) {
                Response::error('Validation failed.', 422, $errors);
            }

            $user = Auth::attempt((string) $this->request->input('username'), (string) $this->request->input('password'));
            if ($user === null) {
                Response::error('Invalid username or password.', 401);
            }

            Response::success(['admin' => $user]);
        }

        if ($method === 'POST' && $action === 'logout') {
            Auth::logout();
            Response::success(['message' => 'Logged out successfully.']);
        }

        if ($method === 'POST' && $action === 'forgot-password') {
            $errors = Validator::required($this->request->body(), ['email']);
            if ($errors !== []) {
                Response::error('Validation failed.', 422, $errors);
            }

            $admin = $this->admins->findByEmail((string) $this->request->input('email'));
            if ($admin === null) {
                Response::error('No admin found with that email address.', 404);
            }

            $token = $this->passwordResets->createToken((int) $admin['id']);
            Response::success([
                'message' => 'Password reset token generated.',
                'reset_token' => $token,
            ]);
        }

        if ($method === 'POST' && $action === 'reset-password') {
            $errors = Validator::required($this->request->body(), ['token', 'new_password']);
            if ($errors !== []) {
                Response::error('Validation failed.', 422, $errors);
            }

            $reset = $this->passwordResets->findValidToken((string) $this->request->input('token'));
            if ($reset === null) {
                Response::error('Invalid or expired reset token.', 422);
            }

            $this->admins->updatePassword((int) $reset['admin_id'], (string) $this->request->input('new_password'));
            $this->passwordResets->markUsed((int) $reset['id']);

            Response::success(['message' => 'Password reset successfully.']);
        }

        Response::error('Route not found.', 404);
    }

    private function handleAdmin(string $method, ?string $action): void
    {
        $admin = Auth::requireAdmin();

        if ($action !== 'profile' && $action !== 'password') {
            Response::error('Route not found.', 404);
        }

        if ($method === 'GET' && $action === 'profile') {
            Response::success(['admin' => $admin]);
        }

        if ($method === 'PUT' && $action === 'profile') {
            $errors = Validator::required($this->request->body(), ['name', 'email', 'phone']);
            if ($errors !== []) {
                Response::error('Validation failed.', 422, $errors);
            }

            $updated = $this->admins->updateProfile((int) $admin['id'], [
                'name' => (string) $this->request->input('name'),
                'email' => (string) $this->request->input('email'),
                'phone' => (string) $this->request->input('phone'),
            ]);

            Response::success(['admin' => $updated]);
        }

        if ($method === 'PUT' && $action === 'password') {
            $errors = Validator::required($this->request->body(), ['current_password', 'new_password']);
            if ($errors !== []) {
                Response::error('Validation failed.', 422, $errors);
            }

            $freshAdmin = $this->admins->findById((int) $admin['id']);
            if ($freshAdmin === null || !password_verify((string) $this->request->input('current_password'), $freshAdmin['password'])) {
                Response::error('Current password is incorrect.', 422);
            }

            $this->admins->updatePassword((int) $admin['id'], (string) $this->request->input('new_password'));
            Response::success(['message' => 'Password updated successfully.']);
        }

        Response::error('Route not found.', 404);
    }

    private function handleDashboard(string $method, ?string $action): void
    {
        Auth::requireAdmin();

        if ($method === 'GET' && $action === 'stats') {
            Response::success([
                'stats' => $this->vehicles->dashboardStats(),
            ]);
        }

        Response::error('Route not found.', 404);
    }

    private function handleCategories(string $method, ?int $id): void
    {
        Auth::requireAdmin();

        if ($method === 'GET' && $id === null) {
            Response::success(['categories' => $this->categories->all()]);
        }

        if ($method === 'POST') {
            $errors = Validator::required($this->request->body(), ['name', 'hourly_rate']);
            if ($errors !== []) {
                Response::error('Validation failed.', 422, $errors);
            }

            Response::success([
                'category' => $this->categories->create($this->request->body()),
            ], 201);
        }

        if ($id === null) {
            Response::error('Category not found.', 404);
        }

        if ($method === 'GET') {
            $category = $this->categories->find($id);
            if ($category === null) {
                Response::error('Category not found.', 404);
            }

            Response::success(['category' => $category]);
        }

        if ($method === 'PUT') {
            $category = $this->categories->update($id, $this->request->body());
            if ($category === null) {
                Response::error('Category not found.', 404);
            }

            Response::success(['category' => $category]);
        }

        if ($method === 'DELETE') {
            $deleted = $this->categories->delete($id);
            if (!$deleted) {
                Response::error('Category not found.', 404);
            }

            Response::success(['message' => 'Category deleted successfully.']);
        }

        Response::error('Route not found.', 404);
    }

    private function handleSlots(string $method, ?int $id): void
    {
        Auth::requireAdmin();

        if ($method === 'GET' && $id === null) {
            Response::success(['slots' => $this->slots->all()]);
        }

        if ($method === 'POST') {
            $errors = Validator::required($this->request->body(), ['slot_number']);
            if ($errors !== []) {
                Response::error('Validation failed.', 422, $errors);
            }

            Response::success(['slot' => $this->slots->create($this->request->body())], 201);
        }

        if ($id === null) {
            Response::error('Slot not found.', 404);
        }

        if ($method === 'GET') {
            $slot = $this->slots->find($id);
            if ($slot === null) {
                Response::error('Slot not found.', 404);
            }

            Response::success(['slot' => $slot]);
        }

        if ($method === 'PUT') {
            $slot = $this->slots->update($id, $this->request->body());
            if ($slot === null) {
                Response::error('Slot not found.', 404);
            }

            Response::success(['slot' => $slot]);
        }

        if ($method === 'DELETE') {
            $deleted = $this->slots->delete($id);
            if (!$deleted) {
                Response::error('Slot not found.', 404);
            }

            Response::success(['message' => 'Slot deleted successfully.']);
        }

        Response::error('Route not found.', 404);
    }

    private function handleVehicles(string $method, ?int $id, ?string $action, array $segments): void
    {
        Auth::requireAdmin();

        if ($method === 'GET' && ($segments[1] ?? null) === 'search') {
            $query = (string) $this->request->input('query', '');
            Response::success(['vehicles' => $this->vehicles->search($query)]);
        }

        if ($method === 'GET' && $id === null) {
            Response::success(['vehicles' => $this->vehicles->all()]);
        }

        if ($method === 'POST' && $id === null) {
            $errors = Validator::required($this->request->body(), [
                'vehicle_category_id',
                'vehicle_type',
                'registration_number',
                'owner_name',
                'owner_contact',
            ]);

            if ($errors !== []) {
                Response::error('Validation failed.', 422, $errors);
            }

            Response::success(['vehicle' => $this->vehicles->createEntry($this->request->body())], 201);
        }

        if ($id === null) {
            Response::error('Vehicle record not found.', 404);
        }

        if ($method === 'GET') {
            $vehicle = $this->vehicles->find($id);
            if ($vehicle === null) {
                Response::error('Vehicle record not found.', 404);
            }

            Response::success(['vehicle' => $vehicle]);
        }

        if ($method === 'POST' && $action === 'exit') {
            $record = $this->vehicles->find($id);
            if ($record === null) {
                Response::error('Vehicle record not found.', 404);
            }

            if ($record['status'] === 'EXITED') {
                Response::error('Vehicle has already been checked out.', 422);
            }

            $result = $this->vehicles->markExit($id, $this->calculator->calculate($record));
            Response::success(['vehicle' => $result]);
        }

        Response::error('Route not found.', 404);
    }

    private function handleReports(string $method, ?string $action): void
    {
        Auth::requireAdmin();

        if ($method === 'GET' && $action === 'vehicles') {
            $from = (string) $this->request->input('from');
            $to = (string) $this->request->input('to');

            if ($from === '' || $to === '') {
                Response::error('from and to dates are required.', 422);
            }

            Response::success([
                'summary' => $this->vehicles->reportSummary($from, $to),
                'vehicles' => $this->vehicles->betweenDates($from, $to),
            ]);
        }

        Response::error('Route not found.', 404);
    }
}
