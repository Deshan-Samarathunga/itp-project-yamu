<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/dispute-management.php';
yamu_start_session();
yamu_require_admin('../index.php', '../access-denied.php');
include 'config.php';

if (isset($_POST['updateDispute'])) {
    $complaintId = (int) ($_POST['complaint_id'] ?? 0);
    $status = yamu_complaint_normalize_status($_POST['status'] ?? 'open');
    $adminNotes = yamu_escape($conn, $_POST['admin_notes'] ?? '');
    $complaint = yamu_complaint_fetch($conn, $complaintId);

    if (!$complaint) {
        yamu_redirect_with_message('../disputes.php', 'error', 'Dispute not found');
    }

    $sql = "UPDATE complaints
            SET status = '" . yamu_escape($conn, $status) . "',
                admin_notes = '{$adminNotes}',
                updated_at = NOW()
            WHERE complaint_id = {$complaintId}";

    if (!mysqli_query($conn, $sql)) {
        yamu_redirect_with_message('../dispute-view.php?complaint_id=' . $complaintId, 'error', 'Unable to update dispute');
    }

    yamu_redirect_with_message('../dispute-view.php?complaint_id=' . $complaintId, 'msg', 'Dispute updated successfully');
}

