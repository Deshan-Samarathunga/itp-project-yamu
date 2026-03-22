<?php
require_once __DIR__ . '/includes/auth.php';
yamu_start_session();
yamu_require_assigned_user_role(['staff'], 'signin.php', ['active', 'pending', 'verified'], 'access-denied.php');
include 'includes/config.php';

$page_title = 'Staff Profile';
$userId = (int) ($_SESSION['user']['user_ID'] ?? 0);
$staffStatus = yamu_current_user_role_status('staff');
yamu_ensure_role_profile_row($conn, $userId, 'staff');
$profile = yamu_fetch_role_profile($conn, $userId, 'staff') ?? [];
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
                    <?php if (yamu_is_role_pending($staffStatus)) { ?>
                        <p>Your staff role is pending verification. You can complete store onboarding here, but store and vehicle operations stay blocked until approval.</p>
                    <?php } ?>
                    <div class="form-group">
                        <label>Store Verification Status:</label>
                        <input type="text" value="<?php echo yamu_e(ucfirst($profile['verification_status'] ?? 'pending')); ?>" readonly />
                    </div>
                    <form action="includes/role-profile-setting.php" method="POST" class="signup-form">
                        <div class="form-group">
                            <label for="store_name">Store Name:</label>
                            <input type="text" name="store_name" id="store_name" value="<?php echo yamu_e($profile['store_name'] ?? ''); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="store_owner">Store Owner / Contact Person:</label>
                            <input type="text" name="store_owner" id="store_owner" value="<?php echo yamu_e($profile['store_owner'] ?? ''); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="business_registration_number">Business Registration Number:</label>
                            <input type="text" name="business_registration_number" id="business_registration_number" value="<?php echo yamu_e($profile['business_registration_number'] ?? ''); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="store_address">Store Address:</label>
                            <textarea name="store_address" id="store_address" required><?php echo yamu_e($profile['store_address'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="store_contact_number">Store Contact Number:</label>
                            <input type="text" name="store_contact_number" id="store_contact_number" value="<?php echo yamu_e($profile['store_contact_number'] ?? ''); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="store_email">Store Email:</label>
                            <input type="email" name="store_email" id="store_email" value="<?php echo yamu_e($profile['store_email'] ?? ''); ?>" required />
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
