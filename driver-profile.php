<?php
require_once __DIR__ . '/includes/auth.php';
yamu_start_session();
yamu_require_assigned_user_role(['driver'], 'signin.php', ['active', 'pending', 'verified'], 'access-denied.php');
include 'includes/config.php';

$page_title = 'Driver Profile';
$userId = (int) ($_SESSION['user']['user_ID'] ?? 0);
$driverStatus = yamu_current_user_role_status('driver');
yamu_ensure_role_profile_row($conn, $userId, 'driver');
$profile = yamu_fetch_role_profile($conn, $userId, 'driver') ?? [];
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
                    <?php if (yamu_is_role_pending($driverStatus)) { ?>
                        <p>Your driver role is pending verification. You can complete onboarding here, but driver ads and other operational actions stay blocked until approval.</p>
                    <?php } ?>
                    <div class="form-group">
                        <label>Verification Status:</label>
                        <input type="text" value="<?php echo yamu_e(ucfirst($profile['verification_status'] ?? 'pending')); ?>" readonly />
                    </div>
                    <form action="includes/role-profile-setting.php" method="POST" class="signup-form">
                        <div class="form-group">
                            <label for="driving_license_number">Driving License Number:</label>
                            <input type="text" name="driving_license_number" id="driving_license_number" value="<?php echo yamu_e($profile['driving_license_number'] ?? ''); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="license_expiry_date">License Expiry Date:</label>
                            <input type="date" name="license_expiry_date" id="license_expiry_date" value="<?php echo yamu_e($profile['license_expiry_date'] ?? ''); ?>" />
                        </div>
                        <div class="form-group">
                            <label for="nic_id">NIC / ID:</label>
                            <input type="text" name="nic_id" id="nic_id" value="<?php echo yamu_e($profile['nic_id'] ?? ''); ?>" />
                        </div>
                        <div class="form-group">
                            <label for="service_area">Service Area / Location:</label>
                            <input type="text" name="service_area" id="service_area" value="<?php echo yamu_e($profile['service_area'] ?? ''); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="provider_details">Provider Details:</label>
                            <textarea name="provider_details" id="provider_details" rows="5" required><?php echo yamu_e($profile['provider_details'] ?? ''); ?></textarea>
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
