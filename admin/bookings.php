<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/booking-management.php';
carzo_start_session();
carzo_require_admin('index.php');
include 'includes/config.php';
$page_title = "Booking";

$statusFilter = strtolower(trim((string) ($_GET['status'] ?? '')));
$paymentFilter = strtolower(trim((string) ($_GET['payment_status'] ?? '')));
$allowedStatuses = ['pending', 'confirmed', 'rejected', 'cancelled', 'completed'];
$allowedPayments = ['pending', 'paid', 'failed', 'refunded'];

$statsResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total_bookings,
            SUM(booking_status = 'pending') AS pending_bookings,
            SUM(booking_status = 'confirmed') AS confirmed_bookings,
            SUM(booking_status = 'completed') AS completed_bookings,
            SUM(booking_status IN ('cancelled', 'rejected')) AS closed_bookings
     FROM booking"
);
$stats = $statsResult ? mysqli_fetch_assoc($statsResult) : [];

$sql = "SELECT b.*,
               v.vehicle_title,
               v.registration_number,
               c.full_name AS customer_name,
               c.email AS customer_email,
               d.full_name AS driver_name,
               d.email AS driver_email
        FROM booking b
        LEFT JOIN vehicles v ON v.vehicle_id = b.vehicle_ID
        LEFT JOIN users c ON c.user_id = b.user_ID
        LEFT JOIN users d ON d.user_id = b.driver_id
        WHERE 1 = 1";

if (in_array($statusFilter, $allowedStatuses, true)) {
    $sql .= " AND b.booking_status = '" . carzo_escape($conn, $statusFilter) . "'";
}

if (in_array($paymentFilter, $allowedPayments, true)) {
    $sql .= " AND b.payment_status = '" . carzo_escape($conn, $paymentFilter) . "'";
}

