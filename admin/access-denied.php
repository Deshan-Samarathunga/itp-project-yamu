<?php
    require_once __DIR__ . '/../includes/auth.php';
    yamu_start_session();
    $page_title = "Access Denied";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('includes/header.php'); ?>
</head>
<body>
    <section class="admin-signin">
        <?php include('../includes/alert.php'); ?>
        <div class="container">
            <div class="signup-content">
                <h3>Access Denied</h3>
                <p>You do not have permission to access this admin page.</p>
                <div class="form-submit">
                    <?php if (yamu_is_admin_authenticated()) { ?>
                        <a href="dashboard.php" class="btn main-btn">Back to Dashboard</a>
                    <?php } else { ?>
                        <a href="index.php" class="btn main-btn">Admin Sign In</a>
                    <?php } ?>
                    <a href="../index.php" class="btn second-btn">Back to Site</a>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
