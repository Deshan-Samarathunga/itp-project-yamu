<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/booking-management.php';

function yamu_complaint_normalize_status($status)
{
    $status = strtolower(trim((string) $status));
    $allowed = ['open', 'under_review', 'resolved', 'rejected'];
    return in_array($status, $allowed, true) ? $status : 'open';
}

function yamu_complaint_fetch($conn, $complaintId)
{
    $complaintId = (int) $complaintId;
    $result = mysqli_query($conn, "SELECT * FROM complaints WHERE complaint_id = {$complaintId} LIMIT 1");
    return ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;
}

function yamu_complaint_create($conn, $booking, $complainantUserId, $subject, $category, $description, $attachment = '')
{
    $subject = yamu_escape($conn, $subject);
    $category = yamu_escape($conn, $category);
    $description = yamu_escape($conn, $description);
    $attachment = yamu_escape($conn, $attachment);

    if ($subject === '' || $category === '' || $description === '') {
        return [false, 'Please complete all dispute fields'];
    }

    $sql = "INSERT INTO complaints
            (`booking_id`, `complainant_user_id`, `target_user_id`, `target_vehicle_id`, `subject`, `category`, `description`, `attachment`, `status`, `created_at`, `updated_at`)
            VALUES
            (" . (int) $booking['booking_id'] . ",
             " . (int) $complainantUserId . ",
             " . ((int) ($booking['driver_id'] ?? 0) > 0 ? (int) $booking['driver_id'] : 'NULL') . ",
             " . ((int) ($booking['vehicle_ID'] ?? 0) > 0 ? (int) $booking['vehicle_ID'] : 'NULL') . ",
             '{$subject}',
             '{$category}',
             '{$description}',
             " . ($attachment !== '' ? "'{$attachment}'" : 'NULL') . ",
             'open',
             NOW(),
             NOW())";

    if (!mysqli_query($conn, $sql)) {
        return [false, 'Unable to submit dispute'];
    }

    return [true, 'Dispute submitted successfully'];
}
