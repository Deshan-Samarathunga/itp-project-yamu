<?php
    require_once __DIR__ . '/includes/auth.php';
    yamu_start_session();
    $page_title = "Reset Password";
    $token = trim((string) ($_GET['token'] ?? ''));
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

    <section class="register">
        <?php
            include('includes/alert.php');
        ?>
        <div class="container">
            <div class="signup-content">
                <?php if ($token === '') { ?>
                    <div class="alert alert-error">
                        Invalid or missing reset token. Request a new link from <a href="forgot-password.php">Forgot Password</a>.
                    </div>
                <?php } else { ?>
                    <form action="includes/reset-password.php" method="POST" class="signup-form">
                        <h3>Reset Password</h3>
                        <p>Set a new password for your account.</p>
                        <input type="hidden" name="token" value="<?php echo yamu_e($token); ?>" />
                        <div class="form-group">
                            <label for="new_password">New Password:</label>
                            <input type="password" name="new_password" id="new_password" placeholder="Enter New Password" required/>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password:</label>
                            <input type="password" name="confirm_password" id="confirm_password" placeholder="Re-enter New Password" required/>
                        </div>
                        <div class="form-submit">
                            <input type="submit" value="Reset Password" class="btn main-btn" name="resetPassword" id="resetPassword" />
                        </div>
                    </form>
                <?php } ?>
                <p><a href="signin.php">Back to Sign In</a></p>
            </div>
        </div>
    </section>

    <?php
        include('includes/footer.php');
    ?>

    <script src="assets/js/main.js"></script>
</body>
</html>
