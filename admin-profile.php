<?php
    require_once __DIR__ . '/includes/auth.php';
    carzo_start_session();
    carzo_require_any_assigned_user_role(['admin'], 'signin.php', 'access-denied.php');
    include 'includes/config.php';
    $page_title = "Admin Profile";
    $userId = (int) ($_SESSION['user']['user_ID'] ?? 0);
    carzo_ensure_role_profile_row($conn, $userId, 'admin');
    $profile = carzo_fetch_role_profile($conn, $userId, 'admin') ?? [];
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
                    $currentAccountPage = 'admin-profile';
                    include('includes/account-sidebar.php');
                ?>
                <div class="profile-details card">
                    <h3>Admin Profile</h3>
                    <form action="includes/role-profile-setting.php" method="POST" class="signup-form">
                        <div class="form-group">
                            <label for="system_permissions">System Permissions:</label>
                            <textarea name="system_permissions" id="system_permissions" placeholder="e.g. all or comma-separated permissions"><?php echo carzo_e($profile['system_permissions'] ?? 'all'); ?></textarea>
                        </div>
                        <input type="submit" value="Update Admin Profile" class="btn main-btn" name="updateAdminProfile" />
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php include('includes/footer.php'); ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
