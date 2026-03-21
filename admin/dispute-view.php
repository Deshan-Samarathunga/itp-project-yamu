<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/dispute-management.php';
carzo_start_session();
carzo_require_admin('index.php');
$page_title = "Disputes";
include 'includes/config.php';

$complaintId = isset($_GET['complaint_id']) ? (int) $_GET['complaint_id'] : 0;
$sql = "SELECT c.*, b.booking_No, v.vehicle_title, u.full_name AS complainant_name, t.full_name AS target_name
        FROM complaints c
        LEFT JOIN booking b ON b.booking_id = c.booking_id
        LEFT JOIN vehicles v ON v.vehicle_id = c.target_vehicle_id
        LEFT JOIN users u ON u.user_id = c.complainant_user_id
        LEFT JOIN users t ON t.user_id = c.target_user_id
        WHERE c.complaint_id = {$complaintId}
        LIMIT 1";
$result = mysqli_query($conn, $sql);
$complaint = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;

if (!$complaint) {
    carzo_redirect_with_message('disputes.php', 'error', 'Dispute not found');
}
?>
<!DOCTYPE html>
<html lang="en">
<head><?php include('includes/header.php'); ?></head>
<body>
<div class="grid-container">
    <?php include('includes/menu.php'); ?>
    <?php include('includes/aside.php'); ?>
    <main class="main">
        <?php include('../includes/alert.php'); ?>
        <h2>Dispute Details</h2>
        <div class="main-cards">
            <div class="card">
                <div class="form-group"><label>Booking No.</label><input type="text" value="<?php echo carzo_e($complaint['booking_No']); ?>" readonly></div>
                <div class="form-group"><label>Vehicle</label><input type="text" value="<?php echo carzo_e($complaint['vehicle_title']); ?>" readonly></div>
                <div class="form-group"><label>Customer</label><input type="text" value="<?php echo carzo_e($complaint['complainant_name']); ?>" readonly></div>
                <div class="form-group"><label>Target</label><input type="text" value="<?php echo carzo_e($complaint['target_name']); ?>" readonly></div>
                <div class="form-group"><label>Subject</label><input type="text" value="<?php echo carzo_e($complaint['subject']); ?>" readonly></div>
                <div class="form-group"><label>Category</label><input type="text" value="<?php echo carzo_e($complaint['category']); ?>" readonly></div>
                <div class="form-group"><label>Description</label><textarea readonly><?php echo carzo_e($complaint['description']); ?></textarea></div>
                <div class="form-group"><label>Driver Response</label><textarea readonly><?php echo carzo_e($complaint['driver_response']); ?></textarea></div>
                <form action="includes/dispute-process.php" method="POST" class="signup-form">
                    <input type="hidden" name="complaint_id" value="<?php echo (int) $complaint['complaint_id']; ?>">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <option value="open" <?php echo $complaint['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                            <option value="under_review" <?php echo $complaint['status'] === 'under_review' ? 'selected' : ''; ?>>Under Review</option>
                            <option value="resolved" <?php echo $complaint['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                            <option value="rejected" <?php echo $complaint['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="admin_notes">Admin Notes</label>
                        <textarea name="admin_notes" id="admin_notes"><?php echo carzo_e($complaint['admin_notes']); ?></textarea>
                    </div>
                    <input type="submit" value="Update Dispute" class="btn main-btn" name="updateDispute">
                </form>
            </div>
        </div>
    </main>
    <footer class="footer"><div class="footer__copyright">&copy; 2023 EM</div><div class="footer__signature">Made with love by pure genius</div></footer>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>
