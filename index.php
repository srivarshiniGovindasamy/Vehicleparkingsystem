<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

use ParkingSystem\Core\ApiController;
use ParkingSystem\Core\Request;

$request = Request::capture();
$controller = new ApiController($request);
$controller->dispatch();
