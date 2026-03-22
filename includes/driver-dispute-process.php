<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/dispute-management.php';
yamu_start_session();
yamu_require_user_roles(['driver'], '../signin.php', ['active', 'verified'], '../access-denied.php');
include 'config.php';

$driverId = (int) ($_SESSION['user']['user_ID'] ?? 0);

if (isset($_POST['submitDriverResponse'])) {
    $complaintId = (int) ($_POST['complaint_id'] ?? 0);
    $response = yamu_escape($conn, $_POST['driver_response'] ?? '');
    $complaint = yamu_complaint_fetch($conn, $complaintId);

    if (!$complaint) {
        yamu_redirect_with_message('../driver-disputes.php', 'error', 'Dispute not found');
    }

    $booking = yamu_booking_fetch($conn, (int) ($complaint['booking_id'] ?? 0));
    $hasAccess = $booking
        && (int) ($complaint['target_user_id'] ?? 0) === $driverId
        && empty($booking['vehicle_ID']);

    if (!$hasAccess) {
        yamu_redirect_with_message('../driver-disputes.php', 'error', 'You do not have permission to respond to that dispute');
    }

    if ($response === '') {
        yamu_redirect_with_message('../driver-dispute-view.php?complaint_id=' . $complaintId, 'error', 'Please enter a response');
    }

    $status = yamu_complaint_normalize_status($complaint['status'] ?? 'open');
    $nextStatus = $status === 'open' ? 'under_review' : $status;

    $sql = "UPDATE complaints
            SET driver_response = '{$response}',
                status = '" . yamu_escape($conn, $nextStatus) . "',
                updated_at = NOW()
            WHERE complaint_id = {$complaintId}";

    if (!mysqli_query($conn, $sql)) {
        yamu_redirect_with_message('../driver-dispute-view.php?complaint_id=' . $complaintId, 'error', 'Unable to save response');
    }

    yamu_redirect_with_message('../driver-dispute-view.php?complaint_id=' . $complaintId, 'msg', 'Response saved successfully');
}



