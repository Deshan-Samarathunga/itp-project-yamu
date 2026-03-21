<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/vehicle-management.php';
carzo_start_session();
carzo_require_user_roles(['driver'], '../signin.php', ['active', 'pending'], '../index.php');
include 'config.php';

$driverId = (int) ($_SESSION['user']['user_ID'] ?? 0);

if (isset($_POST['createDriverVehicle'])) {
    [$payload, $error] = carzo_vehicle_collect_payload($conn, $driverId, 'driver');

    if ($payload === false) {
        carzo_redirect_with_message('../driver-vehicle-add.php', 'error', $error);
    }

    if (!carzo_vehicle_save($conn, $payload)) {
        carzo_redirect_with_message('../driver-vehicle-add.php', 'error', 'Failed to create vehicle listing');
    }

    carzo_redirect_with_message('../driver-vehicles.php', 'msg', 'Vehicle listing submitted for approval');
}

if (isset($_POST['updateDriverVehicle'])) {
    $vehicleId = (int) ($_POST['vehicleId'] ?? 0);
    $vehicle = carzo_vehicle_fetch($conn, $vehicleId);

    if (!$vehicle || (int) ($vehicle['owner_user_id'] ?? 0) !== $driverId) {
        carzo_redirect_with_message('../driver-vehicles.php', 'error', 'Vehicle not found');
    }

    [$payload, $error] = carzo_vehicle_collect_payload($conn, $driverId, 'driver', $vehicle);

    if ($payload === false) {
        carzo_redirect_with_message('../driver-vehicle-edit.php?vehicle_id=' . $vehicleId, 'error', $error);
    }

    if (!carzo_vehicle_save($conn, $payload, $vehicleId)) {
        carzo_redirect_with_message('../driver-vehicle-edit.php?vehicle_id=' . $vehicleId, 'error', 'Failed to update vehicle listing');
    }

    carzo_redirect_with_message('../driver-vehicles.php', 'msg', 'Vehicle listing updated successfully');
}

if (isset($_GET['deleteVehicle'])) {
    $vehicleId = (int) $_GET['deleteVehicle'];
    $vehicle = carzo_vehicle_fetch($conn, $vehicleId);

    if (!$vehicle || (int) ($vehicle['owner_user_id'] ?? 0) !== $driverId) {
        carzo_redirect_with_message('../driver-vehicles.php', 'error', 'Vehicle not found');
    }

    if (!mysqli_query($conn, 'DELETE FROM vehicles WHERE vehicle_id = ' . $vehicleId)) {
        carzo_redirect_with_message('../driver-vehicles.php', 'error', 'Failed to delete vehicle listing');
    }

    carzo_redirect_with_message('../driver-vehicles.php', 'msg', 'Vehicle listing deleted successfully');
}
