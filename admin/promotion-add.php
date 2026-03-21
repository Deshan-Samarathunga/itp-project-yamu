<?php
require_once __DIR__ . '/../includes/auth.php';
carzo_start_session();
carzo_require_admin('index.php');
$page_title = "Promotions";
include 'includes/config.php';
$vehicleResult = mysqli_query($conn, "SELECT vehicle_id, vehicle_title FROM vehicles ORDER BY vehicle_title ASC");
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
        <h2>Add Promotion</h2>
        <div class="main-cards"><div class="card">
            <form action="includes/promotion-process.php" method="POST" class="signup-form">
                <div class="form-group"><label for="code">Code</label><input type="text" name="code" id="code" required></div>
                <div class="form-group"><label for="title">Title</label><input type="text" name="title" id="title" required></div>
                <div class="form-group"><label for="description">Description</label><textarea name="description" id="description"></textarea></div>
                <div class="form-group"><label for="discount_type">Discount Type</label><select name="discount_type" id="discount_type"><option value="fixed">Fixed</option><option value="percentage">Percentage</option></select></div>
                <div class="form-group"><label for="discount_value">Discount Value</label><input type="number" step="0.01" name="discount_value" id="discount_value" required></div>
                <div class="form-group"><label for="valid_from">Valid From</label><input type="datetime-local" name="valid_from" id="valid_from"></div>
                <div class="form-group"><label for="valid_to">Valid To</label><input type="datetime-local" name="valid_to" id="valid_to"></div>
                <div class="form-group"><label for="usage_limit">Usage Limit</label><input type="number" name="usage_limit" id="usage_limit"></div>
                <div class="form-group"><label for="minimum_booking_amount">Minimum Booking Amount</label><input type="number" step="0.01" name="minimum_booking_amount" id="minimum_booking_amount"></div>
                <div class="form-group"><label for="status">Status</label><select name="status" id="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                <div class="form-group"><label for="applicable_vehicle_id">Applicable Vehicle</label><select name="applicable_vehicle_id" id="applicable_vehicle_id"><option value="">All vehicles</option><?php while ($vehicleResult && $vehicle = mysqli_fetch_assoc($vehicleResult)) { ?><option value="<?php echo (int) $vehicle['vehicle_id']; ?>"><?php echo carzo_e($vehicle['vehicle_title']); ?></option><?php } ?></select></div>
                <input type="submit" value="Create Promotion" class="btn main-btn" name="createPromotion">
            </form>
        </div></div>
    </main>
    <footer class="footer"><div class="footer__copyright">&copy; 2023 EM</div><div class="footer__signature">Made with love by pure genius</div></footer>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>
