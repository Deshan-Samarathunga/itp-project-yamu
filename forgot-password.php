<?php
    require_once __DIR__ . '/includes/auth.php';
    carzo_start_session();
    $page_title = "Forgot Password";
    $previewLink = $_SESSION['password_reset_preview'] ?? '';
    unset($_SESSION['password_reset_preview']);
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
                <form action="includes/forgot-password.php" method="POST" class="signup-form">
                    <h3>Forgot Password</h3>
                    <p>Enter your email to reset your password.</p>
                    <div class="form-group">
                        <label for="email">Email Address:</label>
                        <input type="email" name="email" id="email" placeholder="Enter Email Address" required/>
                    </div>
                    <div class="form-submit">
                        <input type="submit" value="Send Reset Link" class="btn main-btn" name="forgotPassword" id="forgotPassword" />
                    </div>
                </form>
                <?php if ($previewLink !== '') { ?>
                    <div class="alert alert-success" style="margin-top: 16px;">
                        Development reset link:
                        <a href="<?php echo carzo_e($previewLink); ?>"><?php echo carzo_e($previewLink); ?></a>
                    </div>
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
