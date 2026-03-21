<?php
    require_once __DIR__ . '/includes/auth.php';
    carzo_start_session();
    carzo_require_any_assigned_user_role(['staff'], 'signin.php', 'access-denied.php');
    include 'includes/config.php';
    $page_title = "Staff Profile";
    $userId = (int) ($_SESSION['user']['user_ID'] ?? 0);
    carzo_ensure_role_profile_row($conn, $userId, 'staff');
    $profile = carzo_fetch_role_profile($conn, $userId, 'staff') ?? [];
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
                    $currentAccountPage = 'staff-profile';
                    include('includes/account-sidebar.php');
                ?>
                <div class="profile-details card">
                    <h3>Staff Profile</h3>
                    <div class="form-group">
                        <label>Store Verification Status:</label>
                        <input type="text" value="<?php echo carzo_e(ucfirst($profile['verification_status'] ?? 'pending')); ?>" readonly />
                    </div>
                    <form action="includes/role-profile-setting.php" method="POST" class="signup-form">
                        <div class="form-group">
                            <label for="store_name">Store Name:</label>
                            <input type="text" name="store_name" id="store_name" value="<?php echo carzo_e($profile['store_name'] ?? ''); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="store_owner">Store Owner / Contact Person:</label>
                            <input type="text" name="store_owner" id="store_owner" value="<?php echo carzo_e($profile['store_owner'] ?? ''); ?>" />
                        </div>
                        <div class="form-group">
                            <label for="business_registration_number">Business Registration Number:</label>
                            <input type="text" name="business_registration_number" id="business_registration_number" value="<?php echo carzo_e($profile['business_registration_number'] ?? ''); ?>" />
                        </div>
                        <div class="form-group">
                            <label for="store_address">Store Address:</label>
                            <textarea name="store_address" id="store_address"><?php echo carzo_e($profile['store_address'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="store_contact_number">Store Contact Number:</label>
                            <input type="text" name="store_contact_number" id="store_contact_number" value="<?php echo carzo_e($profile['store_contact_number'] ?? ''); ?>" />
                        </div>
                        <div class="form-group">
                            <label for="store_email">Store Email:</label>
                            <input type="email" name="store_email" id="store_email" value="<?php echo carzo_e($profile['store_email'] ?? ''); ?>" />
                        </div>
                        <input type="submit" value="Update Staff Profile" class="btn main-btn" name="updateStaffProfile" />
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php include('includes/footer.php'); ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
