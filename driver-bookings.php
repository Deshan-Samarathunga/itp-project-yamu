<?php
    require_once __DIR__ . '/includes/auth.php';
    require_once __DIR__ . '/includes/booking-management.php';
    carzo_start_session();
    carzo_require_user_roles(['driver'], 'signin.php', ['active', 'pending'], 'index.php');
    include 'includes/config.php';
    $page_title = "Driver Bookings";

    $driverId = (int) ($_SESSION['user']['user_ID'] ?? 0);
    $statusFilter = trim((string) ($_GET['status'] ?? ''));
    $sql = "SELECT b.*, v.vehicle_title, u.full_name AS customer_name, u.email AS customer_email
            FROM booking b
            LEFT JOIN vehicles v ON v.vehicle_id = b.vehicle_ID
            LEFT JOIN users u ON u.user_id = b.user_ID
            WHERE b.driver_id = {$driverId}";

    if ($statusFilter !== '') {
        $sql .= " AND b.booking_status = '" . carzo_escape($conn, carzo_booking_normalize_status($statusFilter)) . "'";
    }

    $sql .= ' ORDER BY b.created_at DESC, b.booking_id DESC';
    $result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('includes/header.php'); ?>
</head>
<body>
    <?php include('includes/menu.php'); ?>

    <section class="profile">
        <?php include('includes/alert.php'); ?>
        <div class="container">
            <div class="row">
                <?php
                    $currentAccountPage = 'driver-bookings';
                    include('includes/account-sidebar.php');
                ?>
                <div class="profile-details card">
                    <h3>Booking Requests</h3>
                    <div class="card-title" style="width: 100%; margin: 20px 0;">
                        <form action="" method="GET" style="width: 100%; margin: 0;">
                            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                                <select name="status" style="width: 180px;">
                                    <option value="">All Booking Statuses</option>
                                    <option value="pending" <?php echo ($statusFilter === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo ($statusFilter === 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="rejected" <?php echo ($statusFilter === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                                    <option value="cancelled" <?php echo ($statusFilter === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                    <option value="completed" <?php echo ($statusFilter === 'completed') ? 'selected' : ''; ?>>Completed</option>
                                </select>
                                <button type="submit" class="btn second-btn">Filter</button>
                                <a href="driver-bookings.php" class="btn second-btn">Reset</a>
                            </div>
                        </form>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Booking No</th>
                                <th>Vehicle</th>
                                <th>Customer</th>
                                <th>Dates</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            <?php if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $status = carzo_booking_normalize_status($row['booking_status']); ?>
                                    <tr>
                                        <td><?php echo carzo_e($row['booking_No']); ?></td>
                                        <td><?php echo carzo_e($row['vehicle_title']); ?></td>
                                        <td><?php echo carzo_e($row['customer_name']); ?><br><small><?php echo carzo_e($row['customer_email']); ?></small></td>
                                        <td><?php echo carzo_e($row['start_Data']); ?><br>to<br><?php echo carzo_e($row['end_Date']); ?></td>
                                        <td><?php echo carzo_e($row['total']); ?></td>
                                        <td><span class="<?php echo carzo_e(carzo_badge_class($status)); ?>"><?php echo carzo_e(ucfirst($status)); ?></span></td>
                                        <td><span class="<?php echo carzo_e(carzo_badge_class($row['payment_status'])); ?>"><?php echo carzo_e(ucfirst($row['payment_status'])); ?></span></td>
                                        <td>
                                            <?php if ($status === 'pending') { ?>
                                                <a href="includes/driver-booking-process.php?action=confirm&booking_id=<?php echo $row['booking_id']; ?>" class="edit-badge" title="Confirm"><i class="ri-check-fill"></i></a>
                                                <a href="includes/driver-booking-process.php?action=reject&booking_id=<?php echo $row['booking_id']; ?>" class="del-badge" title="Reject"><i class="ri-close-fill"></i></a>
                                            <?php } elseif ($status === 'confirmed') { ?>
                                                <a href="includes/driver-booking-process.php?action=complete&booking_id=<?php echo $row['booking_id']; ?>" class="edit-badge" title="Complete"><i class="ri-flag-fill"></i></a>
                                            <?php } else { ?>
                                                <span class="<?php echo carzo_e(carzo_badge_class($status)); ?>"><?php echo carzo_e(ucfirst($status)); ?></span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="8">No booking requests found.</td>
                                </tr>
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
