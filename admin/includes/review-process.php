<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/review-management.php';
carzo_start_session();
carzo_require_admin('../index.php', '../access-denied.php');
include 'config.php';

if (isset($_GET['review_id'], $_GET['status'])) {
    $reviewId = (int) $_GET['review_id'];
    $review = carzo_review_fetch($conn, $reviewId);

    if (!$review) {
        carzo_redirect_with_message('../reviews.php', 'error', 'Review not found');
    }

    $status = carzo_review_normalize_status($_GET['status']);
    $statusEscaped = carzo_escape($conn, $status);

    if (!mysqli_query($conn, "UPDATE reviews SET status = '{$statusEscaped}', updated_at = NOW() WHERE review_id = {$reviewId}")) {
        carzo_redirect_with_message('../reviews.php', 'error', 'Unable to update review status');
    }

    carzo_redirect_with_message('../reviews.php', 'msg', 'Review status updated successfully');
}

