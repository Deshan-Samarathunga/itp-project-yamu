<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/review-management.php';
carzo_start_session();
carzo_require_user_roles(['customer'], '../signin.php', ['active', 'verified'], '../access-denied.php');
include 'config.php';

$customerId = (int) ($_SESSION['user']['user_ID'] ?? 0);

if (isset($_POST['submitReview'])) {
    $bookingId = (int) ($_POST['booking_id'] ?? 0);
    $rating = (int) ($_POST['rating'] ?? 0);
    $comment = trim((string) ($_POST['comment'] ?? ''));

    [$allowed, $error, $booking] = carzo_review_validate_submission($conn, $bookingId, $customerId);
    if (!$allowed) {
        carzo_redirect_with_message('../booking-review.php?booking_id=' . $bookingId, 'error', $error);
    }

    [$success, $message] = carzo_review_create($conn, $booking, $customerId, $rating, $comment);
    if (!$success) {
        carzo_redirect_with_message('../booking-review.php?booking_id=' . $bookingId, 'error', $message);
    }

    carzo_redirect_with_message('../my-reviews.php', 'msg', $message);
}



