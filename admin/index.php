<?php
    require_once __DIR__ . '/../includes/auth.php';
    carzo_start_session();

    if (carzo_is_admin_authenticated()) {
        carzo_redirect('dashboard.php');
    }

    $page_title = "Admin | Signin";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        include('includes/header.php');
    ?>
</head>
<body>
    
        <section class="admin-signin">
            <!-- Allert Box -->
            <?php
                include('../includes/alert.php');
            ?>
            <div class="container">
                <div class="signup-content">
                    <form action="<?php echo $adminBasePath; ?>/includes/signin-process.php" method="POST" class="signup-form">
                        <h3>Admin | Sign in</h3>
                        <br><br>
                        <div class="form-group">
                            <label for="email">Email Address:</label>
                            <input type="email" name="email" id="email" placeholder="Enter Email Address" required/>
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" name="password" id="password" placeholder="Enter Password" required/>
                        </div>
                        <div class="form-submit">
                            <input type="submit" value="Sign in" class="btn main-btn" name="signin" id="signin" />
                        </div>
                        <br><br>
                        <a href="<?php echo $siteBasePath; ?>/index.php">Back to Home</a>
                    </form>
                </div>
            </div>
        </section>    

</body>
</html>
