<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/dispute-management.php';
carzo_start_session();
carzo_require_user_roles(['driver'], '../signin.php', ['active', 'pending'], '../index.php');
include 'config.php';

$driverId = (int) ($_SESSION['user']['user_ID'] ?? 0);

if (isset($_POST['submitDriverResponse'])) {
    $complaintId = (int) ($_POST['complaint_id'] ?? 0);
    $response = carzo_escape($conn, $_POST['driver_response'] ?? '');
    $complaint = carzo_complaint_fetch($conn, $complaintId);

    if (!$complaint) {
        carzo_redirect_with_message('../driver-disputes.php', 'error', 'Dispute not found');
    }

    $vehicleResult = mysqli_query($conn, "SELECT owner_user_id FROM vehicles WHERE vehicle_id = " . (int) ($complaint['target_vehicle_id'] ?? 0) . " LIMIT 1");
    $vehicleOwnerId = ($vehicleResult && mysqli_num_rows($vehicleResult) > 0) ? (int) mysqli_fetch_assoc($vehicleResult)['owner_user_id'] : 0;
    $hasAccess = (int) ($complaint['target_user_id'] ?? 0) === $driverId || $vehicleOwnerId === $driverId;

    if (!$hasAccess) {
        carzo_redirect_with_message('../driver-disputes.php', 'error', 'You do not have permission to respond to that dispute');
    }

    if ($response === '') {
        carzo_redirect_with_message('../driver-dispute-view.php?complaint_id=' . $complaintId, 'error', 'Please enter a response');
    }

    $status = carzo_complaint_normalize_status($complaint['status'] ?? 'open');
    $nextStatus = $status === 'open' ? 'under_review' : $status;

    $sql = "UPDATE complaints
            SET driver_response = '{$response}',
                status = '" . carzo_escape($conn, $nextStatus) . "',
                updated_at = NOW()
            WHERE complaint_id = {$complaintId}";

    if (!mysqli_query($conn, $sql)) {
        carzo_redirect_with_message('../driver-dispute-view.php?complaint_id=' . $complaintId, 'error', 'Unable to save response');
    }

    carzo_redirect_with_message('../driver-dispute-view.php?complaint_id=' . $complaintId, 'msg', 'Response saved successfully');
}
