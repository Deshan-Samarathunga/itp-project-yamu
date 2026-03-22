<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/booking-management.php';
yamu_start_session();
yamu_require_user_roles(['staff'], 'signin.php', ['active', 'verified'], 'access-denied.php');
include 'includes/config.php';

$page_title = 'Staff Bookings';
$staffId = (int) ($_SESSION['user']['user_ID'] ?? 0);
$statusFilter = trim((string) ($_GET['status'] ?? ''));
$sql = "SELECT b.*, COALESCE(v.vehicle_title, 'Vehicle Rental') AS service_name, u.full_name AS customer_name, u.email AS customer_email
        FROM booking b
        INNER JOIN vehicles v ON v.vehicle_id = b.vehicle_ID AND v.owner_user_id = {$staffId}
        LEFT JOIN users u ON u.user_id = b.user_ID
        WHERE b.driver_id = {$staffId}
          AND b.vehicle_ID IS NOT NULL";

if ($statusFilter !== '') {
    $sql .= " AND b.booking_status = '" . yamu_escape($conn, yamu_booking_normalize_status($statusFilter)) . "'";
}

$sql .= ' ORDER BY b.created_at DESC, b.booking_id DESC';
$result = mysqli_query($conn, $sql);
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
                $currentAccountPage = 'staff-bookings';
                include 'includes/account-sidebar.php';
                ?>
                <div class="profile-details card">
                    <h3>Rental Bookings</h3>
                    <form action="" method="GET" class="driver-filter-form driver-filter-form-compact">
                        <select name="status">
                            <option value="">All Booking Statuses</option>
                            <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $statusFilter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                        <button type="submit" class="btn second-btn">Filter</button>
                        <a href="staff-bookings.php" class="btn second-btn">Reset</a>
                    </form>

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
                            <?php if ($result && mysqli_num_rows($result) > 0) { ?>
                                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                    <?php $status = yamu_booking_normalize_status($row['booking_status'] ?? 'pending'); ?>
                                    <tr>
                                        <td><?php echo yamu_e($row['booking_No']); ?></td>
                                        <td><?php echo yamu_e($row['service_name']); ?></td>
                                        <td><?php echo yamu_e($row['customer_name']); ?><br><small><?php echo yamu_e($row['customer_email']); ?></small></td>
                                        <td><?php echo yamu_e($row['start_Data']); ?><br>to<br><?php echo yamu_e($row['end_Date']); ?></td>
                                        <td><?php echo yamu_e($row['total']); ?></td>
                                        <td><span class="<?php echo yamu_e(yamu_badge_class($status)); ?>"><?php echo yamu_e(ucfirst($status)); ?></span></td>
                                        <td><span class="<?php echo yamu_e(yamu_badge_class($row['payment_status'])); ?>"><?php echo yamu_e(ucfirst($row['payment_status'])); ?></span></td>
                                        <td class="action-cell">
                                            <div class="table-actions">
                                                <?php if ($status === 'pending') { ?>
                                                    <a href="includes/staff-booking-process.php?action=confirm&booking_id=<?php echo (int) $row['booking_id']; ?>" class="edit-badge" title="Confirm"><i class="ri-check-fill"></i></a>
                                                    <a href="includes/staff-booking-process.php?action=reject&booking_id=<?php echo (int) $row['booking_id']; ?>" class="del-badge" title="Reject"><i class="ri-close-fill"></i></a>
                                                <?php } elseif ($status === 'confirmed') { ?>
                                                    <a href="includes/staff-booking-process.php?action=complete&booking_id=<?php echo (int) $row['booking_id']; ?>" class="edit-badge" title="Complete"><i class="ri-flag-fill"></i></a>
                                                <?php } else { ?>
                                                    <span class="<?php echo yamu_e(yamu_badge_class($status)); ?>"><?php echo yamu_e(ucfirst($status)); ?></span>
                                                <?php } ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="8">No rental bookings found.</td>
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
