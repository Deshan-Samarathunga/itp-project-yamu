<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/promotion-management.php';
yamu_start_session();
$page_title = "Promotions";
include 'includes/config.php';

$sql = "SELECT p.*, v.vehicle_title
        FROM promotions p
        LEFT JOIN vehicles v ON v.vehicle_id = p.applicable_vehicle_id
        WHERE p.status = 'active'
          AND (p.valid_from IS NULL OR p.valid_from <= NOW())
          AND (p.valid_to IS NULL OR p.valid_to >= NOW())
        ORDER BY p.created_at DESC, p.promotion_id DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head><?php include('includes/header.php'); ?></head>
<body>
<?php include('includes/menu.php'); ?>
<section class="cars">
    <?php include('includes/alert.php'); ?>
    <div class="container">
        <div class="section-header">
            <h3>Save More On Your Next Rental</h3>
            <h2>Active Promotions</h2>
        </div>
        <div class="grid-3">
            <?php if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) { ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <h4 class="card-title"><?php echo yamu_e($row['title']); ?></h4>
                                <h5 class="card-title"><?php echo yamu_e($row['code']); ?></h5>
                            </div>
                            <p><?php echo yamu_e($row['description']); ?></p>
                            <p><strong>Discount:</strong> <?php echo yamu_e($row['discount_type'] === 'percentage' ? yamu_money($row['discount_value']) . '%' : 'Rs. ' . yamu_money($row['discount_value'])); ?></p>
                            <p><strong>Valid:</strong> <?php echo yamu_e($row['valid_from']); ?> to <?php echo yamu_e($row['valid_to']); ?></p>
                            <p><strong>Vehicle:</strong> <?php echo yamu_e($row['vehicle_title'] ?: 'All vehicles'); ?></p>
                            <p><strong>Usage:</strong> <?php echo (int) $row['usage_count']; ?> / <?php echo (int) ($row['usage_limit'] ?: 0); ?></p>
                        </div>
                    </div>
                <?php }
            } else { ?>
                <div class="card"><div class="card-body"><p>No promotions available right now.</p></div></div>
            <?php } ?>
        </div>
    </div>
</section>
<?php include('includes/footer.php'); ?>
<script src="assets/js/main.js"></script>
</body>
</html>
