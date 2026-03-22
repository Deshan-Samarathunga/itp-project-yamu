<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/review-management.php';
yamu_start_session();
yamu_require_admin('../index.php', '../access-denied.php');
include 'config.php';

if (isset($_GET['review_id'], $_GET['status'])) {
    $reviewId = (int) $_GET['review_id'];
    $review = yamu_review_fetch($conn, $reviewId);

    if (!$review) {
        yamu_redirect_with_message('../reviews.php', 'error', 'Review not found');
    }

    $status = yamu_review_normalize_status($_GET['status']);
    $statusEscaped = yamu_escape($conn, $status);

    if (!mysqli_query($conn, "UPDATE reviews SET status = '{$statusEscaped}', updated_at = NOW() WHERE review_id = {$reviewId}")) {
        yamu_redirect_with_message('../reviews.php', 'error', 'Unable to update review status');
    }

    yamu_redirect_with_message('../reviews.php', 'msg', 'Review status updated successfully');
}

