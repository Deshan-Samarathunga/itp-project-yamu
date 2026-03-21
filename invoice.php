<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/payment-management.php';
carzo_start_session();
include 'includes/config.php';
$page_title = "Invoice";

$paymentId = isset($_GET['payment_id']) ? (int) $_GET['payment_id'] : 0;
$sql = "SELECT p.*, b.booking_No, b.start_Data, b.end_Date, b.booking_status, b.payment_status AS booking_payment_status,
               v.vehicle_title, v.vehicle_brand,
               customer.full_name AS customer_name, customer.email AS customer_email,
               driver.full_name AS driver_name
        FROM payments p
        LEFT JOIN booking b ON b.booking_id = p.booking_id
        LEFT JOIN vehicles v ON v.vehicle_id = b.vehicle_ID
        LEFT JOIN users customer ON customer.user_id = p.customer_id
        LEFT JOIN users driver ON driver.user_id = p.driver_id
        WHERE p.payment_id = {$paymentId}
        LIMIT 1";
$result = mysqli_query($conn, $sql);
$invoice = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;

if (!$invoice) {
    carzo_redirect_with_message('payment-history.php', 'error', 'Invoice not found');
}

$canAccess = false;
if (carzo_is_admin_authenticated()) {
    $canAccess = true;
} elseif (carzo_is_user_authenticated()) {
    $currentUserId = (int) ($_SESSION['user']['user_ID'] ?? 0);
    $canAccess = in_array($currentUserId, [(int) $invoice['customer_id'], (int) $invoice['driver_id']], true);
}

if (!$canAccess) {
    carzo_redirect_with_message('index.php', 'error', 'You do not have permission to access that invoice');
}
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
            <div class="profile-details card" style="width: 100%;">
                <h3>Payment Invoice</h3>
                <table>
                    <tr><th>Transaction Ref.</th><td><?php echo carzo_e($invoice['transaction_reference']); ?></td><th>Payment Date</th><td><?php echo carzo_e($invoice['paid_at']); ?></td></tr>
                    <tr><th>Booking No.</th><td><?php echo carzo_e($invoice['booking_No']); ?></td><th>Payment Method</th><td><?php echo carzo_e(ucfirst(str_replace('_', ' ', $invoice['payment_method']))); ?></td></tr>
                    <tr><th>Customer</th><td><?php echo carzo_e($invoice['customer_name']); ?> (<?php echo carzo_e($invoice['customer_email']); ?>)</td><th>Driver</th><td><?php echo carzo_e($invoice['driver_name']); ?></td></tr>
                    <tr><th>Vehicle</th><td><?php echo carzo_e($invoice['vehicle_brand'] . ' - ' . $invoice['vehicle_title']); ?></td><th>Rental Dates</th><td><?php echo carzo_e($invoice['start_Data']); ?> to <?php echo carzo_e($invoice['end_Date']); ?></td></tr>
                    <tr><th>Amount</th><td>Rs. <?php echo carzo_money($invoice['amount']); ?></td><th>Discount</th><td>Rs. <?php echo carzo_money($invoice['discount_amount']); ?></td></tr>
                    <tr><th>Final Amount</th><td>Rs. <?php echo carzo_money($invoice['final_amount']); ?></td><th>Promo Code</th><td><?php echo carzo_e($invoice['promo_code'] ?: 'N/A'); ?></td></tr>
                    <tr><th>Payment Status</th><td><span class="<?php echo carzo_e(carzo_badge_class($invoice['payment_status'])); ?>"><?php echo carzo_e(ucfirst($invoice['payment_status'])); ?></span></td><th>Booking Status</th><td><span class="<?php echo carzo_e(carzo_badge_class($invoice['booking_status'])); ?>"><?php echo carzo_e(ucfirst($invoice['booking_status'])); ?></span></td></tr>
                </table>
            </div>
        </div>
    </div>
</section>
<?php include('includes/footer.php'); ?>
<script src="assets/js/main.js"></script>
</body>
</html>
