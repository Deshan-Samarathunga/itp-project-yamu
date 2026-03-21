<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/driver-ad-options.php';
carzo_start_session();
include 'includes/config.php';
$page_title = 'Explore Drivers';
$serviceLocations = carzo_driver_service_locations();

$search = trim((string) ($_GET['search'] ?? ''));
$location = trim((string) ($_GET['location'] ?? ''));
$availabilityFilter = strtolower(trim((string) ($_GET['availability'] ?? '')));
$allowedAvailabilityStatuses = ['available', 'busy', 'on_request'];

$sql = "SELECT da.*, u.full_name, u.email, u.phone, u.city, u.profile_pic, u.bio, u.verification_status,
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
        WHERE u.role = 'driver'
          AND u.account_status = 'active'
          AND u.verification_status IN ('approved', 'verified')
          AND da.advertisement_status = 'active'";

if ($search !== '') {
    $safeSearch = carzo_escape($conn, $search);
    $sql .= " AND (
        da.ad_title LIKE '%{$safeSearch}%'
        OR da.tagline LIKE '%{$safeSearch}%'
        OR da.service_location LIKE '%{$safeSearch}%'
        OR da.languages LIKE '%{$safeSearch}%'
        OR da.specialties LIKE '%{$safeSearch}%'
        OR u.full_name LIKE '%{$safeSearch}%'
    )";
}

if ($location !== '') {
    $safeLocation = carzo_escape($conn, $location);
    $sql .= " AND (da.service_location LIKE '%{$safeLocation}%' OR u.city LIKE '%{$safeLocation}%')";
}

if (in_array($availabilityFilter, $allowedAvailabilityStatuses, true)) {
    $sql .= " AND da.availability_status = '" . carzo_escape($conn, $availabilityFilter) . "'";
}

$sql .= " ORDER BY FIELD(da.availability_status, 'available', 'on_request', 'busy'), review_stats.avg_rating DESC, da.updated_at DESC, da.driver_ad_id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
</head>
<body>
    <?php include 'includes/menu.php'; ?>

    <section class="banner-page">
        <h2>Explore Drivers</h2>
        <div class="banner-link">
            <a href="index.php">Home</a> &gt; <a href="drivers.php">Explore Drivers</a>
        </div>
    </section>

    <section class="driver-directory">
        <div class="container">
            <div class="section-header">
                <h3>Find a local tour driver</h3>
                <h2>Explore trusted drivers for your next trip</h2>
                <p>Browse verified drivers who advertise tour services, day trips, chauffeur work, and airport transfers.</p>
            </div>

            <div class="driver-filter-bar">
                <form action="" method="GET" class="driver-filter-form">
                    <input type="text" name="search" value="<?php echo carzo_e($search); ?>" placeholder="Search by driver, tour style, language..." />
                    <select name="location">
                        <option value="">All Service Locations</option>
                        <?php if ($location !== '' && !carzo_driver_service_location_exists($location)) { ?>
                            <option value="<?php echo carzo_e($location); ?>" selected><?php echo carzo_e($location); ?></option>
                        <?php } ?>
                        <?php foreach ($serviceLocations as $serviceLocation) { ?>
                            <option value="<?php echo carzo_e($serviceLocation); ?>" <?php echo $location === $serviceLocation ? 'selected' : ''; ?>>
                                <?php echo carzo_e($serviceLocation); ?>
                            </option>
                        <?php } ?>
                    </select>
                    <select name="availability">
                        <option value="">All Availability</option>
                        <option value="available" <?php echo $availabilityFilter === 'available' ? 'selected' : ''; ?>>Available</option>
                        <option value="on_request" <?php echo $availabilityFilter === 'on_request' ? 'selected' : ''; ?>>On Request</option>
                        <option value="busy" <?php echo $availabilityFilter === 'busy' ? 'selected' : ''; ?>>Busy</option>
                    </select>
                    <button type="submit" class="btn main-btn">Find Drivers</button>
                    <a href="drivers.php" class="btn second-btn">Reset</a>
                </form>
            </div>

            <div class="grid-3">
                <?php if ($result && mysqli_num_rows($result) > 0) {
                    while ($driver = mysqli_fetch_assoc($result)) {
                        $avatar = carzo_profile_avatar_path($driver['profile_pic'] ?? 'avatar.png');
                        $phoneHref = preg_replace('/[^0-9+]/', '', (string) ($driver['phone'] ?? ''));
                        ?>
                        <div class="card driver-card">
                            <div class="card-body">
                                <div class="driver-card-top">
                                    <div class="driver-avatar">
                                        <img src="<?php echo carzo_e($avatar); ?>" alt="<?php echo carzo_e($driver['full_name']); ?>">
                                    </div>
                                    <div class="driver-heading">
                                        <h3><?php echo carzo_e($driver['full_name']); ?></h3>
                                        <p><?php echo carzo_e($driver['ad_title']); ?></p>
                                    </div>
                                </div>

                                <div class="driver-badge-row">
                                    <span class="<?php echo carzo_e(carzo_badge_class($driver['availability_status'])); ?>"><?php echo carzo_e(ucwords(str_replace('_', ' ', $driver['availability_status']))); ?></span>
                                    <span class="<?php echo carzo_e(carzo_badge_class($driver['verification_status'])); ?>"><?php echo carzo_e(ucfirst(str_replace('_', ' ', $driver['verification_status']))); ?></span>
                                </div>

                                <?php if (!empty($driver['tagline'])) { ?>
                                    <p class="driver-tagline"><?php echo carzo_e($driver['tagline']); ?></p>
                                <?php } ?>

                                <div class="driver-meta-list">
                                    <span><i class="ri-map-pin-line"></i> <?php echo carzo_e($driver['service_location']); ?></span>
                                    <span><i class="ri-global-line"></i> <?php echo carzo_e($driver['languages']); ?></span>
                                    <span><i class="ri-time-line"></i> <?php echo (int) $driver['experience_years']; ?> years experience</span>
                                    <span><i class="ri-group-line"></i> Up to <?php echo (int) $driver['max_group_size']; ?> travelers</span>
                                </div>

                                <div class="driver-stats-row">
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

                                <p class="driver-summary"><?php echo carzo_e($driver['description']); ?></p>

                                <div class="driver-card-actions">
                                    <a href="driver-details.php?ad_id=<?php echo (int) $driver['driver_ad_id']; ?>" class="btn main-btn">View Details</a>
                                    <?php if (!empty($phoneHref)) { ?>
                                        <a href="tel:<?php echo carzo_e($phoneHref); ?>" class="btn second-btn">Call Driver</a>
                                    <?php } else { ?>
                                        <a href="mailto:<?php echo carzo_e($driver['email']); ?>" class="btn second-btn">Email Driver</a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    <?php }
                } else { ?>
                    <div class="empty-state card">
                        <div class="card-body">
                            <h3>No driver advertisements found</h3>
                            <p>Try changing your search, or check back after more drivers publish their tour ads.</p>
                            <a href="drivers.php" class="btn main-btn">Clear Filters</a>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
