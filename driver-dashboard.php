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

    $dashboardSections = [
        [
            'title' => 'Tour Ad Overview',
            'description' => 'Track how visible and bookable your public driver advertisements are.',
            'cards' => [
                [
                    'label' => 'Total Tour Ads',
                    'value' => (int) ($adStats['total_ads'] ?? 0),
                    'icon' => 'ri-advertisement-line',
                    'accent' => 'blue',
                ],
                [
                    'label' => 'Published Ads',
                    'value' => (int) ($adStats['published_ads'] ?? 0),
                    'icon' => 'ri-rocket-line',
                    'accent' => 'green',
                ],
                [
                    'label' => 'Paused Ads',
                    'value' => (int) ($adStats['paused_ads'] ?? 0),
                    'icon' => 'ri-pause-circle-line',
                    'accent' => 'orange',
                ],
                [
                    'label' => 'Draft Ads',
                    'value' => (int) ($adStats['draft_ads'] ?? 0),
                    'icon' => 'ri-file-list-3-line',
                    'accent' => 'pink',
                ],
                [
                    'label' => 'Available For Tours',
                    'value' => (int) ($adStats['available_ads'] ?? 0),
                    'icon' => 'ri-road-map-line',
                    'accent' => 'teal',
                ],
                [
                    'label' => 'On-Request Ads',
                    'value' => (int) ($adStats['on_request_ads'] ?? 0),
                    'icon' => 'ri-time-line',
                    'accent' => 'amber',
                ],
            ],
        ],
        [
            'title' => 'Bookings',
            'description' => 'See incoming traveler requests and active tours at a glance.',
            'cards' => [
                [
                    'label' => 'Total Requests',
                    'value' => (int) ($bookingStats['total_requests'] ?? 0),
                    'icon' => 'ri-booklet-line',
                    'accent' => 'blue',
                ],
                [
                    'label' => 'Pending Requests',
                    'value' => (int) ($bookingStats['pending_requests'] ?? 0),
                    'icon' => 'ri-timer-line',
                    'accent' => 'amber',
                ],
                [
                    'label' => 'Active Bookings',
                    'value' => (int) ($bookingStats['active_bookings'] ?? 0),
                    'icon' => 'ri-calendar-check-line',
                    'accent' => 'green',
                ],
                [
                    'label' => 'Completed Trips',
                    'value' => (int) ($bookingStats['completed_bookings'] ?? 0),
                    'icon' => 'ri-flag-line',
                    'accent' => 'teal',
                ],
            ],
        ],
        [
            'title' => 'Reviews, Disputes And Earnings',
            'description' => 'Monitor your reputation, support issues, and paid earnings.',
            'cards' => [
                [
                    'label' => 'Average Rating',
                    'value' => number_format((float) ($reviewStats['average_rating'] ?? 0), 1) . ' / 5',
                    'icon' => 'ri-star-smile-line',
                    'accent' => 'pink',
                ],
                [
                    'label' => 'Total Reviews',
                    'value' => (int) ($reviewStats['total_reviews'] ?? 0),
                    'icon' => 'ri-star-line',
                    'accent' => 'blue',
                ],
                [
                    'label' => 'Visible Reviews',
                    'value' => (int) ($reviewStats['visible_reviews'] ?? 0),
                    'icon' => 'ri-question-answer-line',
                    'accent' => 'teal',
                ],
                [
                    'label' => 'Pending Reviews',
                    'value' => (int) ($reviewStats['pending_reviews'] ?? 0),
                    'icon' => 'ri-message-2-line',
                    'accent' => 'orange',
                ],
                [
                    'label' => 'Total Disputes',
                    'value' => (int) ($disputeStats['total_disputes'] ?? 0),
                    'icon' => 'ri-feedback-line',
                    'accent' => 'pink',
                ],
                [
                    'label' => 'Active Disputes',
                    'value' => (int) ($disputeStats['active_disputes'] ?? 0),
                    'icon' => 'ri-alarm-warning-line',
                    'accent' => 'amber',
                ],
                [
                    'label' => 'Paid Transactions',
                    'value' => (int) ($earningsStats['paid_transactions'] ?? 0),
                    'icon' => 'ri-bank-card-line',
                    'accent' => 'green',
                ],
                [
                    'label' => 'Paid Earnings',
                    'value' => 'Rs. ' . carzo_money($earningsStats['paid_earnings'] ?? 0),
                    'icon' => 'ri-money-dollar-circle-line',
                    'accent' => 'teal',
                ],
            ],
        ],
    ];

    $accountSnapshot = [
        'Account Type' => ucfirst((string) ($_SESSION['user']['role'] ?? 'driver')),
        'Account Status' => ucfirst((string) ($_SESSION['user']['account_status'] ?? 'pending')),
        'Verification' => ucfirst(str_replace('_', ' ', (string) ($_SESSION['user']['verification_status'] ?? 'pending'))),
        'License / NIC' => trim((string) ($_SESSION['user']['license_or_nic'] ?? '')) !== '' ? (string) $_SESSION['user']['license_or_nic'] : 'Not added yet',
    ];

    $driverBio = trim((string) ($_SESSION['user']['bio'] ?? ''));
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
                    <div class="driver-dashboard-shell">
                        <div class="driver-dashboard-header">
                            <div>
                                <h3>Driver Dashboard</h3>
                                <p>See your ads, bookings, reviews, disputes, and earnings in the same quick card view used across the admin area.</p>
                            </div>
                            <a href="driver-ads.php" class="btn main-btn">Manage Tour Ads</a>
                        </div>

                        <?php foreach ($dashboardSections as $dashboardSection) { ?>
                            <div class="driver-dashboard-section">
                                <div class="driver-dashboard-section-head">
                                    <div>
                                        <h4><?php echo carzo_e($dashboardSection['title']); ?></h4>
                                        <p><?php echo carzo_e($dashboardSection['description']); ?></p>
                                    </div>
                                </div>
                                <div class="driver-dashboard-grid">
                                    <?php foreach ($dashboardSection['cards'] as $dashboardCard) { ?>
                                        <div class="driver-overview-card accent-<?php echo carzo_e($dashboardCard['accent']); ?>">
                                            <div class="driver-overview-info">
                                                <h5><?php echo carzo_e($dashboardCard['label']); ?></h5>
                                                <span><?php echo carzo_e((string) $dashboardCard['value']); ?></span>
                                            </div>
                                            <div class="driver-overview-icon">
                                                <i class="<?php echo carzo_e($dashboardCard['icon']); ?>"></i>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="driver-dashboard-summary">
                            <div class="driver-dashboard-summary-copy">
                                <h4>Account Snapshot</h4>
                                <p>Use the driver menu to publish your tour ads, keep your traveler-facing profile updated, and manage requests from one place.</p>
                                <?php if ($driverBio !== '') { ?>
                                    <div class="driver-dashboard-bio">
                                        <span>Driver Bio</span>
                                        <p><?php echo nl2br(carzo_e($driverBio)); ?></p>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="driver-dashboard-snapshot">
                                <?php foreach ($accountSnapshot as $snapshotLabel => $snapshotValue) { ?>
                                    <div class="driver-dashboard-snapshot-item">
                                        <span><?php echo carzo_e($snapshotLabel); ?></span>
                                        <strong><?php echo carzo_e($snapshotValue); ?></strong>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
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
