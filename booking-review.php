<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/review-management.php';
yamu_start_session();
yamu_require_user_roles(['customer'], 'signin.php', ['active', 'verified'], 'access-denied.php');
$page_title = "Booking Review";
include 'includes/config.php';

$customerId = (int) ($_SESSION['user']['user_ID'] ?? 0);
$bookingId = isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : 0;
[$allowed, $error, $booking] = yamu_review_validate_submission($conn, $bookingId, $customerId);
$existingReview = yamu_review_fetch_by_booking($conn, $bookingId, $customerId);

if (!$allowed && !$existingReview) {
    yamu_redirect_with_message('my-booking.php', 'error', $error);
}

$bookingInfoResult = mysqli_query($conn, "SELECT b.booking_No, COALESCE(v.vehicle_title, 'Driver Service') AS service_name FROM booking b LEFT JOIN vehicles v ON v.vehicle_id = b.vehicle_ID WHERE b.booking_id = {$bookingId} LIMIT 1");
$bookingInfo = ($bookingInfoResult && mysqli_num_rows($bookingInfoResult) > 0) ? mysqli_fetch_assoc($bookingInfoResult) : ['booking_No' => '', 'service_name' => ''];
?>
<!DOCTYPE html>
<html lang="en">
<head><?php include('includes/header.php'); ?></head>
<body>
<?php include('includes/menu.php'); ?>
<section class="profile">
    <?php include('includes/alert.php'); ?>
    <div class="container">
        <div class="row">
            <?php $currentAccountPage = 'reviews'; include('includes/account-sidebar.php'); ?>
            <div class="profile-details card">
                <h3>Review Booking</h3>
                <div class="form-group">
                    <label>Booking No.</label>
                    <input type="text" value="<?php echo yamu_e($bookingInfo['booking_No']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Service</label>
                    <input type="text" value="<?php echo yamu_e($bookingInfo['service_name']); ?>" readonly>
                </div>
                <?php if ($existingReview) { ?>
                    <div class="form-group">
                        <label>Your Rating</label>
                        <input type="text" value="<?php echo str_repeat('★', (int) $existingReview['rating']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Your Comment</label>
                        <textarea readonly><?php echo yamu_e($existingReview['comment']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <input type="text" value="<?php echo yamu_e(ucfirst($existingReview['status'])); ?>" readonly>
                    </div>
                <?php } else { ?>
                    <form action="includes/review-process.php" method="POST" class="signup-form">
                        <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                        <div class="form-group">
                            <label for="rating">Rating</label>
                            <select name="rating" id="rating" required>
                                <option value="">Select Rating</option>
                                <option value="5">5 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="2">2 Stars</option>
                                <option value="1">1 Star</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="comment">Comment</label>
                            <textarea name="comment" id="comment" rows="6" required></textarea>
                        </div>
                        <input type="submit" value="Submit Review" class="btn main-btn" name="submitReview">
                    </form>
                <?php } ?>
            </div>
        </div>
    </div>
</section>
<?php include('includes/footer.php'); ?>
<script src="assets/js/main.js"></script>
</body>
</html>



