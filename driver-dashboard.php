<?php
    require_once __DIR__ . '/includes/auth.php';
    carzo_start_session();
    carzo_require_user_roles(['driver'], 'signin.php', ['active', 'pending'], 'index.php');
    include 'includes/config.php';
    $page_title = "Driver Dashboard";
    $driverId = (int) ($_SESSION['user']['user_ID'] ?? 0);

    $vehicleStatsResult = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total_listings,
                SUM(listing_status = 'pending') AS pending_approvals,
                SUM(listing_status = 'approved') AS approved_listings,
                SUM(availability_status = 'booked') AS booked_listings
         FROM vehicles
         WHERE owner_user_id = {$driverId}"
    );
    $vehicleStats = $vehicleStatsResult ? mysqli_fetch_assoc($vehicleStatsResult) : [];

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
                SUM(status = 'pending') AS pending_reviews
         FROM reviews
         WHERE driver_id = {$driverId}"
    );
    $reviewStats = $reviewStatsResult ? mysqli_fetch_assoc($reviewStatsResult) : [];

    $disputeStatsResult = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total_disputes,
                SUM(status IN ('open', 'under_review')) AS active_disputes
         FROM complaints
         WHERE target_user_id = {$driverId}
            OR target_vehicle_id IN (SELECT vehicle_id FROM vehicles WHERE owner_user_id = {$driverId})"
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
                        <label>Total Listings:</label>
                        <input type="text" value="<?php echo (int) ($vehicleStats['total_listings'] ?? 0); ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Pending Listing Approvals:</label>
                        <input type="text" value="<?php echo (int) ($vehicleStats['pending_approvals'] ?? 0); ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Approved Listings:</label>
                        <input type="text" value="<?php echo (int) ($vehicleStats['approved_listings'] ?? 0); ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Booked Vehicles:</label>
                        <input type="text" value="<?php echo (int) ($vehicleStats['booked_listings'] ?? 0); ?>" readonly />
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
                        Use the driver menu to manage your own listings, monitor approvals, and respond to booking requests from one place.
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
