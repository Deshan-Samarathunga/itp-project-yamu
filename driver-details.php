<?php
require_once __DIR__ . '/includes/auth.php';
yamu_start_session();
include 'includes/config.php';
$page_title = 'Driver Details';
$hasUserRolesTable = yamu_table_exists($conn, 'user_roles');
$driverRoleJoin = $hasUserRolesTable
    ? "INNER JOIN user_roles ur_driver
         ON ur_driver.user_id = u.user_id
        AND ur_driver.role_key = 'driver'
        AND ur_driver.role_status IN ('active', 'verified')
        AND ur_driver.verification_status IN ('approved', 'verified')"
    : '';
$driverVisibilityWhere = $hasUserRolesTable
    ? ''
    : "AND u.role = 'driver'
       AND u.account_status = 'active'
       AND u.verification_status IN ('approved', 'verified')";

$driverVerificationSelect = $hasUserRolesTable
    ? 'ur_driver.verification_status AS driver_role_verification_status,'
    : 'u.verification_status AS driver_role_verification_status,';

$adId = isset($_GET['ad_id']) ? (int) $_GET['ad_id'] : 0;
$stmt = $conn->prepare(
    "SELECT da.*, u.full_name, u.email, u.phone, u.city, u.bio, u.profile_pic, {$driverVerificationSelect}
            COALESCE(review_stats.review_count, 0) AS review_count,
            COALESCE(review_stats.avg_rating, 0) AS avg_rating,
            COALESCE(booking_stats.completed_trips, 0) AS completed_trips
     FROM driver_ads da
     INNER JOIN users u ON u.user_id = da.driver_user_id
     {$driverRoleJoin}
     LEFT JOIN (
         SELECT driver_id, COUNT(*) AS review_count, AVG(rating) AS avg_rating
         FROM reviews r
         LEFT JOIN booking b ON b.booking_id = r.booking_id
         WHERE r.status = 'visible'
           AND b.vehicle_ID IS NULL
         GROUP BY driver_id
     ) review_stats ON review_stats.driver_id = da.driver_user_id
     LEFT JOIN (
         SELECT driver_id, COUNT(*) AS completed_trips
         FROM booking
         WHERE booking_status = 'completed'
           AND vehicle_ID IS NULL
         GROUP BY driver_id
     ) booking_stats ON booking_stats.driver_id = da.driver_user_id
     WHERE da.driver_ad_id = ?
       AND da.advertisement_status = 'active'
       {$driverVisibilityWhere}
     LIMIT 1"
);

$driver = null;

if ($stmt) {
    $stmt->bind_param('i', $adId);
    $stmt->execute();
    $driverResult = $stmt->get_result();
    $driver = $driverResult ? $driverResult->fetch_assoc() : null;
    $stmt->close();
}

if (!$driver) {
    http_response_code(404);
}

$reviews = null;
if ($driver) {
    $reviewStmt = $conn->prepare(
        "SELECT r.rating, r.comment, r.created_at, c.full_name AS customer_name
         FROM reviews r
         LEFT JOIN booking b ON b.booking_id = r.booking_id
         LEFT JOIN users c ON c.user_id = r.customer_id
         WHERE r.driver_id = ?
           AND r.status = 'visible'
           AND b.vehicle_ID IS NULL
         ORDER BY r.created_at DESC, r.review_id DESC
         LIMIT 5"
    );

    if ($reviewStmt) {
        $driverUserId = (int) $driver['driver_user_id'];
        $reviewStmt->bind_param('i', $driverUserId);
        $reviewStmt->execute();
        $reviews = $reviewStmt->get_result();
        $reviewStmt->close();
    }
}

