<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/booking-management.php';
carzo_start_session();
carzo_require_user_roles(['driver'], '../signin.php', ['active', 'pending'], '../index.php');
include 'config.php';

$driverId = (int) ($_SESSION['user']['user_ID'] ?? 0);

if (isset($_GET['action'], $_GET['booking_id'])) {
    $bookingId = (int) $_GET['booking_id'];
    $action = $_GET['action'];
    $booking = carzo_booking_fetch($conn, $bookingId);

    if (!$booking || (int) ($booking['driver_id'] ?? 0) !== $driverId) {
        carzo_redirect_with_message('../driver-bookings.php', 'error', 'Booking not found');
    }

    $currentStatus = carzo_booking_normalize_status($booking['booking_status'] ?? 'pending');
    $newStatus = null;

    if ($action === 'confirm' && $currentStatus === 'pending') {
        $newStatus = 'confirmed';
    } elseif ($action === 'reject' && $currentStatus === 'pending') {
        $newStatus = 'rejected';
    } elseif ($action === 'complete' && $currentStatus === 'confirmed') {
        $newStatus = 'completed';
    }

    if ($newStatus === null) {
        carzo_redirect_with_message('../driver-bookings.php', 'error', 'That booking action is not allowed');
    }

    if (!carzo_booking_update_status($conn, $bookingId, $newStatus)) {
        carzo_redirect_with_message('../driver-bookings.php', 'error', 'Failed to update booking status');
    }

    carzo_redirect_with_message('../driver-bookings.php', 'msg', 'Booking status updated successfully');
}
