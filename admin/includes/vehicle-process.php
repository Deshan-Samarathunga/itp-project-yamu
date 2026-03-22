<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/vehicle-management.php';
yamu_start_session();
yamu_require_admin('../index.php', '../access-denied.php');
include 'config.php';

$adminUserId = (int) ($_SESSION['admin']['user_id'] ?? $_SESSION['admin']['admin_id'] ?? 0);

if (isset($_POST['carSubmit'])) {
    $ownerUserId = (int) ($_POST['owner_user_id'] ?? $adminUserId);
    [$payload, $error] = yamu_vehicle_collect_payload($conn, $ownerUserId, 'admin');

    if ($payload === false) {
        yamu_redirect_with_message('../vehicle-add.php', 'error', $error);
    }

    if (!yamu_vehicle_save($conn, $payload)) {
        yamu_redirect_with_message('../vehicle-add.php', 'error', 'Error adding vehicle');
    }

    yamu_redirect_with_message('../vehicle.php', 'msg', 'Vehicle added successfully');
}

if (isset($_POST['updateVehicle'])) {
    $vehicleId = (int) ($_POST['vehicleId'] ?? 0);
    $ownerUserId = (int) ($_POST['owner_user_id'] ?? $adminUserId);
    $vehicle = yamu_vehicle_fetch($conn, $vehicleId);

    if (!$vehicle) {
        yamu_redirect_with_message('../vehicle.php', 'error', 'Vehicle not found');
    }

    [$payload, $error] = yamu_vehicle_collect_payload($conn, $ownerUserId, 'admin', $vehicle);

    if ($payload === false) {
        yamu_redirect_with_message('../vehicle-edit.php?vehicle_id=' . $vehicleId, 'error', $error);
    }

    if (!yamu_vehicle_save($conn, $payload, $vehicleId)) {
        yamu_redirect_with_message('../vehicle-edit.php?vehicle_id=' . $vehicleId, 'error', 'Error updating vehicle');
    }

    yamu_redirect_with_message('../vehicle.php', 'msg', 'Vehicle updated successfully');
}

if (isset($_GET['vehicle_id'])) {
    $vehicleId = (int) $_GET['vehicle_id'];

    if (!mysqli_query($conn, 'DELETE FROM vehicles WHERE vehicle_id = ' . $vehicleId)) {
        yamu_redirect_with_message('../vehicle.php', 'error', 'Error deleting vehicle');
    }

    yamu_redirect_with_message('../vehicle.php', 'msg', 'Vehicle deleted successfully');
}