$phoneHref = $driver ? preg_replace('/[^0-9+]/', '', (string) ($driver['phone'] ?? '')) : '';
$driverIsBookable = $driver && in_array(($driver['availability_status'] ?? 'available'), ['available', 'on_request'], true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
</head>
<body>
    <?php include 'includes/menu.php'; ?>

    <section class="banner-page">
        <h2><?php echo $driver ? yamu_e($driver['full_name']) : 'Driver Not Found'; ?></h2>
        <div class="banner-link">
            <a href="index.php">Home</a> &gt; <a href="drivers.php">Explore Drivers</a> &gt; <a href="#"><?php echo $driver ? yamu_e($driver['full_name']) : 'Driver Not Found'; ?></a>
        </div>
    </section>

    <section class="driver-directory">
        <div class="container">
            <?php if (!$driver) { ?>
                <div class="empty-state card">
                    <div class="card-body">
                        <h3>Driver advertisement not found</h3>
                        <p>This driver ad may have been removed, paused, or is no longer public.</p>
                        <a href="drivers.php" class="btn main-btn">Explore Other Drivers</a>
                    </div>
                </div>
            <?php } else { ?>
                <div class="driver-hero">
                    <div class="card driver-info-card">
                        <div class="card-body">
                            <div class="driver-avatar large">
                                <img src="<?php echo yamu_e(yamu_profile_avatar_path($driver['profile_pic'] ?? 'avatar.png')); ?>" alt="<?php echo yamu_e($driver['full_name']); ?>">
                            </div>
                            <h3><?php echo yamu_e($driver['full_name']); ?></h3>
                            <p><?php echo yamu_e($driver['ad_title']); ?></p>
                            <div class="driver-badge-row centered">
                                <span class="<?php echo yamu_e(yamu_badge_class($driver['availability_status'])); ?>"><?php echo yamu_e(ucwords(str_replace('_', ' ', $driver['availability_status']))); ?></span>
                                <span class="<?php echo yamu_e(yamu_badge_class($driver['driver_role_verification_status'])); ?>"><?php echo yamu_e(ucfirst(str_replace('_', ' ', $driver['driver_role_verification_status']))); ?></span>
                            </div>
                            <div class="driver-stats-row detail">
                                <div class="driver-stat">
                                    <strong>Rs. <?php echo yamu_money($driver['daily_rate']); ?></strong>
                                    <span>per day</span>
                                </div>
                                <div class="driver-stat">
                                    <strong><?php echo number_format((float) $driver['avg_rating'], 1); ?>/5</strong>
                                    <span><?php echo (int) $driver['review_count']; ?> reviews</span>
                                </div>
                                <div class="driver-stat">
                                    <strong><?php echo (int) $driver['completed_trips']; ?></strong>
                                    <span>completed trips</span>
                                </div>
                            </div>
                            <div class="driver-card-actions stack">
                                <?php if (!empty($phoneHref)) { ?>
                                    <a href="tel:<?php echo yamu_e($phoneHref); ?>" class="btn main-btn">Call Driver</a>
                                <?php } ?>
                                <a href="mailto:<?php echo yamu_e($driver['email']); ?>" class="btn second-btn">Email Driver</a>
                            </div>
                        </div>
                    </div>

                    <div class="card driver-detail-card">
                        <div class="card-body">
                            <h3><?php echo yamu_e($driver['ad_title']); ?></h3>
                            <?php if (!empty($driver['tagline'])) { ?>
                                <p class="driver-tagline"><?php echo yamu_e($driver['tagline']); ?></p>
                            <?php } ?>

                            <div class="driver-detail-grid">
                                <div class="driver-detail-item">
                                    <span>Service Area</span>
                                    <strong><?php echo yamu_e($driver['service_location']); ?></strong>
                                </div>
                                <div class="driver-detail-item">
                                    <span>Languages</span>
                                    <strong><?php echo yamu_e($driver['languages']); ?></strong>
                                </div>
                                <div class="driver-detail-item">
                                    <span>Experience</span>
                                    <strong><?php echo (int) $driver['experience_years']; ?> years</strong>
                                </div>
                                <div class="driver-detail-item">
                                    <span>Max Group Size</span>
                                    <strong><?php echo (int) $driver['max_group_size']; ?> travelers</strong>
                                </div>
                                <div class="driver-detail-item">
                                    <span>Preferred Contact</span>
                                    <strong><?php echo yamu_e(ucwords(str_replace('_', ' ', $driver['contact_preference']))); ?></strong>
                                </div>
                                <div class="driver-detail-item">
                                    <span>Based In</span>
                                    <strong><?php echo yamu_e($driver['city'] ?: 'Not specified'); ?></strong>
                                </div>
                            </div>

                            <h4>About This Driver</h4>
                            <p><?php echo nl2br(yamu_e($driver['description'])); ?></p>

                            <?php if (!empty($driver['specialties'])) { ?>
                                <h4>Tour Specialties</h4>
                                <p><?php echo nl2br(yamu_e($driver['specialties'])); ?></p>
                            <?php } ?>

                            <?php if (!empty($driver['bio'])) { ?>
                                <h4>Driver Bio</h4>
                                <p><?php echo nl2br(yamu_e($driver['bio'])); ?></p>
                            <?php } ?>

                            <div class="booking-card" style="margin-top: 30px;">
                                <div class="booking-card-title">
                                    <h3>Rs <h3 id="driverPricePerDay"><?php echo yamu_money($driver['daily_rate']); ?></h3> <span>/ Day</span></h3>
                                </div>
                                <div class="booking-card-body">
                                    <h3>Book This Driver</h3>
                                    <p>
                                        Availability: <strong><?php echo yamu_e(ucwords(str_replace('_', ' ', $driver['availability_status']))); ?></strong><br>
                                        Service Area: <strong><?php echo yamu_e($driver['service_location']); ?></strong>
                                    </p>
                                    <form action="includes/booking-process.php" method="POST" class="booking-form">
                                        <input type="hidden" name="service_type" value="driver">
                                        <input type="hidden" name="driverAdID" value="<?php echo (int) $driver['driver_ad_id']; ?>">
                                        <div class="form-group">
                                            <label for="driverStartDate">Start Date:</label>
                                            <input type="date" name="startDate" id="driverStartDate" required <?php echo $driverIsBookable ? '' : 'disabled'; ?>>
                                        </div>
                                        <div class="form-group">
                                            <label for="driverEndDate">End Date:</label>
                                            <input type="date" name="endDate" id="driverEndDate" required <?php echo $driverIsBookable ? '' : 'disabled'; ?>>
                                        </div>
                                        <div class="form-group row price-lable">
                                            <h4>Total</h4>
                                            <h4 id="driverPriceText"></h4>
                                        </div>

                                        <?php if (!$driverIsBookable) { ?>
                                            <p>This driver is not accepting new bookings right now.</p>
                                        <?php } elseif (yamu_is_user_authenticated() && yamu_current_user_role() === 'customer' && in_array(yamu_current_user_role_status(), ['active', 'verified'], true)) { ?>
                                            <div class="form-group">
                                                <input type="submit" value="Book Driver" name="booking" class="btn main-btn">
                                            </div>
                                        <?php } elseif (yamu_is_admin_authenticated()) { ?>
                                            <p>Admin accounts manage the platform but cannot place customer bookings.</p>
                                            <a href="admin/dashboard.php" class="btn main-btn">Open Admin Dashboard</a>
                                        <?php } elseif (yamu_is_user_authenticated() && yamu_current_user_role() === 'driver') { ?>
                                            <p>Driver accounts can publish and manage services, but bookings must be placed through a customer role.</p>
                                            <a href="driver-dashboard.php" class="btn main-btn">Open Driver Dashboard</a>
                                        <?php } elseif (yamu_is_user_authenticated() && yamu_current_user_role() === 'staff') { ?>
                                            <p>Rental center accounts cannot place customer bookings.</p>
                                            <a href="staff-dashboard.php" class="btn main-btn">Open Staff Dashboard</a>
                                        <?php } else { ?>
                                            <a href="signin.php" class="btn main-btn">Login To Book</a>
                                        <?php } ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="driver-reviews">
                    <div class="section-header">
                        <h3>Traveler feedback</h3>
                        <h2>Recent reviews for <?php echo yamu_e($driver['full_name']); ?></h2>
                    </div>

                    <?php if ($reviews && $reviews->num_rows > 0) { ?>
                        <div class="reviews-list">
                            <?php while ($review = $reviews->fetch_assoc()) { ?>
                                <div class="review-card">
                                    <div class="review-card-top">
                                        <strong><?php echo yamu_e($review['customer_name'] ?: 'Traveler'); ?></strong>
                                        <span><?php echo (int) $review['rating']; ?>/5</span>
                                    </div>
                                    <p><?php echo yamu_e($review['comment']); ?></p>
                                    <small><?php echo yamu_e($review['created_at']); ?></small>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <div class="empty-state card">
                            <div class="card-body">
                                <h3>No public reviews yet</h3>
                                <p>This driver advertisement is live, but there are no visible traveler reviews yet.</p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
    <script>
        function yamuDriverBookingTotal() {
            const startInput = document.getElementById('driverStartDate');
            const endInput = document.getElementById('driverEndDate');
            const totalText = document.getElementById('driverPriceText');
            const pricePerDayElement = document.getElementById('driverPricePerDay');

            if (!startInput || !endInput || !totalText || !pricePerDayElement) {
                return;
            }

            const startDate = startInput.value;
            const endDate = endInput.value;

            if (!startDate || !endDate) {
                totalText.textContent = '';
                return;
            }

            const start = new Date(startDate);
            const end = new Date(endDate);

            if (end < start) {
                totalText.textContent = '';
                return;
            }

            const days = Math.max(1, Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24)));
            const total = parseFloat(pricePerDayElement.textContent || '0') * days;
            totalText.textContent = total > 0 ? 'Rs. ' + total.toFixed(2) : '';
        }

        const driverStartInput = document.getElementById('driverStartDate');
        const driverEndInput = document.getElementById('driverEndDate');

        if (driverStartInput) {
            driverStartInput.addEventListener('input', yamuDriverBookingTotal);
        }

        if (driverEndInput) {
            driverEndInput.addEventListener('input', yamuDriverBookingTotal);
        }
    </script>
</body>
</html>
