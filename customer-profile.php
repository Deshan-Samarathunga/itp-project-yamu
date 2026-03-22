<?php
require_once __DIR__ . '/includes/auth.php';
yamu_start_session();
yamu_require_assigned_user_role(['customer'], 'signin.php', ['active', 'pending', 'verified'], 'access-denied.php');
include 'includes/config.php';

$page_title = 'Customer Profile';
$userId = (int) ($_SESSION['user']['user_ID'] ?? 0);
yamu_ensure_role_profile_row($conn, $userId, 'customer');
$profile = yamu_fetch_role_profile($conn, $userId, 'customer') ?? [];
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
                    $currentAccountPage = 'customer-profile';
                    include('includes/account-sidebar.php');
                ?>
                <div class="profile-details card">
                    <h3>Customer Profile</h3>
                    <p>Customer-specific preferences stay separate from your common account profile.</p>
                    <form action="includes/role-profile-setting.php" method="POST" class="signup-form">
                        <div class="form-group">
                            <label for="preferences">Customer Preferences:</label>
                            <textarea name="preferences" id="preferences" placeholder="Preferred vehicle type, pickup preferences, notes"><?php echo yamu_e($profile['preferences'] ?? ''); ?></textarea>
                        </div>
                        <input type="submit" value="Update Customer Profile" class="btn main-btn" name="updateCustomerProfile" />
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php include('includes/footer.php'); ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
