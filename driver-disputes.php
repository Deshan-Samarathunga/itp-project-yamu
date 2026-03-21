<?php
require_once __DIR__ . '/includes/auth.php';
carzo_start_session();
carzo_require_user_roles(['driver'], 'signin.php', ['active', 'pending', 'verified'], 'access-denied.php');
$page_title = "Driver Disputes";
include 'includes/config.php';

$driverId = (int) ($_SESSION['user']['user_ID'] ?? 0);
$sql = "SELECT c.*, b.booking_No, v.vehicle_title, u.full_name AS complainant_name
        FROM complaints c
        LEFT JOIN booking b ON b.booking_id = c.booking_id
        LEFT JOIN vehicles v ON v.vehicle_id = c.target_vehicle_id
        LEFT JOIN users u ON u.user_id = c.complainant_user_id
        LEFT JOIN vehicles owned_vehicle ON owned_vehicle.vehicle_id = c.target_vehicle_id
        WHERE c.target_user_id = {$driverId}
           OR owned_vehicle.owner_user_id = {$driverId}
        ORDER BY c.created_at DESC, c.complaint_id DESC";
$result = mysqli_query($conn, $sql);
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
            <?php $currentAccountPage = 'driver-disputes'; include('includes/account-sidebar.php'); ?>
            <div class="profile-details card">
                <h3>Disputes</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Booking No.</th>
                            <th>Vehicle</th>
                            <th>Customer</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        <?php if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?php echo carzo_e($row['booking_No']); ?></td>
                                    <td><?php echo carzo_e($row['vehicle_title']); ?></td>
                                    <td><?php echo carzo_e($row['complainant_name']); ?></td>
                                    <td><?php echo carzo_e($row['subject']); ?></td>
                                    <td><span class="<?php echo carzo_e(carzo_badge_class($row['status'])); ?>"><?php echo carzo_e(ucfirst(str_replace('_', ' ', $row['status']))); ?></span></td>
                                    <td class="action-cell"><div class="table-actions"><a href="driver-dispute-view.php?complaint_id=<?php echo (int) $row['complaint_id']; ?>" class="Status-active-badge">View</a></div></td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr><td colspan="6">No disputes found.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php include('includes/footer.php'); ?>
<script src="assets/js/main.js"></script>
</body>
</html>



