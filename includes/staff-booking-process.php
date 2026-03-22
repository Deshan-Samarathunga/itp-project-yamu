<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/booking-management.php';
require_once __DIR__ . '/vehicle-management.php';
yamu_start_session();
yamu_require_user_roles(['staff'], '../signin.php', ['active', 'verified'], '../access-denied.php');
include 'config.php';

$staffId = (int) ($_SESSION['user']['user_ID'] ?? 0);

if (isset($_GET['action'], $_GET['booking_id'])) {
    $bookingId = (int) $_GET['booking_id'];
    $action = $_GET['action'];
    $booking = yamu_booking_fetch($conn, $bookingId);
    $vehicle = $booking ? yamu_vehicle_fetch($conn, (int) ($booking['vehicle_ID'] ?? 0)) : null;

    if (
        !$booking
        || empty($booking['vehicle_ID'])
        || !$vehicle
        || (int) ($vehicle['owner_user_id'] ?? 0) !== $staffId
        || (int) ($booking['driver_id'] ?? 0) !== $staffId
    ) {
        yamu_redirect_with_message('../staff-bookings.php', 'error', 'Booking not found');
    }

    $currentStatus = yamu_booking_normalize_status($booking['booking_status'] ?? 'pending');
    $newStatus = null;

    if ($action === 'confirm' && $currentStatus === 'pending') {
        $newStatus = 'confirmed';
    } elseif ($action === 'reject' && $currentStatus === 'pending') {
        $newStatus = 'rejected';
    } elseif ($action === 'complete' && $currentStatus === 'confirmed') {
        $newStatus = 'completed';
    }

    if ($newStatus === null) {
        yamu_redirect_with_message('../staff-bookings.php', 'error', 'That booking action is not allowed');
    }

    if (!yamu_booking_update_status($conn, $bookingId, $newStatus)) {
        yamu_redirect_with_message('../staff-bookings.php', 'error', 'Failed to update booking status');
    }

    yamu_redirect_with_message('../staff-bookings.php', 'msg', 'Booking status updated successfully');
}

yamu_redirect('../staff-bookings.php');
