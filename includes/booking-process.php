<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/booking-management.php';
yamu_start_session();
yamu_require_user_roles(['customer'], '../signin.php', ['active', 'verified'], '../access-denied.php');
include 'config.php';

$customerId = (int) ($_SESSION['user']['user_ID'] ?? 0);

if (isset($_POST['booking'])) {
    $serviceType = strtolower(trim((string) ($_POST['service_type'] ?? 'vehicle')));
    $vehicleId = (int) ($_POST['vehicleID'] ?? 0);
    $driverAdId = (int) ($_POST['driverAdID'] ?? 0);
    $startDate = trim((string) ($_POST['startDate'] ?? ''));
    $endDate = trim((string) ($_POST['endDate'] ?? ''));

    if ($serviceType === 'driver') {
        [$success, $message] = yamu_booking_create_driver_service($conn, $customerId, $driverAdId, $startDate, $endDate);

        if (!$success) {
            yamu_redirect_with_message('../driver-details.php?ad_id=' . $driverAdId, 'error', $message);
        }
    } else {
        [$success, $message] = yamu_booking_create($conn, $customerId, $vehicleId, $startDate, $endDate);

        if (!$success) {
            yamu_redirect_with_message('../vehical-details.php?vehicle_id=' . $vehicleId, 'error', $message);
        }
    }

    yamu_redirect_with_message('../my-booking.php', 'msg', $message);
}

if (isset($_GET['cancelBooking'])) {
    $bookingId = (int) $_GET['cancelBooking'];
    $booking = yamu_booking_fetch($conn, $bookingId);

    if (!$booking || (int) ($booking['user_ID'] ?? 0) !== $customerId) {
        yamu_redirect_with_message('../my-booking.php', 'error', 'Booking not found');
    }

    $currentStatus = yamu_booking_normalize_status($booking['booking_status'] ?? 'pending');

    if (!in_array($currentStatus, ['pending', 'confirmed'], true)) {
        yamu_redirect_with_message('../my-booking.php', 'error', 'This booking can no longer be cancelled');
    }

    if (!yamu_booking_update_status($conn, $bookingId, 'cancelled')) {
        yamu_redirect_with_message('../my-booking.php', 'error', 'Unable to cancel booking');
    }

    yamu_redirect_with_message('../my-booking.php', 'msg', 'Booking cancelled successfully');
}



