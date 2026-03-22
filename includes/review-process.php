<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/review-management.php';
yamu_start_session();
yamu_require_user_roles(['customer'], '../signin.php', ['active', 'verified'], '../access-denied.php');
include 'config.php';

$customerId = (int) ($_SESSION['user']['user_ID'] ?? 0);

if (isset($_POST['submitReview'])) {
    $bookingId = (int) ($_POST['booking_id'] ?? 0);
    $rating = (int) ($_POST['rating'] ?? 0);
    $comment = trim((string) ($_POST['comment'] ?? ''));

    [$allowed, $error, $booking] = yamu_review_validate_submission($conn, $bookingId, $customerId);
    if (!$allowed) {
        yamu_redirect_with_message('../booking-review.php?booking_id=' . $bookingId, 'error', $error);
    }

    [$success, $message] = yamu_review_create($conn, $booking, $customerId, $rating, $comment);
    if (!$success) {
        yamu_redirect_with_message('../booking-review.php?booking_id=' . $bookingId, 'error', $message);
    }

    yamu_redirect_with_message('../my-reviews.php', 'msg', $message);
}



