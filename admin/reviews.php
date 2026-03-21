<?php
require_once __DIR__ . '/../includes/auth.php';
carzo_start_session();
carzo_require_admin('index.php');
$page_title = "Reviews";
include 'includes/config.php';

$statusFilter = strtolower(trim((string) ($_GET['status'] ?? '')));
$allowedStatuses = ['pending', 'visible', 'hidden', 'flagged'];
$sql = "SELECT r.*, b.booking_No, v.vehicle_title, c.full_name AS customer_name, d.full_name AS driver_name
        FROM reviews r
        LEFT JOIN booking b ON b.booking_id = r.booking_id
        LEFT JOIN vehicles v ON v.vehicle_id = r.vehicle_id
        LEFT JOIN users c ON c.user_id = r.customer_id
        LEFT JOIN users d ON d.user_id = r.driver_id
        WHERE 1 = 1";

if (in_array($statusFilter, $allowedStatuses, true)) {
    $sql .= " AND r.status = '" . carzo_escape($conn, $statusFilter) . "'";
}

$sql .= " ORDER BY r.created_at DESC, r.review_id DESC";
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
        <h2>Reviews</h2>
        <div class="main-cards">
            <div class="card">
                <div class="card-title" style="align-items: flex-end; gap: 10px; flex-wrap: wrap;">
                    <div class="search-box"><input type="text" id="myInput" onkeyup="seacrFunction()" placeholder="Search booking no..."></div>
                    <form method="GET" style="display:flex; gap:10px; align-items:center;">
                        <select name="status">
                            <option value="">All Statuses</option>
                            <?php foreach ($allowedStatuses as $allowedStatus) { ?>
                                <option value="<?php echo carzo_e($allowedStatus); ?>" <?php echo $statusFilter === $allowedStatus ? 'selected' : ''; ?>><?php echo carzo_e(ucfirst($allowedStatus)); ?></option>
                            <?php } ?>
                        </select>
                        <button type="submit" class="btn second-btn">Filter</button>
                        <a href="reviews.php" class="btn second-btn">Reset</a>
                    </form>
                </div>
                <table id="table">
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Vehicle</th>
                            <th>Customer</th>
                            <th>Driver</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        <?php if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?php echo carzo_e($row['booking_No']); ?></td>
                                    <td><?php echo carzo_e($row['vehicle_title']); ?></td>
                                    <td><?php echo carzo_e($row['customer_name']); ?></td>
                                    <td><?php echo carzo_e($row['driver_name']); ?></td>
                                    <td><?php echo str_repeat('★', (int) $row['rating']); ?></td>
                                    <td><?php echo carzo_e($row['comment']); ?></td>
                                    <td><span class="<?php echo carzo_e(carzo_badge_class($row['status'])); ?>"><?php echo carzo_e(ucfirst($row['status'])); ?></span></td>
                                    <td>
                                        <a href="includes/review-process.php?review_id=<?php echo (int) $row['review_id']; ?>&status=visible" class="edit-badge" title="Visible"><i class="ri-eye-line"></i></a>
                                        <a href="includes/review-process.php?review_id=<?php echo (int) $row['review_id']; ?>&status=flagged" class="edit-badge" title="Flag"><i class="ri-flag-line"></i></a>
                                        <a href="includes/review-process.php?review_id=<?php echo (int) $row['review_id']; ?>&status=hidden" class="del-badge" title="Hide"><i class="ri-eye-off-line"></i></a>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr><td colspan="8">No reviews found.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <footer class="footer"><div class="footer__copyright">&copy; 2023 EM</div><div class="footer__signature">Made with love by pure genius</div></footer>
</div>
<script src="assets/js/main.js"></script>
<script>
function seacrFunction() {
    var input = document.getElementById("myInput");
    var filter = input.value.toUpperCase();
    var table = document.getElementById("table");
    var tr = table.getElementsByTagName("tr");
    for (var i = 0; i < tr.length; i++) {
        var td = tr[i].getElementsByTagName("td")[0];
        if (td) {
            var txtValue = td.textContent || td.innerText;
            tr[i].style.display = txtValue.toUpperCase().indexOf(filter) > -1 ? "" : "none";
        }
    }
}
</script>
</body>
</html>
