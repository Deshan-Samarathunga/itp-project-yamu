<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/dispute-management.php';
carzo_start_session();
carzo_require_user_roles(['customer'], 'signin.php', ['active', 'verified'], 'access-denied.php');
$page_title = "Booking Dispute";
include 'includes/config.php';

$customerId = (int) ($_SESSION['user']['user_ID'] ?? 0);
$bookingId = isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : 0;
$booking = carzo_booking_fetch($conn, $bookingId);

if (!$booking || (int) ($booking['user_ID'] ?? 0) !== $customerId) {
    carzo_redirect_with_message('my-booking.php', 'error', 'Booking not found');
}

$bookingInfoResult = mysqli_query($conn, "SELECT b.booking_No, v.vehicle_title FROM booking b LEFT JOIN vehicles v ON v.vehicle_id = b.vehicle_ID WHERE b.booking_id = {$bookingId} LIMIT 1");
$bookingInfo = ($bookingInfoResult && mysqli_num_rows($bookingInfoResult) > 0) ? mysqli_fetch_assoc($bookingInfoResult) : ['booking_No' => '', 'vehicle_title' => ''];
$existingResult = mysqli_query($conn, "SELECT * FROM complaints WHERE booking_id = {$bookingId} AND complainant_user_id = {$customerId} ORDER BY created_at DESC");
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
            <?php $currentAccountPage = 'disputes'; include('includes/account-sidebar.php'); ?>
            <div class="profile-details card">
                <h3>Raise a Dispute</h3>
                <div class="form-group">
                    <label>Booking No.</label>
                    <input type="text" value="<?php echo carzo_e($bookingInfo['booking_No']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Vehicle</label>
                    <input type="text" value="<?php echo carzo_e($bookingInfo['vehicle_title']); ?>" readonly>
                </div>
                <form action="includes/dispute-process.php" method="POST" enctype="multipart/form-data" class="signup-form">
                    <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" name="subject" id="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select name="category" id="category" required>
                            <option value="">Select Category</option>
                            <option value="vehicle_issue">Vehicle Issue</option>
                            <option value="driver_conduct">Driver Conduct</option>
                            <option value="booking_problem">Booking Problem</option>
                            <option value="payment_problem">Payment Problem</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" rows="6" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="attachment">Attachment</label>
                        <input type="file" name="attachment" id="attachment">
                    </div>
                    <input type="submit" value="Submit Dispute" class="btn main-btn" name="submitDispute">
                </form>

                <h3 style="margin-top: 30px;">Previous Disputes</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Driver Response</th>
                            <th>Admin Notes</th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        <?php if ($existingResult && mysqli_num_rows($existingResult) > 0) {
                            while ($row = mysqli_fetch_assoc($existingResult)) { ?>
                                <tr>
                                    <td><?php echo carzo_e($row['subject']); ?></td>
                                    <td><?php echo carzo_e($row['category']); ?></td>
                                    <td><span class="<?php echo carzo_e(carzo_badge_class($row['status'])); ?>"><?php echo carzo_e(ucfirst(str_replace('_', ' ', $row['status']))); ?></span></td>
                                    <td><?php echo carzo_e($row['driver_response']); ?></td>
                                    <td><?php echo carzo_e($row['admin_notes']); ?></td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr><td colspan="5">No disputes submitted for this booking yet.</td></tr>
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



