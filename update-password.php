<?php
    require_once __DIR__ . '/includes/auth.php';
    carzo_start_session();
    carzo_require_user_roles(['customer', 'driver'], 'signin.php', ['active', 'pending'], 'index.php');
    $page_title = "Update Password"; 
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
    
    <!-- Accout Dashboard -->
    <section class="profile">
        <?php
           include('includes/alert.php');
        ?>
        <div class="container">
            <div class="row">
                <?php
                    $currentAccountPage = 'password';
                    include('includes/account-sidebar.php');
                ?>
                <div class="profile-details card">
                    <h3>Change password</h3>
                    <form action="includes/update-password.php" method="POST" class="signup-form">
                        <input 
                            type="hidden" 
                            name="userID" 
                            id="userID" 
                            placeholder="Enter Name" 
                            value = "<?php echo $_SESSION['user']['user_ID'] ?>"
                            required
                        />
                        <div class="form-group">
                            <label for="current_password">Current Password:</label>
                            <input type="password" name="current_password" id="current_password" placeholder="Enter Current Password" required/>
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password:</label>
                            <input type="password" name="new_password" id="new_password" placeholder="Enter New Password" required/>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Re-enter Password:</label>
                            <input type="password" name="confirm_password" id="confirm_password"placeholder="Re-enter Password" required/>
                        </div>
                        <input type="submit" value="Update" class="btn main-btn" name="UpdatePassword" id="submit" />
                    </form>
                </div>
            </div>
        </div>
    </section>


    <!-- Footer -->
    <?php
        include('includes/footer.php');
    ?>

    <script src="assets/js/main.js"></script>
</body>
</html>
