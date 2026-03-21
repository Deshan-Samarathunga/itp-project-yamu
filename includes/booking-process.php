<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/booking-management.php';
carzo_start_session();
carzo_require_user_roles(['customer'], '../signin.php', ['active'], '../index.php');
include 'config.php';

$customerId = (int) ($_SESSION['user']['user_ID'] ?? 0);

if (isset($_POST['booking'])) {
    $userId = (int) ($_POST['userID'] ?? 0);
    $vehicleId = (int) ($_POST['vehicleID'] ?? 0);
    $startDate = trim((string) ($_POST['startDate'] ?? ''));
    $endDate = trim((string) ($_POST['endDate'] ?? ''));

    if ($userId !== $customerId) {
        carzo_redirect_with_message('../index.php', 'error', 'Unauthorized booking request');
    }

    [$success, $message] = carzo_booking_create($conn, $customerId, $vehicleId, $startDate, $endDate);

    if (!$success) {
        carzo_redirect_with_message('../vehical-details.php?vehicle_id=' . $vehicleId, 'error', $message);
    }

    carzo_redirect_with_message('../my-booking.php', 'msg', $message);
}

if (isset($_GET['cancelBooking'])) {
    $bookingId = (int) $_GET['cancelBooking'];
    $booking = carzo_booking_fetch($conn, $bookingId);

    if (!$booking || (int) ($booking['user_ID'] ?? 0) !== $customerId) {
        carzo_redirect_with_message('../my-booking.php', 'error', 'Booking not found');
    }

    $currentStatus = carzo_booking_normalize_status($booking['booking_status'] ?? 'pending');

    if (!in_array($currentStatus, ['pending', 'confirmed'], true)) {
        carzo_redirect_with_message('../my-booking.php', 'error', 'This booking can no longer be cancelled');
    }

    if (!carzo_booking_update_status($conn, $bookingId, 'cancelled')) {
        carzo_redirect_with_message('../my-booking.php', 'error', 'Unable to cancel booking');
    }

    carzo_redirect_with_message('../my-booking.php', 'msg', 'Booking cancelled successfully');
}
