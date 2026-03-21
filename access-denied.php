<?php
    require_once __DIR__ . '/includes/auth.php';
    carzo_start_session();
    $page_title = "Access Denied";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('includes/header.php'); ?>
</head>
<body>
    <?php include('includes/menu.php'); ?>

    <section class="register">
        <?php include('includes/alert.php'); ?>
        <div class="container">
            <div class="signup-content">
                <h3>Access Denied</h3>
                <p>You do not have permission to access this page with your current role.</p>
                <div class="form-submit">
                    <?php if (carzo_is_user_authenticated()) { ?>
                        <a href="role-switch.php" class="btn main-btn">Switch Role</a>
                    <?php } ?>
                    <a href="<?php echo carzo_e(carzo_current_public_home_path()); ?>" class="btn second-btn">Go Back</a>
                </div>
            </div>
        </div>
    </section>

    <?php include('includes/footer.php'); ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
