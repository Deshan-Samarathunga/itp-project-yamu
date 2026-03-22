<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/booking-management.php';
yamu_start_session();
yamu_require_admin('../index.php', '../access-denied.php');
include 'config.php'; // Database Connection

if (isset($_GET['action'], $_GET['booking_id'])) {
    $bookingId = (int) $_GET['booking_id'];
    $booking = yamu_booking_fetch($conn, $bookingId);

    if (!$booking) {
        yamu_redirect_with_message('../bookings.php', 'error', 'Booking not found');
    }

    $currentStatus = yamu_booking_normalize_status($booking['booking_status'] ?? 'pending');
    $targetStatus = null;
    $action = strtolower(trim((string) $_GET['action']));

    if ($action === 'confirm' && $currentStatus === 'pending') {
        $targetStatus = 'confirmed';
    } elseif ($action === 'reject' && $currentStatus === 'pending') {
        $targetStatus = 'rejected';
    } elseif ($action === 'cancel' && in_array($currentStatus, ['pending', 'confirmed'], true)) {
        $targetStatus = 'cancelled';
    } elseif ($action === 'complete' && $currentStatus === 'confirmed') {
        $targetStatus = 'completed';
    }

    if ($targetStatus === null) {
        yamu_redirect_with_message('../bookings.php', 'error', 'That booking action is not allowed');
    }

    if (!yamu_booking_update_status($conn, $bookingId, $targetStatus)) {
        yamu_redirect_with_message('../bookings.php', 'error', 'Failed to update booking status');
    }

    yamu_redirect_with_message('../bookings.php', 'msg', 'Booking status updated successfully');
}

if (isset($_GET['deleteBooking'])) {
    $bookingId = (int) $_GET['deleteBooking'];
    $booking = yamu_booking_fetch($conn, $bookingId);

    if (!$booking) {
        yamu_redirect_with_message('../bookings.php', 'error', 'Booking not found');
    }

    $sql = "DELETE FROM `booking` WHERE booking_id = {$bookingId}";

    if (!mysqli_query($conn, $sql)) {
        yamu_redirect_with_message('../bookings.php', 'error', 'Error deleting booking');
    }

    if (!empty($booking['vehicle_ID'])) {
        yamu_booking_sync_vehicle_availability($conn, (int) $booking['vehicle_ID']);
    }

    yamu_redirect_with_message('../bookings.php', 'msg', 'Booking deleted successfully');
}
?>

