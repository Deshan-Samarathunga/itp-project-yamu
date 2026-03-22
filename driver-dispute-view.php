<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/dispute-management.php';
yamu_start_session();
yamu_require_user_roles(['driver'], 'signin.php', ['active', 'verified'], 'access-denied.php');
$page_title = "Driver Disputes";
include 'includes/config.php';

$driverId = (int) ($_SESSION['user']['user_ID'] ?? 0);
$complaintId = isset($_GET['complaint_id']) ? (int) $_GET['complaint_id'] : 0;
$sql = "SELECT c.*, b.booking_No, COALESCE(v.vehicle_title, 'Driver Service') AS service_name, u.full_name AS complainant_name
        FROM complaints c
        LEFT JOIN booking b ON b.booking_id = c.booking_id
        LEFT JOIN vehicles v ON v.vehicle_id = c.target_vehicle_id
        LEFT JOIN users u ON u.user_id = c.complainant_user_id
        WHERE c.complaint_id = {$complaintId}
          AND c.target_user_id = {$driverId}
          AND b.vehicle_ID IS NULL
        LIMIT 1";
$result = mysqli_query($conn, $sql);
$complaint = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;

if (!$complaint) {
    yamu_redirect_with_message('driver-disputes.php', 'error', 'Dispute not found');
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
            <?php $currentAccountPage = 'driver-disputes'; include('includes/account-sidebar.php'); ?>
            <div class="profile-details card">
                <h3>Dispute Details</h3>
                <div class="form-group"><label>Booking No.</label><input type="text" value="<?php echo yamu_e($complaint['booking_No']); ?>" readonly></div>
                <div class="form-group"><label>Service</label><input type="text" value="<?php echo yamu_e($complaint['service_name']); ?>" readonly></div>
                <div class="form-group"><label>Customer</label><input type="text" value="<?php echo yamu_e($complaint['complainant_name']); ?>" readonly></div>
                <div class="form-group"><label>Subject</label><input type="text" value="<?php echo yamu_e($complaint['subject']); ?>" readonly></div>
                <div class="form-group"><label>Category</label><input type="text" value="<?php echo yamu_e($complaint['category']); ?>" readonly></div>
                <div class="form-group"><label>Description</label><textarea readonly><?php echo yamu_e($complaint['description']); ?></textarea></div>
                <div class="form-group"><label>Status</label><input type="text" value="<?php echo yamu_e(ucfirst(str_replace('_', ' ', $complaint['status']))); ?>" readonly></div>
                <form action="includes/driver-dispute-process.php" method="POST" class="signup-form">
                    <input type="hidden" name="complaint_id" value="<?php echo (int) $complaint['complaint_id']; ?>">
                    <div class="form-group">
                        <label for="driver_response">Driver Response</label>
                        <textarea name="driver_response" id="driver_response" rows="6"><?php echo yamu_e($complaint['driver_response']); ?></textarea>
                    </div>
                    <input type="submit" value="Save Response" class="btn main-btn" name="submitDriverResponse">
                </form>
            </div>
        </div>
    </div>
</section>
<?php include('includes/footer.php'); ?>
<script src="assets/js/main.js"></script>
</body>
</html>



