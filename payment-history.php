<?php
require_once __DIR__ . '/includes/auth.php';
yamu_start_session();
yamu_require_user_roles(['customer'], 'signin.php', ['active', 'verified'], 'access-denied.php');
$page_title = "Payment History";
include 'includes/config.php';

$customerId = (int) ($_SESSION['user']['user_ID'] ?? 0);
$sql = "SELECT p.*, b.booking_No, COALESCE(v.vehicle_title, 'Driver Service') AS service_name
        FROM payments p
        LEFT JOIN booking b ON b.booking_id = p.booking_id
        LEFT JOIN vehicles v ON v.vehicle_id = b.vehicle_ID
        WHERE p.customer_id = {$customerId}
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
            <?php $currentAccountPage = 'payments'; include('includes/account-sidebar.php'); ?>
            <div class="profile-details card">
                <h3>Payment History</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Booking No.</th>
                            <th>Service</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Discount</th>
                            <th>Final</th>
                            <th>Status</th>
                            <th>Invoice</th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        <?php if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?php echo yamu_e($row['booking_No']); ?></td>
                                    <td><?php echo yamu_e($row['service_name']); ?></td>
                                    <td><?php echo yamu_e(ucfirst(str_replace('_', ' ', $row['payment_method']))); ?></td>
                                    <td><?php echo yamu_money($row['amount']); ?></td>
                                    <td><?php echo yamu_money($row['discount_amount']); ?></td>
                                    <td><?php echo yamu_money($row['final_amount']); ?></td>
                                    <td><span class="<?php echo yamu_e(yamu_badge_class($row['payment_status'])); ?>"><?php echo yamu_e(ucfirst($row['payment_status'])); ?></span></td>
                                    <td class="action-cell"><div class="table-actions"><a href="invoice.php?payment_id=<?php echo (int) $row['payment_id']; ?>" class="Status-active-badge">View</a></div></td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr><td colspan="8">No payments found yet.</td></tr>
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



