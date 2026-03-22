<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/payment-management.php';
yamu_start_session();
yamu_require_user_roles(['customer'], 'signin.php', ['active', 'verified'], 'access-denied.php');
$page_title = "Booking Payment";
include 'includes/config.php';

$customerId = (int) ($_SESSION['user']['user_ID'] ?? 0);
$bookingId = isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : 0;
$sql = "SELECT b.*, COALESCE(v.vehicle_title, 'Driver Service') AS service_name, v.vehicle_brand
        FROM booking b
        LEFT JOIN vehicles v ON v.vehicle_id = b.vehicle_ID
        WHERE b.booking_id = {$bookingId}
          AND b.user_ID = {$customerId}
        LIMIT 1";
$result = mysqli_query($conn, $sql);
$booking = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;

if (!$booking) {
    yamu_redirect_with_message('my-booking.php', 'error', 'Booking not found');
}

if (yamu_booking_normalize_payment_status($booking['payment_status'] ?? 'pending') === 'paid') {
    yamu_redirect_with_message('payment-history.php', 'error', 'This booking is already paid');
}

$promotionSql = "SELECT p.*, v.vehicle_title AS applicable_vehicle_title
                 FROM promotions p
                 LEFT JOIN vehicles v ON v.vehicle_id = p.applicable_vehicle_id
                 WHERE p.status = 'active'
                   AND (p.valid_from IS NULL OR p.valid_from <= NOW())
                   AND (p.valid_to IS NULL OR p.valid_to >= NOW())
                 ORDER BY p.created_at DESC, p.promotion_id DESC";
$promotionResult = mysqli_query($conn, $promotionSql);
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
                <h3>Pay For Booking</h3>
                <div class="form-group">
                    <label>Booking No.</label>
                    <input type="text" value="<?php echo yamu_e($booking['booking_No']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Service</label>
                    <input type="text" value="<?php echo yamu_e($booking['service_name']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Booking Status</label>
                    <input type="text" value="<?php echo yamu_e(ucfirst($booking['booking_status'])); ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Original Total</label>
                    <input type="text" value="Rs. <?php echo yamu_money($booking['total']); ?>" readonly>
                </div>
                <form action="includes/payment-process.php" method="POST" class="signup-form">
                    <input type="hidden" name="booking_id" value="<?php echo (int) $booking['booking_id']; ?>">
                    <div class="form-group">
                        <label for="promo_code">Promo Code</label>
                        <input type="text" name="promo_code" id="promo_code" value="<?php echo yamu_e($booking['promo_code']); ?>" placeholder="Optional promo code">
                    </div>
                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select name="payment_method" id="payment_method" required>
                            <option value="mock_card">Mock Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="wallet">Wallet</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>
                    <input type="submit" value="Pay Now" class="btn main-btn" name="payBooking">
                </form>

                <h3 style="margin-top: 30px;">Active Promotions</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Title</th>
                            <th>Discount</th>
                            <th>Valid To</th>
                            <th>Vehicle</th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        <?php if ($promotionResult && mysqli_num_rows($promotionResult) > 0) {
                            while ($row = mysqli_fetch_assoc($promotionResult)) { ?>
                                <tr>
                                    <td><?php echo yamu_e($row['code']); ?></td>
                                    <td><?php echo yamu_e($row['title']); ?></td>
                                    <td><?php echo yamu_e($row['discount_type'] === 'percentage' ? yamu_money($row['discount_value']) . '%' : 'Rs. ' . yamu_money($row['discount_value'])); ?></td>
                                    <td><?php echo yamu_e($row['valid_to']); ?></td>
                                    <td><?php echo yamu_e($row['applicable_vehicle_title'] ?: 'All vehicles'); ?></td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr><td colspan="5">No active promotions available right now.</td></tr>
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



