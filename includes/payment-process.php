<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/payment-management.php';
yamu_start_session();
yamu_require_user_roles(['customer'], '../signin.php', ['active', 'verified'], '../access-denied.php');
include 'config.php';

$customerId = (int) ($_SESSION['user']['user_ID'] ?? 0);

if (isset($_POST['payBooking'])) {
    $bookingId = (int) ($_POST['booking_id'] ?? 0);
    $paymentMethod = trim((string) ($_POST['payment_method'] ?? ''));
    $promoCode = trim((string) ($_POST['promo_code'] ?? ''));
    $booking = yamu_booking_fetch($conn, $bookingId);

    if (!$booking || (int) ($booking['user_ID'] ?? 0) !== $customerId) {
        yamu_redirect_with_message('../payment-history.php', 'error', 'Booking not found');
    }

    [$success, $message, $paymentId] = yamu_payment_create($conn, $booking, $customerId, $paymentMethod, $promoCode);
    if (!$success) {
        yamu_redirect_with_message('../booking-payment.php?booking_id=' . $bookingId, 'error', $message);
    }

    yamu_redirect_with_message('../invoice.php?payment_id=' . (int) $paymentId, 'msg', $message);
}



