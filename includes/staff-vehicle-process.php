<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/vehicle-management.php';
yamu_start_session();
yamu_require_user_roles(['staff'], '../signin.php', ['active', 'verified'], '../access-denied.php');
include 'config.php';

$staffId = (int) ($_SESSION['user']['user_ID'] ?? 0);

if (isset($_POST['createStaffVehicle']) || isset($_POST['createDriverVehicle'])) {
    [$payload, $error] = yamu_vehicle_collect_payload($conn, $staffId, 'staff');

    if ($payload === false) {
        yamu_redirect_with_message('../staff-vehicle-add.php', 'error', $error);
    }

    if (!yamu_vehicle_save($conn, $payload)) {
        yamu_redirect_with_message('../staff-vehicle-add.php', 'error', 'Failed to create vehicle listing');
    }

    yamu_redirect_with_message('../staff-vehicles.php', 'msg', 'Vehicle listing submitted for approval');
}

if (isset($_POST['updateStaffVehicle']) || isset($_POST['updateDriverVehicle'])) {
    $vehicleId = (int) ($_POST['vehicleId'] ?? 0);
    $vehicle = yamu_vehicle_fetch($conn, $vehicleId);

    if (!$vehicle || (int) ($vehicle['owner_user_id'] ?? 0) !== $staffId) {
        yamu_redirect_with_message('../staff-vehicles.php', 'error', 'Vehicle not found');
    }

    [$payload, $error] = yamu_vehicle_collect_payload($conn, $staffId, 'staff', $vehicle);

    if ($payload === false) {
        yamu_redirect_with_message('../staff-vehicle-edit.php?vehicle_id=' . $vehicleId, 'error', $error);
    }

    if (!yamu_vehicle_save($conn, $payload, $vehicleId)) {
        yamu_redirect_with_message('../staff-vehicle-edit.php?vehicle_id=' . $vehicleId, 'error', 'Failed to update vehicle listing');
    }

    yamu_redirect_with_message('../staff-vehicles.php', 'msg', 'Vehicle listing updated successfully');
}

if (isset($_GET['deleteVehicle'])) {
    $vehicleId = (int) ($_GET['deleteVehicle'] ?? 0);
    $vehicle = yamu_vehicle_fetch($conn, $vehicleId);

    if (!$vehicle || (int) ($vehicle['owner_user_id'] ?? 0) !== $staffId) {
        yamu_redirect_with_message('../staff-vehicles.php', 'error', 'Vehicle not found');
    }

    $stmt = $conn->prepare('DELETE FROM vehicles WHERE vehicle_id = ? AND owner_user_id = ? LIMIT 1');

    if (!$stmt) {
        yamu_redirect_with_message('../staff-vehicles.php', 'error', 'Failed to delete vehicle listing');
    }

    $stmt->bind_param('ii', $vehicleId, $staffId);
    $deleted = $stmt->execute();
    $stmt->close();

    if (!$deleted) {
        yamu_redirect_with_message('../staff-vehicles.php', 'error', 'Failed to delete vehicle listing');
    }

    yamu_redirect_with_message('../staff-vehicles.php', 'msg', 'Vehicle listing deleted successfully');
}

yamu_redirect('../staff-vehicles.php');