$sql .= " ORDER BY COALESCE(b.updated_at, b.booking_Date) DESC, b.booking_id DESC";
$result = mysqli_query($conn, $sql);
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
            <h2>Manage Bookings</h2>

            <div class="main-overview">
                <div class="overviewcard">
                    <div class="overviewcard-info">
                        <h3>Total Bookings</h3>
                        <span><?php echo (int) ($stats['total_bookings'] ?? 0); ?></span>
                    </div>
                    <div class="overviewcard-icon">
                        <i class="ri-bookmark-line"></i>
                    </div>
                </div>
                <div class="overviewcard">
                    <div class="overviewcard-info">
                        <h3>Pending</h3>
                        <span><?php echo (int) ($stats['pending_bookings'] ?? 0); ?></span>
                    </div>
                    <div class="overviewcard-icon">
                        <i class="ri-time-line"></i>
                    </div>
                </div>
                <div class="overviewcard">
                    <div class="overviewcard-info">
                        <h3>Confirmed</h3>
                        <span><?php echo (int) ($stats['confirmed_bookings'] ?? 0); ?></span>
                    </div>
                    <div class="overviewcard-icon">
                        <i class="ri-check-line"></i>
                    </div>
                </div>
                <div class="overviewcard">
                    <div class="overviewcard-info">
                        <h3>Completed</h3>
                        <span><?php echo (int) ($stats['completed_bookings'] ?? 0); ?></span>
                    </div>
                    <div class="overviewcard-icon">
                        <i class="ri-flag-line"></i>
                    </div>
                </div>
                <div class="overviewcard">
                    <div class="overviewcard-info">
                        <h3>Closed</h3>
                        <span><?php echo (int) ($stats['closed_bookings'] ?? 0); ?></span>
                    </div>
                    <div class="overviewcard-icon">
                        <i class="ri-close-circle-line"></i>
                    </div>
                </div>
            </div>

            <div class="main-cards">
                <div class="card">
                    <h3>Bookings Info</h3>
                    <div class="card-title" style="align-items: flex-end; gap: 10px; flex-wrap: wrap;">
                        <div class="search-box">
                            <input type="text" id="myInput" onkeyup="seacrFunction()" placeholder="Search booking no...">
                        </div>
                        <form action="" method="GET" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <select name="status" style="width: 170px;">
                                <option value="">All Statuses</option>
                                <?php foreach ($allowedStatuses as $allowedStatus) { ?>
                                    <option value="<?php echo carzo_e($allowedStatus); ?>" <?php echo ($statusFilter === $allowedStatus) ? 'selected' : ''; ?>>
                                        <?php echo carzo_e(ucfirst($allowedStatus)); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <select name="payment_status" style="width: 170px;">
                                <option value="">All Payments</option>
                                <?php foreach ($allowedPayments as $allowedPayment) { ?>
                                    <option value="<?php echo carzo_e($allowedPayment); ?>" <?php echo ($paymentFilter === $allowedPayment) ? 'selected' : ''; ?>>
                                        <?php echo carzo_e(ucfirst($allowedPayment)); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <button type="submit" class="btn second-btn">Filter</button>
                            <a href="bookings.php" class="btn second-btn">Reset</a>
                        </form>
                    </div>
                    <table id="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Booking No.</th>
                                <th>Vehicle</th>
                                <th>Customer</th>
                                <th>Driver</th>
                                <th>Dates</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            <?php if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $status = carzo_booking_normalize_status($row['booking_status'] ?? 'pending');
                                    $paymentStatus = carzo_booking_normalize_payment_status($row['payment_status'] ?? 'pending');
                                    ?>
                                    <tr>
                                        <td><?php echo (int) $row['booking_id']; ?></td>
                                        <td><?php echo carzo_e($row['booking_No']); ?></td>
                                        <td>
                                            <?php echo carzo_e($row['vehicle_title']); ?><br>
                                            <small><?php echo carzo_e($row['registration_number']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo carzo_e($row['customer_name']); ?><br>
                                            <small><?php echo carzo_e($row['customer_email']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo carzo_e($row['driver_name']); ?><br>
                                            <small><?php echo carzo_e($row['driver_email']); ?></small>
                                        </td>
                                        <td><?php echo carzo_e($row['start_Data']); ?><br>to<br><?php echo carzo_e($row['end_Date']); ?></td>
                                        <td><?php echo carzo_e($row['total']); ?></td>
                                        <td><span class="<?php echo carzo_e(carzo_badge_class($paymentStatus)); ?>"><?php echo carzo_e(ucfirst($paymentStatus)); ?></span></td>
                                        <td><span class="<?php echo carzo_e(carzo_badge_class($status)); ?>"><?php echo carzo_e(ucfirst($status)); ?></span></td>
                                        <td>
                                            <a href="booking-details.php?bookingID=<?php echo (int) $row['booking_id']; ?>" class="edit-badge" title="View"><i class="ri-eye-line"></i></a>
                                            <?php if ($status === 'pending') { ?>
                                                <a href="includes/booking-process.php?action=confirm&booking_id=<?php echo (int) $row['booking_id']; ?>" class="edit-badge" title="Confirm"><i class="ri-check-fill"></i></a>
                                                <a href="includes/booking-process.php?action=reject&booking_id=<?php echo (int) $row['booking_id']; ?>" class="del-badge" title="Reject"><i class="ri-close-fill"></i></a>
                                            <?php } elseif ($status === 'confirmed') { ?>
                                                <a href="includes/booking-process.php?action=complete&booking_id=<?php echo (int) $row['booking_id']; ?>" class="edit-badge" title="Complete"><i class="ri-flag-fill"></i></a>
                                                <a href="includes/booking-process.php?action=cancel&booking_id=<?php echo (int) $row['booking_id']; ?>" class="del-badge" title="Cancel"><i class="ri-close-circle-fill"></i></a>
                                            <?php } ?>
                                            <a href="includes/booking-process.php?deleteBooking=<?php echo (int) $row['booking_id']; ?>" class="del-badge" title="Delete"><i class="ri-delete-bin-7-fill"></i></a>
                                        </td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="10">No bookings found.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <footer class="footer">
            <div class="footer__copyright">&copy; 2023 EM</div>
            <div class="footer__signature">Made with love by pure genius</div>
        </footer>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        function seacrFunction() {
            var input = document.getElementById("myInput");
            var filter = input.value.toUpperCase();
            var table = document.getElementById("table");
            var tr = table.getElementsByTagName("tr");

            for (var i = 0; i < tr.length; i++) {
                var td = tr[i].getElementsByTagName("td")[1];

                if (td) {
                    var txtValue = td.textContent || td.innerText;
                    tr[i].style.display = txtValue.toUpperCase().indexOf(filter) > -1 ? "" : "none";
                }
            }
        }
    </script>
</body>
</html>
