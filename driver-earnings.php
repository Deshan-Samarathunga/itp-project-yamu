<?php
require_once __DIR__ . '/includes/auth.php';
carzo_start_session();
carzo_require_user_roles(['driver'], 'signin.php', ['active', 'pending', 'verified'], 'access-denied.php');
$page_title = "Driver Earnings";
include 'includes/config.php';

$driverId = (int) ($_SESSION['user']['user_ID'] ?? 0);
$statsResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS payment_count,
            SUM(final_amount) AS total_earnings
     FROM payments
     WHERE driver_id = {$driverId}
       AND payment_status = 'paid'"
);
$stats = $statsResult ? mysqli_fetch_assoc($statsResult) : [];

$sql = "SELECT p.*, b.booking_No, b.booking_status, v.vehicle_title
        FROM payments p
        LEFT JOIN booking b ON b.booking_id = p.booking_id
        LEFT JOIN vehicles v ON v.vehicle_id = b.vehicle_ID
        WHERE p.driver_id = {$driverId}
        ORDER BY p.created_at DESC, p.payment_id DESC";
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
            <?php $currentAccountPage = 'driver-earnings'; include('includes/account-sidebar.php'); ?>
            <div class="profile-details card">
                <h3>Earnings</h3>
                <div class="form-group"><label>Paid Transactions</label><input type="text" value="<?php echo (int) ($stats['payment_count'] ?? 0); ?>" readonly></div>
                <div class="form-group"><label>Total Earnings</label><input type="text" value="Rs. <?php echo carzo_money($stats['total_earnings'] ?? 0); ?>" readonly></div>
                <table>
                    <thead>
                        <tr>
                            <th>Booking No.</th>
                            <th>Vehicle</th>
                            <th>Booking Status</th>
                            <th>Method</th>
                            <th>Final Amount</th>
                            <th>Payment Status</th>
                            <th>Invoice</th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        <?php if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?php echo carzo_e($row['booking_No']); ?></td>
                                    <td><?php echo carzo_e($row['vehicle_title']); ?></td>
                                    <td><span class="<?php echo carzo_e(carzo_badge_class($row['booking_status'])); ?>"><?php echo carzo_e(ucfirst($row['booking_status'])); ?></span></td>
                                    <td><?php echo carzo_e(ucfirst(str_replace('_', ' ', $row['payment_method']))); ?></td>
                                    <td><?php echo carzo_money($row['final_amount']); ?></td>
                                    <td><span class="<?php echo carzo_e(carzo_badge_class($row['payment_status'])); ?>"><?php echo carzo_e(ucfirst($row['payment_status'])); ?></span></td>
                                    <td class="action-cell"><div class="table-actions"><a href="invoice.php?payment_id=<?php echo (int) $row['payment_id']; ?>" class="Status-active-badge">View</a></div></td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr><td colspan="7">No payment records found.</td></tr>
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



