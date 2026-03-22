<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/vehicle-management.php';
yamu_start_session();
yamu_require_admin('../index.php', '../access-denied.php');
include 'config.php';

$adminUserId = (int) ($_SESSION['admin']['user_id'] ?? $_SESSION['admin']['admin_id'] ?? 0);

function yamu_admin_vehicle_owner_is_staff($conn, $ownerUserId)
{
    $ownerUserId = (int) $ownerUserId;

    if ($ownerUserId <= 0) {
        return false;
    }

    $user = yamu_fetch_user_by_id($conn, $ownerUserId);

    if (!$user) {
        return false;
    }

    $roleAssignments = yamu_fetch_user_roles(
        $conn,
        $ownerUserId,
        $user['role'] ?? 'customer',
        $user['account_status'] ?? 'active',
        $user['verification_status'] ?? 'verified'
    );
    $staffAssignment = $roleAssignments['staff'] ?? null;

    if (!$staffAssignment) {
        return false;
    }

    if (!yamu_role_allows_standard_status($staffAssignment['role_status'] ?? 'pending')) {
        return false;
    }

    $verificationStatus = yamu_normalize_verification_status($staffAssignment['verification_status'] ?? 'pending', 'staff');
    return in_array($verificationStatus, ['approved', 'verified'], true);
}

if (isset($_POST['carSubmit'])) {
    yamu_redirect_with_message('../vehicle.php', 'error', 'Admins cannot post vehicle listings. Create them from a staff account instead.');
}

if (isset($_POST['updateVehicle'])) {
    $vehicleId = (int) ($_POST['vehicleId'] ?? 0);
    $ownerUserId = (int) ($_POST['owner_user_id'] ?? $adminUserId);
    $vehicle = yamu_vehicle_fetch($conn, $vehicleId);

    if (!$vehicle) {
        yamu_redirect_with_message('../vehicle.php', 'error', 'Vehicle not found');
    }

    if (!yamu_admin_vehicle_owner_is_staff($conn, $ownerUserId)) {
        yamu_redirect_with_message('../vehicle-edit.php?vehicle_id=' . $vehicleId, 'error', 'Vehicles must belong to an approved staff account.');
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

    $stmt = $conn->prepare('DELETE FROM vehicles WHERE vehicle_id = ? LIMIT 1');

    if (!$stmt) {
        yamu_redirect_with_message('../vehicle.php', 'error', 'Error deleting vehicle');
    }

    $stmt->bind_param('i', $vehicleId);
    $deleted = $stmt->execute();
    $stmt->close();

    if (!$deleted) {
        yamu_redirect_with_message('../vehicle.php', 'error', 'Error deleting vehicle');
    }

    yamu_redirect_with_message('../vehicle.php', 'msg', 'Vehicle deleted successfully');
}

