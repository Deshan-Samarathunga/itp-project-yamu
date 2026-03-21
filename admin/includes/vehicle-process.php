<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/vehicle-management.php';
carzo_start_session();
carzo_require_admin('../index.php');
include 'config.php';

$adminUserId = (int) ($_SESSION['admin']['user_id'] ?? $_SESSION['admin']['admin_id'] ?? 0);

if (isset($_POST['carSubmit'])) {
    $ownerUserId = (int) ($_POST['owner_user_id'] ?? $adminUserId);
    [$payload, $error] = carzo_vehicle_collect_payload($conn, $ownerUserId, 'admin');

    if ($payload === false) {
        carzo_redirect_with_message('../vehicle-add.php', 'error', $error);
    }

    if (!carzo_vehicle_save($conn, $payload)) {
        carzo_redirect_with_message('../vehicle-add.php', 'error', 'Error adding vehicle');
    }

    carzo_redirect_with_message('../vehicle.php', 'msg', 'Vehicle added successfully');
}

if (isset($_POST['updateVehicle'])) {
    $vehicleId = (int) ($_POST['vehicleId'] ?? 0);
    $ownerUserId = (int) ($_POST['owner_user_id'] ?? $adminUserId);
    $vehicle = carzo_vehicle_fetch($conn, $vehicleId);

    if (!$vehicle) {
        carzo_redirect_with_message('../vehicle.php', 'error', 'Vehicle not found');
    }

    [$payload, $error] = carzo_vehicle_collect_payload($conn, $ownerUserId, 'admin', $vehicle);

    if ($payload === false) {
        carzo_redirect_with_message('../vehicle-edit.php?vehicle_id=' . $vehicleId, 'error', $error);
    }

    if (!carzo_vehicle_save($conn, $payload, $vehicleId)) {
        carzo_redirect_with_message('../vehicle-edit.php?vehicle_id=' . $vehicleId, 'error', 'Error updating vehicle');
    }

    carzo_redirect_with_message('../vehicle.php', 'msg', 'Vehicle updated successfully');
}

if (isset($_GET['vehicle_id'])) {
    $vehicleId = (int) $_GET['vehicle_id'];

    if (!mysqli_query($conn, 'DELETE FROM vehicles WHERE vehicle_id = ' . $vehicleId)) {
        carzo_redirect_with_message('../vehicle.php', 'error', 'Error deleting vehicle');
    }

    carzo_redirect_with_message('../vehicle.php', 'msg', 'Vehicle deleted successfully');
}
