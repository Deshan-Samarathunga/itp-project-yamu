<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/dispute-management.php';
carzo_start_session();
carzo_require_admin('../index.php');
include 'config.php';

if (isset($_POST['updateDispute'])) {
    $complaintId = (int) ($_POST['complaint_id'] ?? 0);
    $status = carzo_complaint_normalize_status($_POST['status'] ?? 'open');
    $adminNotes = carzo_escape($conn, $_POST['admin_notes'] ?? '');
    $complaint = carzo_complaint_fetch($conn, $complaintId);

    if (!$complaint) {
        carzo_redirect_with_message('../disputes.php', 'error', 'Dispute not found');
    }

    $sql = "UPDATE complaints
            SET status = '" . carzo_escape($conn, $status) . "',
                admin_notes = '{$adminNotes}',
                updated_at = NOW()
            WHERE complaint_id = {$complaintId}";

    if (!mysqli_query($conn, $sql)) {
        carzo_redirect_with_message('../dispute-view.php?complaint_id=' . $complaintId, 'error', 'Unable to update dispute');
    }

    carzo_redirect_with_message('../dispute-view.php?complaint_id=' . $complaintId, 'msg', 'Dispute updated successfully');
}
