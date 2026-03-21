<?php
require_once __DIR__ . '/../includes/auth.php';
carzo_start_session();
carzo_require_admin('index.php');
include 'includes/config.php';
$page_title = "Vehicles";

$listingFilter = strtolower(trim((string) ($_GET['listing_status'] ?? '')));
$availabilityFilter = strtolower(trim((string) ($_GET['availability_status'] ?? '')));
$maintenanceFilter = strtolower(trim((string) ($_GET['maintenance_status'] ?? '')));
$allowedListingStatuses = ['pending', 'approved', 'rejected', 'inactive'];
$allowedAvailabilityStatuses = ['available', 'booked', 'unavailable'];
$allowedMaintenanceStatuses = ['good', 'due soon', 'under maintenance', 'unavailable'];

$statsResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total_vehicles,
            SUM(listing_status = 'approved' AND availability_status = 'available') AS active_vehicles,
            SUM(listing_status = 'pending') AS pending_approval,
            SUM(availability_status = 'booked') AS booked_vehicles,
            SUM(maintenance_status IN ('under maintenance', 'unavailable')) AS maintenance_vehicles
     FROM vehicles"
);
$stats = $statsResult ? mysqli_fetch_assoc($statsResult) : [];

$sql = "SELECT v.*, u.full_name AS owner_name, u.email AS owner_email, u.role AS owner_role
        FROM vehicles v
        LEFT JOIN users u ON u.user_id = v.owner_user_id
        WHERE 1 = 1";

if (in_array($listingFilter, $allowedListingStatuses, true)) {
    $sql .= " AND v.listing_status = '" . carzo_escape($conn, $listingFilter) . "'";
}

if (in_array($availabilityFilter, $allowedAvailabilityStatuses, true)) {
    $sql .= " AND v.availability_status = '" . carzo_escape($conn, $availabilityFilter) . "'";
}

if (in_array($maintenanceFilter, $allowedMaintenanceStatuses, true)) {
    $sql .= " AND v.maintenance_status = '" . carzo_escape($conn, $maintenanceFilter) . "'";
}

