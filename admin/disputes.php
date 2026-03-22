<?php
require_once __DIR__ . '/../includes/auth.php';
yamu_start_session();
yamu_require_admin('index.php');
$page_title = "Disputes";
include 'includes/config.php';

$statusFilter = strtolower(trim((string) ($_GET['status'] ?? '')));
$allowedStatuses = ['open', 'under_review', 'resolved', 'rejected'];
$sql = "SELECT c.*, b.booking_No, v.vehicle_title, u.full_name AS complainant_name
        FROM complaints c
        LEFT JOIN booking b ON b.booking_id = c.booking_id
        LEFT JOIN vehicles v ON v.vehicle_id = c.target_vehicle_id
        LEFT JOIN users u ON u.user_id = c.complainant_user_id
        WHERE 1 = 1";

if (in_array($statusFilter, $allowedStatuses, true)) {
    $sql .= " AND c.status = '" . yamu_escape($conn, $statusFilter) . "'";
}

$sql .= " ORDER BY c.created_at DESC, c.complaint_id DESC";
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
        <h2>Disputes</h2>
        <div class="main-cards">
            <div class="card">
                <div class="card-title">
                    <div class="search-box"><input type="text" id="myInput" onkeyup="seacrFunction()" placeholder="Search booking no..."></div>
                    <form method="GET">
                        <select name="status">
                            <option value="">All Statuses</option>
                            <?php foreach ($allowedStatuses as $allowedStatus) { ?>
                                <option value="<?php echo yamu_e($allowedStatus); ?>" <?php echo $statusFilter === $allowedStatus ? 'selected' : ''; ?>><?php echo yamu_e(ucfirst(str_replace('_', ' ', $allowedStatus))); ?></option>
                            <?php } ?>
                        </select>
                        <button type="submit" class="btn second-btn">Filter</button>
                        <a href="disputes.php" class="btn second-btn">Reset</a>
                    </form>
                </div>
                <div class="table-wrap">
                <table id="table">
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Vehicle</th>
                            <th>Customer</th>
                            <th>Subject</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        <?php if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?php echo yamu_e($row['booking_No']); ?></td>
                                    <td><?php echo yamu_e($row['vehicle_title']); ?></td>
                                    <td><?php echo yamu_e($row['complainant_name']); ?></td>
                                    <td><?php echo yamu_e($row['subject']); ?></td>
                                    <td><?php echo yamu_e($row['category']); ?></td>
                                    <td><span class="<?php echo yamu_e(yamu_badge_class($row['status'])); ?>"><?php echo yamu_e(ucfirst(str_replace('_', ' ', $row['status']))); ?></span></td>
                                    <td class="action-cell"><div class="table-actions"><a href="dispute-view.php?complaint_id=<?php echo (int) $row['complaint_id']; ?>" class="Status-active-badge">View</a></div></td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr><td colspan="7">No disputes found.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
                </div>
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
