<?php
require_once __DIR__ . '/../includes/auth.php';
carzo_start_session();
carzo_require_admin('index.php');
$page_title = "Promotions";
include 'includes/config.php';

$sql = "SELECT p.*, v.vehicle_title
        FROM promotions p
        LEFT JOIN vehicles v ON v.vehicle_id = p.applicable_vehicle_id
        ORDER BY p.created_at DESC, p.promotion_id DESC";
$result = mysqli_query($conn, $sql);
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
        <h2>Promotions</h2>
        <div class="main-cards">
            <div class="card">
                <div class="card-title">
                    <div></div>
                    <a href="promotion-add.php" class="btn main-btn">Add Promotion +</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Title</th>
                            <th>Discount</th>
                            <th>Valid To</th>
                            <th>Usage</th>
                            <th>Vehicle</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        <?php if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?php echo carzo_e($row['code']); ?></td>
                                    <td><?php echo carzo_e($row['title']); ?></td>
                                    <td><?php echo carzo_e($row['discount_type'] === 'percentage' ? carzo_money($row['discount_value']) . '%' : 'Rs. ' . carzo_money($row['discount_value'])); ?></td>
                                    <td><?php echo carzo_e($row['valid_to']); ?></td>
                                    <td><?php echo (int) $row['usage_count']; ?> / <?php echo (int) ($row['usage_limit'] ?: 0); ?></td>
                                    <td><?php echo carzo_e($row['vehicle_title'] ?: 'All vehicles'); ?></td>
                                    <td><span class="<?php echo carzo_e(carzo_badge_class($row['status'])); ?>"><?php echo carzo_e(ucfirst($row['status'])); ?></span></td>
                                    <td>
                                        <a href="promotion-edit.php?promotion_id=<?php echo (int) $row['promotion_id']; ?>" class="edit-badge" title="Edit"><i class="ri-pencil-fill"></i></a>
                                        <a href="includes/promotion-process.php?deletePromotion=<?php echo (int) $row['promotion_id']; ?>" class="del-badge" title="Delete"><i class="ri-delete-bin-7-fill"></i></a>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr><td colspan="8">No promotions created yet.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <footer class="footer"><div class="footer__copyright">&copy; 2023 EM</div><div class="footer__signature">Made with love by pure genius</div></footer>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>