$sql .= " ORDER BY COALESCE(v.updated_at, v.reg_date) DESC, v.vehicle_id DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('includes/header.php'); ?>
</head>
<body>

    <div class="grid-container">
        <?php include('includes/menu.php'); ?>
        <?php include('includes/aside.php'); ?>

        <main class="main">
            <?php include('../includes/alert.php'); ?>
            <h2>Manage Vehicles</h2>

            <div class="main-overview">
                <div class="overviewcard">
                    <div class="overviewcard-info">
                        <h3>Total Vehicles</h3>
                        <span><?php echo (int) ($stats['total_vehicles'] ?? 0); ?></span>
                    </div>
                    <div class="overviewcard-icon">
                        <i class="ri-car-line"></i>
                    </div>
                </div>
                <div class="overviewcard">
                    <div class="overviewcard-info">
                        <h3>Active Inventory</h3>
                        <span><?php echo (int) ($stats['active_vehicles'] ?? 0); ?></span>
                    </div>
                    <div class="overviewcard-icon">
                        <i class="ri-checkbox-circle-line"></i>
                    </div>
                </div>
                <div class="overviewcard">
                    <div class="overviewcard-info">
                        <h3>Pending Approval</h3>
                        <span><?php echo (int) ($stats['pending_approval'] ?? 0); ?></span>
                    </div>
                    <div class="overviewcard-icon">
                        <i class="ri-time-line"></i>
                    </div>
                </div>
                <div class="overviewcard">
                    <div class="overviewcard-info">
                        <h3>Booked Vehicles</h3>
                        <span><?php echo (int) ($stats['booked_vehicles'] ?? 0); ?></span>
                    </div>
                    <div class="overviewcard-icon">
                        <i class="ri-roadster-line"></i>
                    </div>
                </div>
                <div class="overviewcard">
                    <div class="overviewcard-info">
                        <h3>Maintenance</h3>
                        <span><?php echo (int) ($stats['maintenance_vehicles'] ?? 0); ?></span>
                    </div>
                    <div class="overviewcard-icon">
                        <i class="ri-tools-line"></i>
                    </div>
                </div>
            </div>

            <div class="main-cards">
                <div class="card">
                    <h3>Listed Vehicles</h3>
                    <div class="card-title">
                        <div class="search-box">
                            <input type="text" id="myInput" onkeyup="seacrFunction()" placeholder="Search vehicle...">
                        </div>
                        <form action="" method="GET">
                            <select name="listing_status" style="width: 170px;">
                                <option value="">All Listings</option>
                                <?php foreach ($allowedListingStatuses as $allowedListingStatus) { ?>
                                    <option value="<?php echo carzo_e($allowedListingStatus); ?>" <?php echo ($listingFilter === $allowedListingStatus) ? 'selected' : ''; ?>>
                                        <?php echo carzo_e(ucfirst($allowedListingStatus)); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <select name="availability_status" style="width: 170px;">
                                <option value="">All Availability</option>
                                <?php foreach ($allowedAvailabilityStatuses as $allowedAvailabilityStatus) { ?>
                                    <option value="<?php echo carzo_e($allowedAvailabilityStatus); ?>" <?php echo ($availabilityFilter === $allowedAvailabilityStatus) ? 'selected' : ''; ?>>
                                        <?php echo carzo_e(ucfirst($allowedAvailabilityStatus)); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <select name="maintenance_status" style="width: 190px;">
                                <option value="">All Maintenance</option>
                                <?php foreach ($allowedMaintenanceStatuses as $allowedMaintenanceStatus) { ?>
                                    <option value="<?php echo carzo_e($allowedMaintenanceStatus); ?>" <?php echo ($maintenanceFilter === $allowedMaintenanceStatus) ? 'selected' : ''; ?>>
                                        <?php echo carzo_e(ucfirst($allowedMaintenanceStatus)); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <button type="submit" class="btn second-btn">Filter</button>
                            <a href="vehicle.php" class="btn second-btn">Reset</a>
                            <a href="vehicle-add.php" class="btn main-btn">Add New +</a>
                        </form>
                    </div>
                    <div class="table-wrap">
                    <table id="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Vehicle</th>
                                <th>Owner</th>
                                <th>Price / Day</th>
                                <th>Availability</th>
                                <th>Listing</th>
                                <th>Maintenance</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            <?php if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) { ?>
                                    <tr>
                                        <td><?php echo (int) $row['vehicle_id']; ?></td>
                                        <td class="image-cell"><img src="assets/images/uploads/vehicles/<?php echo carzo_e($row['vImg1']); ?>" alt="vehicle" class="table-thumb table-thumb--lg"></td>
                                        <td>
                                            <?php echo carzo_e($row['vehicle_title']); ?><br>
                                            <small><?php echo carzo_e($row['vehicle_brand']); ?> | <?php echo carzo_e($row['registration_number']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo carzo_e($row['owner_name']); ?><br>
                                            <small><?php echo carzo_e(ucfirst($row['owner_role'])); ?> | <?php echo carzo_e($row['owner_email']); ?></small>
                                        </td>
                                        <td><?php echo carzo_e($row['price']); ?></td>
                                        <td><span class="<?php echo carzo_e(carzo_badge_class($row['availability_status'])); ?>"><?php echo carzo_e(ucfirst($row['availability_status'])); ?></span></td>
                                        <td><span class="<?php echo carzo_e(carzo_badge_class($row['listing_status'])); ?>"><?php echo carzo_e(ucfirst($row['listing_status'])); ?></span></td>
                                        <td><span class="<?php echo carzo_e(carzo_badge_class($row['maintenance_status'])); ?>"><?php echo carzo_e(ucfirst($row['maintenance_status'])); ?></span></td>
                                        <td class="action-cell">
                                            <div class="table-actions">
                                            <a href="vehicle-edit.php?vehicle_id=<?php echo (int) $row['vehicle_id']; ?>" class="edit-badge" title="Edit"><i class="ri-pencil-fill"></i></a>
                                            <a href="includes/vehicle-process.php?vehicle_id=<?php echo (int) $row['vehicle_id']; ?>" class="del-badge" title="Delete"><i class="ri-delete-bin-7-fill"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="9">No vehicles found.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </main>

        <footer class="footer">
            <div class="footer__copyright">&copy; 2023 EM</div>
            <div class="footer__signature">Made with love by pure genius</div>
        </footer>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        function seacrFunction() {
            var input = document.getElementById("myInput");
            var filter = input.value.toUpperCase();
            var table = document.getElementById("table");
            var tr = table.getElementsByTagName("tr");

            for (var i = 0; i < tr.length; i++) {
                var td = tr[i].getElementsByTagName("td")[2];

                if (td) {
                    var txtValue = td.textContent || td.innerText;
                    tr[i].style.display = txtValue.toUpperCase().indexOf(filter) > -1 ? "" : "none";
                }
            }
        }
    </script>
</body>
</html>
