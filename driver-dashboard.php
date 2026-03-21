<?php
    require_once __DIR__ . '/includes/auth.php';
    carzo_start_session();
    carzo_require_user_roles(['driver'], 'signin.php', ['active', 'pending'], 'index.php');
    include 'includes/config.php';
    $page_title = 'Driver Dashboard';
    $driverId = (int) ($_SESSION['user']['user_ID'] ?? 0);

    $adStatsResult = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total_ads,
                SUM(advertisement_status = 'active') AS published_ads,
                SUM(advertisement_status = 'paused') AS paused_ads,
                SUM(advertisement_status = 'draft') AS draft_ads,
                SUM(availability_status = 'available') AS available_ads,
                SUM(availability_status = 'on_request') AS on_request_ads
         FROM driver_ads
         WHERE driver_user_id = {$driverId}"
    );
    $adStats = $adStatsResult ? mysqli_fetch_assoc($adStatsResult) : [];

    $bookingStatsResult = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total_requests,
                SUM(booking_status = 'pending') AS pending_requests,
                SUM(booking_status = 'confirmed') AS active_bookings,
                SUM(booking_status = 'completed') AS completed_bookings
         FROM booking
         WHERE driver_id = {$driverId}"
    );
    $bookingStats = $bookingStatsResult ? mysqli_fetch_assoc($bookingStatsResult) : [];

    $reviewStatsResult = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total_reviews,
                SUM(status = 'visible') AS visible_reviews,
                SUM(status = 'pending') AS pending_reviews,
                COALESCE(AVG(CASE WHEN status = 'visible' THEN rating END), 0) AS average_rating
         FROM reviews
         WHERE driver_id = {$driverId}"
    );
    $reviewStats = $reviewStatsResult ? mysqli_fetch_assoc($reviewStatsResult) : [];

    $disputeStatsResult = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total_disputes,
                SUM(status IN ('open', 'under_review')) AS active_disputes
         FROM complaints c
         LEFT JOIN vehicles owned_vehicle ON owned_vehicle.vehicle_id = c.target_vehicle_id
         WHERE c.target_user_id = {$driverId}
            OR owned_vehicle.owner_user_id = {$driverId}"
    );
    $disputeStats = $disputeStatsResult ? mysqli_fetch_assoc($disputeStatsResult) : [];

    $earningsStatsResult = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS paid_transactions,
                COALESCE(SUM(final_amount), 0) AS paid_earnings
         FROM payments
         WHERE driver_id = {$driverId}
           AND payment_status = 'paid'"
    );
    $earningsStats = $earningsStatsResult ? mysqli_fetch_assoc($earningsStatsResult) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        include('includes/header.php');
    ?>
</head>
<body>
    <?php
        include('includes/menu.php');
    ?>
    
    <section class="profile">
        <?php
           include('includes/alert.php');
        ?>
        <div class="container">
            <div class="row">
                <?php
                    $currentAccountPage = 'driver-dashboard';
                    include('includes/account-sidebar.php');
                ?>
                <div class="profile-details card">
                    <h3>Driver Dashboard</h3>
                    <div class="form-group">
                        <label>Total Tour Ads:</label>
                        <input type="text" value="<?php echo (int) ($adStats['total_ads'] ?? 0); ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Published Ads:</label>
                        <input type="text" value="<?php echo (int) ($adStats['published_ads'] ?? 0); ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Paused Ads:</label>
                        <input type="text" value="<?php echo (int) ($adStats['paused_ads'] ?? 0); ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Draft Ads:</label>
                        <input type="text" value="<?php echo (int) ($adStats['draft_ads'] ?? 0); ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Available for Tours:</label>
                        <input type="text" value="<?php echo (int) ($adStats['available_ads'] ?? 0); ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>On-Request Ads:</label>
                        <input type="text" value="<?php echo (int) ($adStats['on_request_ads'] ?? 0); ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Total Booking Requests:</label>
                        <input type="text" value="<?php echo (int) ($bookingStats['total_requests'] ?? 0); ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Pending Requests:</label>
                        <input type="text" value="<?php echo (int) ($bookingStats['pending_requests'] ?? 0); ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Active Bookings:</label>
                        <input type="text" value="<?php echo (int) ($bookingStats['active_bookings'] ?? 0); ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Completed Bookings:</label>
                        <input type="text" value="<?php echo (int) ($bookingStats['completed_bookings'] ?? 0); ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Average Rating:</label>
                        <input type="text" value="<?php echo number_format((float) ($reviewStats['average_rating'] ?? 0), 1); ?> / 5" readonly />
                    </div>
                    <div class="form-group">
                        <label>Total Reviews:</label>
                        <input type="text" value="<?php echo (int) ($reviewStats['total_reviews'] ?? 0); ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Reviews Awaiting Moderation:</label>
                        <input type="text" value="<?php echo (int) ($reviewStats['pending_reviews'] ?? 0); ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Visible Reviews:</label>
                        <input type="text" value="<?php echo (int) ($reviewStats['visible_reviews'] ?? 0); ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Total Disputes:</label>
                        <input type="text" value="<?php echo (int) ($disputeStats['total_disputes'] ?? 0); ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Open / Under Review Disputes:</label>
                        <input type="text" value="<?php echo (int) ($disputeStats['active_disputes'] ?? 0); ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Paid Transactions:</label>
                        <input type="text" value="<?php echo (int) ($earningsStats['paid_transactions'] ?? 0); ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Paid Earnings:</label>
                        <input type="text" value="Rs. <?php echo carzo_money($earningsStats['paid_earnings'] ?? 0); ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Account Type:</label>
                        <input type="text" value="<?php echo ucfirst($_SESSION['user']['role']) ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Account Status:</label>
                        <input type="text" value="<?php echo ucfirst($_SESSION['user']['account_status']) ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Verification Status:</label>
                        <input type="text" value="<?php echo ucfirst(str_replace('_', ' ', $_SESSION['user']['verification_status'])) ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>License / NIC:</label>
                        <input type="text" value="<?php echo $_SESSION['user']['license_or_nic'] ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Driver Bio:</label>
                        <textarea readonly><?php echo $_SESSION['user']['bio'] ?></textarea>
                    </div>
                    <p>
                        Use the driver menu to publish your tour driver ads, keep your traveler-facing profile updated, and manage bookings from one place.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <?php
        include('includes/footer.php');
    ?>

    <script src="assets/js/main.js"></script>
</body>
</html>
