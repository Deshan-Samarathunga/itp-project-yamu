<?php
require_once __DIR__ . '/includes/auth.php';
yamu_start_session();
yamu_require_user_roles(['staff'], 'signin.php', ['active', 'verified'], 'access-denied.php');
include 'includes/config.php';

$page_title = 'Staff Dashboard';
$staffId = (int) ($_SESSION['user']['user_ID'] ?? 0);
$staffProfile = yamu_fetch_role_profile($conn, $staffId, 'staff') ?? [];

$vehicleCounts = [
    'total' => 0,
    'approved' => 0,
    'pending' => 0,
    'inactive' => 0,
];
$vehicleStatsResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total,
            SUM(listing_status = 'approved') AS approved,
            SUM(listing_status = 'pending') AS pending,
            SUM(listing_status = 'inactive') AS inactive
     FROM vehicles
     WHERE owner_user_id = {$staffId}"
);
if ($vehicleStatsResult && mysqli_num_rows($vehicleStatsResult) > 0) {
    $vehicleCounts = mysqli_fetch_assoc($vehicleStatsResult);
}

$bookingCounts = [
    'pending' => 0,
    'confirmed' => 0,
    'completed' => 0,
];
$bookingStatsResult = mysqli_query(
    $conn,
    "SELECT SUM(booking_status = 'pending') AS pending,
            SUM(booking_status = 'confirmed') AS confirmed,
            SUM(booking_status = 'completed') AS completed
     FROM booking
     WHERE driver_id = {$staffId}
       AND vehicle_ID IS NOT NULL"
);
if ($bookingStatsResult && mysqli_num_rows($bookingStatsResult) > 0) {
    $bookingCounts = mysqli_fetch_assoc($bookingStatsResult);
}

$revenueResult = mysqli_query(
    $conn,
    "SELECT COALESCE(SUM(p.final_amount), 0) AS total_revenue
     FROM payments p
     INNER JOIN booking b ON b.booking_id = p.booking_id
     WHERE p.driver_id = {$staffId}
       AND p.payment_status = 'paid'
       AND b.vehicle_ID IS NOT NULL"
);
$revenueRow = $revenueResult && mysqli_num_rows($revenueResult) > 0 ? mysqli_fetch_assoc($revenueResult) : ['total_revenue' => 0];

$recentVehicles = mysqli_query(
    $conn,
    "SELECT vehicle_id, vehicle_title, listing_status, availability_status, updated_at
     FROM vehicles
     WHERE owner_user_id = {$staffId}
     ORDER BY updated_at DESC, vehicle_id DESC
     LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
</head>
<body>
    <?php include 'includes/menu.php'; ?>

    <section class="profile">
        <?php include 'includes/alert.php'; ?>
        <div class="container">
            <div class="row">
                <?php
                $currentAccountPage = 'staff-dashboard';
                include 'includes/account-sidebar.php';
                ?>
                <div class="profile-details card">
                    <h3>Rental Center Dashboard</h3>
                    <p><?php echo yamu_e($staffProfile['store_name'] ?? ($_SESSION['user']['name'] ?? 'Your rental center')); ?></p>

                    <div class="grid-4">
                        <div class="card">
                            <div class="card-body">
                                <h4><?php echo (int) ($vehicleCounts['total'] ?? 0); ?></h4>
                                <p>Total vehicle listings</p>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <h4><?php echo (int) ($vehicleCounts['approved'] ?? 0); ?></h4>
                                <p>Approved vehicles</p>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <h4><?php echo (int) ($bookingCounts['pending'] ?? 0); ?></h4>
                                <p>Pending bookings</p>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <h4>Rs. <?php echo yamu_money($revenueRow['total_revenue'] ?? 0); ?></h4>
                                <p>Paid revenue</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-submit" style="margin: 24px 0;">
                        <a href="staff-vehicles.php" class="btn main-btn">Manage Vehicle Listings</a>
                        <a href="staff-bookings.php" class="btn second-btn">View Rental Bookings</a>
                    </div>

                    <h3>Recent Vehicle Listings</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Listing Status</th>
                                <th>Availability</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            <?php if ($recentVehicles && mysqli_num_rows($recentVehicles) > 0) { ?>
                                <?php while ($vehicle = mysqli_fetch_assoc($recentVehicles)) { ?>
                                    <tr>
                                        <td><?php echo yamu_e($vehicle['vehicle_title']); ?></td>
                                        <td><span class="<?php echo yamu_e(yamu_badge_class($vehicle['listing_status'])); ?>"><?php echo yamu_e(ucfirst($vehicle['listing_status'])); ?></span></td>
                                        <td><span class="<?php echo yamu_e(yamu_badge_class($vehicle['availability_status'])); ?>"><?php echo yamu_e(ucfirst($vehicle['availability_status'])); ?></span></td>
                                        <td><?php echo yamu_e($vehicle['updated_at']); ?></td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="4">No vehicle listings created yet.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
