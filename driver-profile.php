<?php
    require_once __DIR__ . '/includes/auth.php';
    carzo_start_session();
    carzo_require_any_assigned_user_role(['driver'], 'signin.php', 'access-denied.php');
    include 'includes/config.php';
    $page_title = "Driver Profile";
    $userId = (int) ($_SESSION['user']['user_ID'] ?? 0);
    carzo_ensure_role_profile_row($conn, $userId, 'driver');
    $profile = carzo_fetch_role_profile($conn, $userId, 'driver') ?? [];
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
                    $currentAccountPage = 'driver-profile';
                    include('includes/account-sidebar.php');
                ?>
                <div class="profile-details card">
                    <h3>Driver Profile</h3>
                    <div class="form-group">
                        <label>Verification Status:</label>
                        <input type="text" value="<?php echo carzo_e(ucfirst($profile['verification_status'] ?? 'pending')); ?>" readonly />
                    </div>
                    <form action="includes/role-profile-setting.php" method="POST" class="signup-form">
                        <div class="form-group">
                            <label for="driving_license_number">Driving License Number:</label>
                            <input type="text" name="driving_license_number" id="driving_license_number" value="<?php echo carzo_e($profile['driving_license_number'] ?? ''); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="license_expiry_date">License Expiry Date:</label>
                            <input type="date" name="license_expiry_date" id="license_expiry_date" value="<?php echo carzo_e($profile['license_expiry_date'] ?? ''); ?>" />
                        </div>
                        <div class="form-group">
                            <label for="nic_id">NIC / ID:</label>
                            <input type="text" name="nic_id" id="nic_id" value="<?php echo carzo_e($profile['nic_id'] ?? ''); ?>" />
                        </div>
                        <div class="form-group">
                            <label for="service_area">Service Area / Location:</label>
                            <input type="text" name="service_area" id="service_area" value="<?php echo carzo_e($profile['service_area'] ?? ''); ?>" />
                        </div>
                        <input type="submit" value="Update Driver Profile" class="btn main-btn" name="updateDriverProfile" />
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php include('includes/footer.php'); ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
