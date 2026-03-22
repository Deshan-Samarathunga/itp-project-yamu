<?php
$page_title = 'Car Listing';
require_once __DIR__ . '/includes/auth.php';
yamu_start_session();
include 'includes/config.php';

$hasUserRolesTable = yamu_table_exists($conn, 'user_roles');
$staffRoleJoin = $hasUserRolesTable
    ? "INNER JOIN user_roles ur_staff
         ON ur_staff.user_id = v.owner_user_id
        AND ur_staff.role_key = 'staff'
        AND ur_staff.role_status IN ('active', 'verified')
        AND ur_staff.verification_status IN ('approved', 'verified')"
    : '';
$staffVisibilityWhere = $hasUserRolesTable ? '' : "AND owner.role = 'staff' AND owner.account_status = 'active'";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        include('includes/header.php');
    ?>
</head>
<body>

    <?php
        include('includes/menu.php');
    ?>

    <!-- Page Banner Section -->
    <section class="banner-page">
        <h2>Car Listing</h2>
                <div class="banner-link">
            <a href="index.php">Home</a> &gt; <a href="car-listing.php">Car Listing</a>
        </div>
    </section>

    <!-- Cars List Section -->
    <section class="cars">
        <div class="container">
            <div class="grid-3">


            <?php 
                $sql = "SELECT v.*
                        FROM vehicles v
                        LEFT JOIN users owner ON owner.user_id = v.owner_user_id
                        {$staffRoleJoin}
                        WHERE v.listing_status = 'approved'
                          {$staffVisibilityWhere}
                        ORDER BY COALESCE(v.updated_at, v.reg_date) DESC, v.vehicle_id DESC";
                $result = mysqli_query($conn, $sql);
      
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {  ?>
                        <div class="card">
                            <div class="card-img">
                                <div class="card-tag">
                                    <span><?php echo $row['vehicle_brand']; ?></span>
                                </div>
                                <img src="admin/assets/images/uploads/vehicles/<?php echo $row['vImg1']; ?>" alt="car">
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <h4 class="card-title"><?php echo $row['vehicle_title']; ?></h4>
                                    <h5 class="card-title"><?php echo $row['year']; ?></h5>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <p><i class="ri-group-line"></i><?php echo $row['capacity']; ?></p>
                                    </div>
                                    <div class="col">
                                        <p><i class="ri-gas-station-line"></i> <?php echo $row['fuel_type']; ?></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <p><i class="ri-car-line"></i><?php echo $row['year']; ?></p>
                                    </div>
                                    <div class="col">
                                        <p><i class="ri-steering-2-fill"></i><?php echo $row['transmission']; ?></p>
                                    </div>
                                </div>
                                <hr />
                                <div class="row">
                                    <h3>Rs.<?php echo $row['price']; ?> <span>/ Day</span></h3>
                                    <a href="vehical-details.php?vehicle_id=<?php echo $row['vehicle_id']; ?>" class="btn main-btn">View More</a>
                                </div>
                            </div>
                        </div>
                    <?php }} ?>

            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <?php
        include('includes/footer.php');
    ?>

    <script src="assets/js/main.js"></script>
</body>
</html>
