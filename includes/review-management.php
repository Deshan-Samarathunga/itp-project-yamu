<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/booking-management.php';

function carzo_review_normalize_status($status)
{
    $status = strtolower(trim((string) $status));
    $allowed = ['pending', 'visible', 'hidden', 'flagged'];
    return in_array($status, $allowed, true) ? $status : 'pending';
}

function carzo_review_fetch($conn, $reviewId)
{
    $reviewId = (int) $reviewId;
    $result = mysqli_query($conn, "SELECT * FROM reviews WHERE review_id = {$reviewId} LIMIT 1");
    return ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;
}

function carzo_review_fetch_by_booking($conn, $bookingId, $customerId = null)
{
    $bookingId = (int) $bookingId;
    $sql = "SELECT * FROM reviews WHERE booking_id = {$bookingId}";

    if ($customerId !== null) {
        $sql .= " AND customer_id = " . (int) $customerId;
    }

    $sql .= " LIMIT 1";
    $result = mysqli_query($conn, $sql);
    return ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;
}

function carzo_review_validate_submission($conn, $bookingId, $customerId)
{
    $booking = carzo_booking_fetch($conn, $bookingId);

    if (!$booking) {
        return [false, 'Booking not found', null];
    }

    if ((int) ($booking['user_ID'] ?? 0) !== (int) $customerId) {
        return [false, 'This booking does not belong to you', null];
    }

    if (carzo_booking_normalize_status($booking['booking_status'] ?? 'pending') !== 'completed') {
        return [false, 'Reviews can only be submitted after a completed booking', null];
    }

    if (carzo_review_fetch_by_booking($conn, $bookingId, $customerId)) {
        return [false, 'You have already reviewed this booking', null];
    }

    return [true, null, $booking];
}

function carzo_review_create($conn, $booking, $customerId, $rating, $comment)
{
    $rating = (int) $rating;
    if ($rating < 1 || $rating > 5) {
        return [false, 'Please select a rating between 1 and 5'];
    }

    $comment = carzo_escape($conn, $comment);
    $sql = "INSERT INTO reviews
            (`booking_id`, `customer_id`, `vehicle_id`, `driver_id`, `rating`, `comment`, `status`, `created_at`, `updated_at`)
            VALUES
            (" . (int) $booking['booking_id'] . ",
             " . (int) $customerId . ",
             " . (int) $booking['vehicle_ID'] . ",
             " . ((int) ($booking['driver_id'] ?? 0) > 0 ? (int) $booking['driver_id'] : 'NULL') . ",
             {$rating},
             '{$comment}',
             'pending',
             NOW(),
             NOW())";

    if (!mysqli_query($conn, $sql)) {
        return [false, 'Unable to submit review'];
    }

    return [true, 'Review submitted successfully'];
}
