<?php
require_once __DIR__ . '/includes/auth.php';
carzo_start_session();
include 'includes/config.php';
$page_title = 'Driver Details';

$adId = isset($_GET['ad_id']) ? (int) $_GET['ad_id'] : 0;
$stmt = $conn->prepare(
    "SELECT da.*, u.full_name, u.email, u.phone, u.city, u.bio, u.profile_pic, u.verification_status,
            COALESCE(review_stats.review_count, 0) AS review_count,
            COALESCE(review_stats.avg_rating, 0) AS avg_rating,
            COALESCE(booking_stats.completed_trips, 0) AS completed_trips
     FROM driver_ads da
     INNER JOIN users u ON u.user_id = da.driver_user_id
     LEFT JOIN (
         SELECT driver_id, COUNT(*) AS review_count, AVG(rating) AS avg_rating
         FROM reviews
         WHERE status = 'visible'
         GROUP BY driver_id
     ) review_stats ON review_stats.driver_id = da.driver_user_id
     LEFT JOIN (
         SELECT driver_id, COUNT(*) AS completed_trips
         FROM booking
         WHERE booking_status = 'completed'
         GROUP BY driver_id
     ) booking_stats ON booking_stats.driver_id = da.driver_user_id
     WHERE da.driver_ad_id = ?
       AND u.role = 'driver'
       AND u.account_status = 'active'
       AND u.verification_status IN ('approved', 'verified')
       AND da.advertisement_status = 'active'
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
         LEFT JOIN users c ON c.user_id = r.customer_id
         WHERE r.driver_id = ?
           AND r.status = 'visible'
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
</head>
<body>
    <?php include 'includes/menu.php'; ?>

    <section class="banner-page">
        <h2><?php echo $driver ? carzo_e($driver['full_name']) : 'Driver Not Found'; ?></h2>
        <div class="banner-link">
            <a href="index.php">Home</a> &gt; <a href="drivers.php">Explore Drivers</a> &gt; <a href="#"><?php echo $driver ? carzo_e($driver['full_name']) : 'Driver Not Found'; ?></a>
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
                                <img src="<?php echo carzo_e(carzo_profile_avatar_path($driver['profile_pic'] ?? 'avatar.png')); ?>" alt="<?php echo carzo_e($driver['full_name']); ?>">
                            </div>
                            <h3><?php echo carzo_e($driver['full_name']); ?></h3>
                            <p><?php echo carzo_e($driver['ad_title']); ?></p>
                            <div class="driver-badge-row centered">
                                <span class="<?php echo carzo_e(carzo_badge_class($driver['availability_status'])); ?>"><?php echo carzo_e(ucwords(str_replace('_', ' ', $driver['availability_status']))); ?></span>
                                <span class="<?php echo carzo_e(carzo_badge_class($driver['verification_status'])); ?>"><?php echo carzo_e(ucfirst(str_replace('_', ' ', $driver['verification_status']))); ?></span>
                            </div>
                            <div class="driver-stats-row detail">
                                <div class="driver-stat">
                                    <strong>Rs. <?php echo carzo_money($driver['daily_rate']); ?></strong>
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
                                    <a href="tel:<?php echo carzo_e($phoneHref); ?>" class="btn main-btn">Call Driver</a>
                                <?php } ?>
                                <a href="mailto:<?php echo carzo_e($driver['email']); ?>" class="btn second-btn">Email Driver</a>
                            </div>
                        </div>
                    </div>

                    <div class="card driver-detail-card">
                        <div class="card-body">
                            <h3><?php echo carzo_e($driver['ad_title']); ?></h3>
                            <?php if (!empty($driver['tagline'])) { ?>
                                <p class="driver-tagline"><?php echo carzo_e($driver['tagline']); ?></p>
                            <?php } ?>

                            <div class="driver-detail-grid">
                                <div class="driver-detail-item">
                                    <span>Service Area</span>
                                    <strong><?php echo carzo_e($driver['service_location']); ?></strong>
                                </div>
                                <div class="driver-detail-item">
                                    <span>Languages</span>
                                    <strong><?php echo carzo_e($driver['languages']); ?></strong>
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
                                    <strong><?php echo carzo_e(ucwords(str_replace('_', ' ', $driver['contact_preference']))); ?></strong>
                                </div>
                                <div class="driver-detail-item">
                                    <span>Based In</span>
                                    <strong><?php echo carzo_e($driver['city'] ?: 'Not specified'); ?></strong>
                                </div>
                            </div>

                            <h4>About This Driver</h4>
                            <p><?php echo nl2br(carzo_e($driver['description'])); ?></p>

                            <?php if (!empty($driver['specialties'])) { ?>
                                <h4>Tour Specialties</h4>
                                <p><?php echo nl2br(carzo_e($driver['specialties'])); ?></p>
                            <?php } ?>

                            <?php if (!empty($driver['bio'])) { ?>
                                <h4>Driver Bio</h4>
                                <p><?php echo nl2br(carzo_e($driver['bio'])); ?></p>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="driver-reviews">
                    <div class="section-header">
                        <h3>Traveler feedback</h3>
                        <h2>Recent reviews for <?php echo carzo_e($driver['full_name']); ?></h2>
                    </div>

                    <?php if ($reviews && $reviews->num_rows > 0) { ?>
                        <div class="reviews-list">
                            <?php while ($review = $reviews->fetch_assoc()) { ?>
                                <div class="review-card">
                                    <div class="review-card-top">
                                        <strong><?php echo carzo_e($review['customer_name'] ?: 'Traveler'); ?></strong>
                                        <span><?php echo (int) $review['rating']; ?>/5</span>
                                    </div>
                                    <p><?php echo carzo_e($review['comment']); ?></p>
                                    <small><?php echo carzo_e($review['created_at']); ?></small>
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
</body>
</html>
