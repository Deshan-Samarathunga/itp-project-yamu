<?php
    require_once __DIR__ . '/../includes/auth.php';
    carzo_start_session();
    carzo_require_admin('index.php');
    include 'includes/config.php'; // Database Connection   
    $page_title = "Dashboard"; 

    $overviewMetrics = [
        'listed_vehicles' => 0,
        'registered_users' => 0,
        'listed_brands' => 0,
        'total_bookings' => 0,
        'pending_listings' => 0,
        'pending_reviews' => 0,
        'open_disputes' => 0,
        'paid_revenue' => 0,
        'active_promotions' => 0,
    ];

    $overviewQueries = [
        'listed_vehicles' => "SELECT COUNT(*) AS metric_value FROM vehicles",
        'registered_users' => "SELECT COUNT(*) AS metric_value FROM users",
        'listed_brands' => "SELECT COUNT(*) AS metric_value FROM brands",
        'total_bookings' => "SELECT COUNT(*) AS metric_value FROM booking",
        'pending_listings' => "SELECT COUNT(*) AS metric_value FROM vehicles WHERE listing_status = 'pending'",
        'pending_reviews' => "SELECT COUNT(*) AS metric_value FROM reviews WHERE status = 'pending'",
        'open_disputes' => "SELECT COUNT(*) AS metric_value FROM complaints WHERE status IN ('open', 'under_review')",
        'paid_revenue' => "SELECT COALESCE(SUM(final_amount), 0) AS metric_value FROM payments WHERE payment_status = 'paid'",
        'active_promotions' => "SELECT COUNT(*) AS metric_value FROM promotions WHERE status = 'active' AND (valid_to IS NULL OR valid_to >= NOW())",
    ];

    foreach ($overviewQueries as $metricKey => $metricSql) {
        $metricResult = mysqli_query($conn, $metricSql);
        if ($metricResult && mysqli_num_rows($metricResult) > 0) {
            $metricRow = mysqli_fetch_assoc($metricResult);
            $overviewMetrics[$metricKey] = $metricRow['metric_value'] ?? 0;
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        include('includes/header.php');
    ?>
</head>
<body>

    <div class="grid-container">
        <!-- Navbar -->
        <?php
            include('includes/menu.php');
        ?>

        <!-- Aside Section -->
        <?php
            include('includes/aside.php');
        ?>

        <main class="main">
            <!-- Allert Box -->
            <?php
                include('../includes/alert.php');
            ?>
            <h2>Overview</h2>
            <div class="main-overview">
                <div class="overviewcard">
                    <div class="overviewcard-info">
                        <h3>Listed Vehicles</h3>
                        <span><?php echo (int) $overviewMetrics['listed_vehicles']; ?></span>
                    </div>
                    <div class="overviewcard-icon">
                        <i class="ri-car-line"></i>
                    </div>  
                </div>
                <div class="overviewcard">
                    <div class="overviewcard-info">
                    <h3>Registered Users</h3>
                    <span><?php echo (int) $overviewMetrics['registered_users']; ?></span>
                    </div>

                    <div class="overviewcard-icon">
                        <i class="ri-user-line"></i>
                    </div>
                </div>
                <div class="overviewcard">
                    <div class="overviewcard-info">
                        <h3>Listed Brands</h3>
                        <span><?php echo (int) $overviewMetrics['listed_brands']; ?></span>
                    </div>
                    <div class="overviewcard-icon">
                        <i class="ri-file-copy-2-line"></i>
                    </div>
                </div>
                <div class="overviewcard">
                    <div class="overviewcard-info">
                        <h3>Total Bookings</h3>
                        <span><?php echo (int) $overviewMetrics['total_bookings']; ?></span>
                    </div>
                    <div class="overviewcard-icon">
                        <i class="ri-edit-line"></i>
                    </div>
                </div>
                <div class="overviewcard">
                    <div class="overviewcard-info">
                        <h3>Paid Revenue</h3>
                        <span>Rs. <?php echo carzo_money($overviewMetrics['paid_revenue']); ?></span> 
                    </div>
                    <div class="overviewcard-icon">
                        <i class="ri-money-dollar-circle-line"></i>
                    </div>
                </div>
                <div class="overviewcard">
                    <div class="overviewcard-info">
                        <h3>Pending Listings</h3>
                        <span><?php echo (int) $overviewMetrics['pending_listings']; ?></span>
                    </div>
                    <div class="overviewcard-icon">
                        <i class="ri-time-line"></i>
                    </div>
                </div>
                <div class="overviewcard">
                    <div class="overviewcard-info">
                        <h3>Pending Reviews</h3>
                        <span><?php echo (int) $overviewMetrics['pending_reviews']; ?></span>
                    </div>
                    <div class="overviewcard-icon">
                        <i class="ri-star-half-line"></i>
                    </div>
                </div>
                <div class="overviewcard">
                    <div class="overviewcard-info">
                        <h3>Open Disputes</h3>
                        <span><?php echo (int) $overviewMetrics['open_disputes']; ?></span>
                    </div>
                    <div class="overviewcard-icon">
                        <i class="ri-chat-warning-line"></i>
                    </div>
                </div>
                <div class="overviewcard">
                    <div class="overviewcard-info">
                        <h3>Active Promotions</h3>
                        <span><?php echo (int) $overviewMetrics['active_promotions']; ?></span>
                    </div>
                    <div class="overviewcard-icon">
                        <i class="ri-coupon-3-line"></i>
                    </div>
                </div>
            </div>
            <!-- <div class="main-cards">
                <div class="card">Card</div>
                <div class="card">Card</div>
                <div class="card">Card</div>
            </div> -->
        </main>

        <footer class="footer">
            <div class="footer__copyright">&copy; 2018 MTH</div>
            <div class="footer__signature">Made with love by pure genius</div>
        </footer>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
