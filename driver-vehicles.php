<?php
    require_once __DIR__ . '/includes/auth.php';
    carzo_start_session();
carzo_require_user_roles(['driver'], 'signin.php', ['active', 'pending', 'verified'], 'access-denied.php');
    include 'includes/config.php';
    $page_title = "Driver Vehicles";

    $driverId = (int) ($_SESSION['user']['user_ID'] ?? 0);
    $statusFilter = strtolower(trim((string) ($_GET['status'] ?? '')));
    $allowedStatuses = ['pending', 'approved', 'rejected', 'inactive'];
    $sql = "SELECT * FROM vehicles WHERE owner_user_id = {$driverId}";

    if (in_array($statusFilter, $allowedStatuses, true)) {
        $sql .= " AND listing_status = '" . carzo_escape($conn, $statusFilter) . "'";
    }

    $sql .= ' ORDER BY updated_at DESC, vehicle_id DESC';
    $result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('includes/header.php'); ?>
</head>
<body>
    <?php include('includes/menu.php'); ?>

    <section class="profile">
        <?php include('includes/alert.php'); ?>
        <div class="container">
            <div class="row">
                <?php
                    $currentAccountPage = 'driver-vehicles';
                    include('includes/account-sidebar.php');
                ?>
                <div class="profile-details card">
                    <h3>My Vehicle Listings</h3>
                    <div class="card-title" style="width: 100%; margin: 20px 0;">
                        <form action="" method="GET" style="width: 100%; margin: 0;">
                            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                                <select name="status" style="width: 180px;">
                                    <option value="">All Listing Statuses</option>
                                    <option value="pending" <?php echo ($statusFilter === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="approved" <?php echo ($statusFilter === 'approved') ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?php echo ($statusFilter === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                                    <option value="inactive" <?php echo ($statusFilter === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                                <button type="submit" class="btn second-btn">Filter</button>
                                <a href="driver-vehicles.php" class="btn second-btn">Reset</a>
                                <a href="driver-vehicle-add.php" class="btn main-btn">Add New +</a>
                            </div>
                        </form>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Listing Status</th>
                                <th>Availability</th>
                                <th>Maintenance</th>
                                <th>Price</th>
                                <th>Location</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            <?php if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) { ?>
                                    <tr>
                                        <td><?php echo carzo_e($row['vehicle_id']); ?></td>
                                        <td><img src="admin/assets/images/uploads/vehicles/<?php echo carzo_e($row['vImg1']); ?>" alt="vehicle" style="width: 80px; height: 80px; object-fit: cover;"></td>
                                        <td><?php echo carzo_e($row['vehicle_title']); ?></td>
                                        <td><span class="<?php echo carzo_e(carzo_badge_class($row['listing_status'])); ?>"><?php echo carzo_e(ucfirst($row['listing_status'])); ?></span></td>
                                        <td><span class="<?php echo carzo_e(carzo_badge_class($row['availability_status'])); ?>"><?php echo carzo_e(ucfirst($row['availability_status'])); ?></span></td>
                                        <td><span class="<?php echo carzo_e(carzo_badge_class($row['maintenance_status'])); ?>"><?php echo carzo_e(ucfirst($row['maintenance_status'])); ?></span></td>
                                        <td><?php echo carzo_e($row['price']); ?></td>
                                        <td><?php echo carzo_e($row['location']); ?></td>
                                        <td>
                                            <a href="driver-vehicle-edit.php?vehicle_id=<?php echo $row['vehicle_id']; ?>" class="edit-badge" title="Edit"><i class="ri-pencil-fill"></i></a>
                                            <a href="includes/driver-vehicle-process.php?deleteVehicle=<?php echo $row['vehicle_id']; ?>" class="del-badge" title="Delete"><i class="ri-delete-bin-7-fill"></i></a>
                                        </td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="9">No vehicle listings found.</td>
                                </tr>
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



require_once __DIR__ . '/includes/auth.php';
carzo_start_session();
carzo_require_user_roles(['driver'], 'signin.php', ['active', 'pending'], 'index.php');
carzo_redirect('driver-ads.php');
