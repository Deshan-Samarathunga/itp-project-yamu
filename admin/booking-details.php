<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/booking-management.php';
carzo_start_session();
carzo_require_admin('index.php');
$page_title = "Booking";
include 'includes/config.php';

$bookingId = isset($_GET['bookingID']) ? (int) $_GET['bookingID'] : (isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : 0);

if ($bookingId <= 0) {
    carzo_redirect_with_message('bookings.php', 'error', 'Booking not found');
}

$sql = "SELECT b.*,
               v.vehicle_title,
               v.price,
               v.location,
               v.registration_number,
               customer.full_name AS customer_name,
               customer.email AS customer_email,
               customer.phone AS customer_phone,
               customer.address AS customer_address,
               customer.city AS customer_city,
               driver.full_name AS driver_name,
               driver.email AS driver_email,
               driver.phone AS driver_phone
        FROM booking b
        LEFT JOIN vehicles v ON v.vehicle_id = b.vehicle_ID
        LEFT JOIN users customer ON customer.user_id = b.user_ID
        LEFT JOIN users driver ON driver.user_id = b.driver_id
        WHERE b.booking_id = {$bookingId}
        LIMIT 1";
$result = mysqli_query($conn, $sql);
$record = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;

if (!$record) {
    carzo_redirect_with_message('bookings.php', 'error', 'Booking not found');
}

$status = carzo_booking_normalize_status($record['booking_status'] ?? 'pending');
$paymentStatus = carzo_booking_normalize_payment_status($record['payment_status'] ?? 'pending');
$totalDays = carzo_booking_total_days($record['start_Data'], $record['end_Date']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('includes/header.php'); ?>
</head>
<body>

    <div class="grid-container">
        <?php include('includes/menu.php'); ?>
        <?php include('includes/aside.php'); ?>

        <main class="main">
            <?php include('../includes/alert.php'); ?>
            <h2>Booking Details</h2>

            <div class="main-cards">
                <div class="card">
                    <div class="card-title">
                        <?php echo carzo_e($record['booking_No']); ?> Booking Details
                    </div>
                    <table>
                        <h4 class="table-title">Customer Details</h4>
                        <tr>
                            <th>Booking No.</th>
                            <td><?php echo carzo_e($record['booking_No']); ?></td>
                            <th>Name</th>
                            <td><?php echo carzo_e($record['customer_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?php echo carzo_e($record['customer_email']); ?></td>
                            <th>Contact No</th>
                            <td><?php echo carzo_e($record['customer_phone']); ?></td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td><?php echo carzo_e($record['customer_address']); ?></td>
                            <th>City</th>
                            <td><?php echo carzo_e($record['customer_city']); ?></td>
                        </tr>
                    </table>

                    <table>
                        <br><br>
                        <h4 class="table-title">Driver Details</h4>
                        <tr>
                            <th>Driver Name</th>
                            <td><?php echo carzo_e($record['driver_name']); ?></td>
                            <th>Email</th>
                            <td><?php echo carzo_e($record['driver_email']); ?></td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td><?php echo carzo_e($record['driver_phone']); ?></td>
                            <th>Vehicle Reg. No.</th>
                            <td><?php echo carzo_e($record['registration_number']); ?></td>
                        </tr>
                    </table>

                    <table>
                        <br><br>
                        <h4 class="table-title">Booking Details</h4>
                        <tr>
                            <th>Vehicle Name</th>
                            <td><?php echo carzo_e($record['vehicle_title']); ?></td>
                            <th>Booking Date</th>
                            <td><?php echo carzo_e($record['booking_Date']); ?></td>
                        </tr>
                        <tr>
                            <th>From Date</th>
                            <td><?php echo carzo_e($record['start_Data']); ?></td>
                            <th>To Date</th>
                            <td><?php echo carzo_e($record['end_Date']); ?></td>
                        </tr>
                        <tr>
                            <th>Total Days</th>
                            <td><?php echo (int) $totalDays; ?></td>
                            <th>Rent Per Day</th>
                            <td><?php echo carzo_e($record['price']); ?></td>
                        </tr>
                        <tr>
                            <th>Location</th>
                            <td><?php echo carzo_e($record['location']); ?></td>
                            <th>Grand Total</th>
                            <td><?php echo carzo_e($record['total']); ?></td>
                        </tr>
                        <tr>
                            <th>Booking Status</th>
                            <td><span class="<?php echo carzo_e(carzo_badge_class($status)); ?>"><?php echo carzo_e(ucfirst($status)); ?></span></td>
                            <th>Payment Status</th>
                            <td><span class="<?php echo carzo_e(carzo_badge_class($paymentStatus)); ?>"><?php echo carzo_e(ucfirst($paymentStatus)); ?></span></td>
                        </tr>
                        <tr>
                            <th>Last Update Date</th>
                            <td><?php echo carzo_e($record['updated_at'] ?: $record['update_Date']); ?></td>
                            <th>Completed / Cancelled</th>
                            <td><?php echo carzo_e($record['completed_at'] ?: $record['cancelled_at']); ?></td>
                        </tr>
                    </table>

                    <div style="margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
                        <a href="bookings.php" class="btn second-btn">Back</a>
                        <?php if ($status === 'pending') { ?>
                            <a href="includes/booking-process.php?action=confirm&booking_id=<?php echo (int) $record['booking_id']; ?>" class="btn main-btn">Confirm</a>
                            <a href="includes/booking-process.php?action=reject&booking_id=<?php echo (int) $record['booking_id']; ?>" class="btn second-btn">Reject</a>
                        <?php } elseif ($status === 'confirmed') { ?>
                            <a href="includes/booking-process.php?action=complete&booking_id=<?php echo (int) $record['booking_id']; ?>" class="btn main-btn">Complete</a>
                            <a href="includes/booking-process.php?action=cancel&booking_id=<?php echo (int) $record['booking_id']; ?>" class="btn second-btn">Cancel</a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </main>

        <footer class="footer">
            <div class="footer__copyright">&copy; 2023 EM</div>
            <div class="footer__signature">Made with love by pure genius</div>
        </footer>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
