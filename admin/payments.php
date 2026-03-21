<?php
require_once __DIR__ . '/../includes/auth.php';
carzo_start_session();
carzo_require_admin('index.php');
$page_title = "Payments";
include 'includes/config.php';

$statusFilter = strtolower(trim((string) ($_GET['status'] ?? '')));
$methodFilter = strtolower(trim((string) ($_GET['method'] ?? '')));
$allowedStatuses = ['pending', 'paid', 'failed', 'refunded'];
$allowedMethods = ['mock_card', 'bank_transfer', 'cash', 'wallet'];

$sql = "SELECT p.*, b.booking_No, v.vehicle_title, c.full_name AS customer_name, d.full_name AS driver_name
        FROM payments p
        LEFT JOIN booking b ON b.booking_id = p.booking_id
        LEFT JOIN vehicles v ON v.vehicle_id = b.vehicle_ID
        LEFT JOIN users c ON c.user_id = p.customer_id
        LEFT JOIN users d ON d.user_id = p.driver_id
        WHERE 1 = 1";

if (in_array($statusFilter, $allowedStatuses, true)) {
    $sql .= " AND p.payment_status = '" . carzo_escape($conn, $statusFilter) . "'";
}

if (in_array($methodFilter, $allowedMethods, true)) {
    $sql .= " AND p.payment_method = '" . carzo_escape($conn, $methodFilter) . "'";
}

$sql .= " ORDER BY p.created_at DESC, p.payment_id DESC";
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
        <h2>Payments</h2>
        <div class="main-cards">
            <div class="card">
                <div class="card-title">
                    <div class="search-box"><input type="text" id="myInput" onkeyup="seacrFunction()" placeholder="Search booking no..."></div>
                    <form method="GET">
                        <select name="status">
                            <option value="">All Statuses</option>
                            <?php foreach ($allowedStatuses as $allowedStatus) { ?>
                                <option value="<?php echo carzo_e($allowedStatus); ?>" <?php echo $statusFilter === $allowedStatus ? 'selected' : ''; ?>><?php echo carzo_e(ucfirst($allowedStatus)); ?></option>
                            <?php } ?>
                        </select>
                        <select name="method">
                            <option value="">All Methods</option>
                            <?php foreach ($allowedMethods as $allowedMethod) { ?>
                                <option value="<?php echo carzo_e($allowedMethod); ?>" <?php echo $methodFilter === $allowedMethod ? 'selected' : ''; ?>><?php echo carzo_e(ucfirst(str_replace('_', ' ', $allowedMethod))); ?></option>
                            <?php } ?>
                        </select>
                        <button type="submit" class="btn second-btn">Filter</button>
                        <a href="payments.php" class="btn second-btn">Reset</a>
                    </form>
                </div>
                <div class="table-wrap">
                <table id="table">
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Vehicle</th>
                            <th>Customer</th>
                            <th>Driver</th>
                            <th>Method</th>
                            <th>Final Amount</th>
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
                                    <td><?php echo carzo_e(ucfirst(str_replace('_', ' ', $row['payment_method']))); ?></td>
                                    <td><?php echo carzo_money($row['final_amount']); ?></td>
                                    <td><span class="<?php echo carzo_e(carzo_badge_class($row['payment_status'])); ?>"><?php echo carzo_e(ucfirst($row['payment_status'])); ?></span></td>
                                    <td class="action-cell">
                                        <div class="table-actions">
                                        <a href="includes/payment-process.php?payment_id=<?php echo (int) $row['payment_id']; ?>&status=paid" class="edit-badge" title="Paid"><i class="ri-check-fill"></i></a>
                                        <a href="includes/payment-process.php?payment_id=<?php echo (int) $row['payment_id']; ?>&status=failed" class="del-badge" title="Failed"><i class="ri-close-fill"></i></a>
                                        <a href="includes/payment-process.php?payment_id=<?php echo (int) $row['payment_id']; ?>&status=refunded" class="edit-badge" title="Refunded"><i class="ri-reply-line"></i></a>
                                        <a href="../invoice.php?payment_id=<?php echo (int) $row['payment_id']; ?>" class="Status-active-badge">Invoice</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr><td colspan="8">No payments found.</td></tr>
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
