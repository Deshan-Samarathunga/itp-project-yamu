<?php
require_once __DIR__ . '/../includes/auth.php';
yamu_start_session();
yamu_require_admin('index.php');
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
    $sql .= " AND v.listing_status = '" . yamu_escape($conn, $listingFilter) . "'";
}

if (in_array($availabilityFilter, $allowedAvailabilityStatuses, true)) {
    $sql .= " AND v.availability_status = '" . yamu_escape($conn, $availabilityFilter) . "'";
}

if (in_array($maintenanceFilter, $allowedMaintenanceStatuses, true)) {
    $sql .= " AND v.maintenance_status = '" . yamu_escape($conn, $maintenanceFilter) . "'";
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
            <p>Vehicle listings belong to staff rental accounts. Admins review, edit, and moderate them here.</p>

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
                            <input type="text" id="vehicleSearchInput" placeholder="Search vehicle..." value="<?php echo yamu_e((string) ($_GET['search'] ?? '')); ?>">
                        </div>
                        <form action="vehicle.php" method="GET" id="vehicleFilterForm">
                            <input type="hidden" name="search" id="vehicleSearchField" value="<?php echo yamu_e((string) ($_GET['search'] ?? '')); ?>">
                            <select name="listing_status" id="listingStatusFilter" style="width: 170px;">
                                <option value="">All Listings</option>
                                <?php foreach ($allowedListingStatuses as $allowedListingStatus) { ?>
                                    <option value="<?php echo yamu_e($allowedListingStatus); ?>" <?php echo ($listingFilter === $allowedListingStatus) ? 'selected' : ''; ?>>
                                        <?php echo yamu_e(ucfirst($allowedListingStatus)); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <select name="availability_status" id="availabilityStatusFilter" style="width: 170px;">
                                <option value="">All Availability</option>
                                <?php foreach ($allowedAvailabilityStatuses as $allowedAvailabilityStatus) { ?>
                                    <option value="<?php echo yamu_e($allowedAvailabilityStatus); ?>" <?php echo ($availabilityFilter === $allowedAvailabilityStatus) ? 'selected' : ''; ?>>
                                        <?php echo yamu_e(ucfirst($allowedAvailabilityStatus)); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <select name="maintenance_status" id="maintenanceStatusFilter" style="width: 190px;">
                                <option value="">All Maintenance</option>
                                <?php foreach ($allowedMaintenanceStatuses as $allowedMaintenanceStatus) { ?>
                                    <option value="<?php echo yamu_e($allowedMaintenanceStatus); ?>" <?php echo ($maintenanceFilter === $allowedMaintenanceStatus) ? 'selected' : ''; ?>>
                                        <?php echo yamu_e(ucfirst($allowedMaintenanceStatus)); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <button type="submit" class="btn second-btn">Filter</button>
                            <a href="vehicle.php" class="btn second-btn">Reset</a>
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
                        <tbody class="table-body" id="vehicleTableBody">
                            <?php if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) { ?>
                                    <tr
                                        data-vehicle-title="<?php echo yamu_e(strtolower((string) $row['vehicle_title'])); ?>"
                                        data-registration-number="<?php echo yamu_e(strtolower((string) $row['registration_number'])); ?>"
                                        data-vehicle-brand="<?php echo yamu_e(strtolower((string) $row['vehicle_brand'])); ?>"
                                        data-owner-name="<?php echo yamu_e(strtolower((string) $row['owner_name'])); ?>"
                                        data-listing-status="<?php echo yamu_e(strtolower((string) $row['listing_status'])); ?>"
                                        data-availability-status="<?php echo yamu_e(strtolower((string) $row['availability_status'])); ?>"
                                        data-maintenance-status="<?php echo yamu_e(strtolower((string) $row['maintenance_status'])); ?>"
                                    >
                                        <td><?php echo (int) $row['vehicle_id']; ?></td>
                                        <td class="image-cell"><img src="assets/images/uploads/vehicles/<?php echo yamu_e($row['vImg1']); ?>" alt="vehicle" class="table-thumb table-thumb--lg"></td>
                                        <td>
                                            <?php echo yamu_e($row['vehicle_title']); ?><br>
                                            <small><?php echo yamu_e($row['vehicle_brand']); ?> | <?php echo yamu_e($row['registration_number']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo yamu_e($row['owner_name']); ?><br>
                                            <small><?php echo yamu_e(ucfirst($row['owner_role'])); ?> | <?php echo yamu_e($row['owner_email']); ?></small>
                                        </td>
                                        <td><?php echo yamu_e($row['price']); ?></td>
                                        <td><span class="<?php echo yamu_e(yamu_badge_class($row['availability_status'])); ?>"><?php echo yamu_e(ucfirst($row['availability_status'])); ?></span></td>
                                        <td><span class="<?php echo yamu_e(yamu_badge_class($row['listing_status'])); ?>"><?php echo yamu_e(ucfirst($row['listing_status'])); ?></span></td>
                                        <td><span class="<?php echo yamu_e(yamu_badge_class($row['maintenance_status'])); ?>"><?php echo yamu_e(ucfirst($row['maintenance_status'])); ?></span></td>
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
                            <tr id="vehicleFilterEmptyRow" style="display: none;">
                                <td colspan="9">No vehicles match the current filters.</td>
                            </tr>
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
        (function () {
            var searchInput = document.getElementById("vehicleSearchInput");
            var searchField = document.getElementById("vehicleSearchField");
            var listingFilter = document.getElementById("listingStatusFilter");
            var availabilityFilter = document.getElementById("availabilityStatusFilter");
            var maintenanceFilter = document.getElementById("maintenanceStatusFilter");
            var tableBody = document.getElementById("vehicleTableBody");
            var emptyRow = document.getElementById("vehicleFilterEmptyRow");

            if (!tableBody) {
                return;
            }

            var rows = tableBody.querySelectorAll("tr[data-listing-status]");

            function applyVehicleFilters() {
                var searchValue = (searchInput ? searchInput.value : "").toLowerCase().trim();
                var listingValue = (listingFilter ? listingFilter.value : "").toLowerCase().trim();
                var availabilityValue = (availabilityFilter ? availabilityFilter.value : "").toLowerCase().trim();
                var maintenanceValue = (maintenanceFilter ? maintenanceFilter.value : "").toLowerCase().trim();
                var visibleCount = 0;

                if (searchField) {
                    searchField.value = searchValue;
                }

                rows.forEach(function (row) {
                    var haystack = [
                        row.dataset.vehicleTitle || "",
                        row.dataset.registrationNumber || "",
                        row.dataset.vehicleBrand || "",
                        row.dataset.ownerName || ""
                    ].join(" ");

                    var matchesSearch = !searchValue || haystack.indexOf(searchValue) !== -1;
                    var matchesListing = !listingValue || row.dataset.listingStatus === listingValue;
                    var matchesAvailability = !availabilityValue || row.dataset.availabilityStatus === availabilityValue;
                    var matchesMaintenance = !maintenanceValue || row.dataset.maintenanceStatus === maintenanceValue;
                    var matches = matchesSearch && matchesListing && matchesAvailability && matchesMaintenance;

                    row.style.display = matches ? "" : "none";

                    if (matches) {
                        visibleCount++;
                    }
                });

                if (emptyRow) {
                    emptyRow.style.display = visibleCount === 0 ? "" : "none";
                }
            }

            if (searchInput) {
                searchInput.addEventListener("input", applyVehicleFilters);
            }

            [listingFilter, availabilityFilter, maintenanceFilter].forEach(function (filter) {
                if (filter) {
                    filter.addEventListener("change", applyVehicleFilters);
                }
            });

            applyVehicleFilters();
        }());
    </script>
</body>
</html>
