<?php
require_once __DIR__ . '/includes/auth.php';
carzo_start_session();
carzo_require_user_roles(['customer'], 'signin.php', ['active'], 'index.php');
$page_title = "My Reviews";
include 'includes/config.php';

$customerId = (int) ($_SESSION['user']['user_ID'] ?? 0);
$sql = "SELECT r.*, b.booking_No, v.vehicle_title
        FROM reviews r
        LEFT JOIN booking b ON b.booking_id = r.booking_id
        LEFT JOIN vehicles v ON v.vehicle_id = r.vehicle_id
        WHERE r.customer_id = {$customerId}
        ORDER BY r.created_at DESC, r.review_id DESC";
$result = mysqli_query($conn, $sql);
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
            <?php $currentAccountPage = 'reviews'; include('includes/account-sidebar.php'); ?>
            <div class="profile-details card">
                <h3>My Reviews</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Booking No.</th>
                            <th>Vehicle</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        <?php if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?php echo carzo_e($row['booking_No']); ?></td>
                                    <td><?php echo carzo_e($row['vehicle_title']); ?></td>
                                    <td><?php echo str_repeat('★', (int) $row['rating']); ?></td>
                                    <td><?php echo carzo_e($row['comment']); ?></td>
                                    <td><span class="<?php echo carzo_e(carzo_badge_class($row['status'])); ?>"><?php echo carzo_e(ucfirst($row['status'])); ?></span></td>
                                    <td><?php echo carzo_e($row['created_at']); ?></td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr><td colspan="6">No reviews submitted yet.</td></tr>
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
