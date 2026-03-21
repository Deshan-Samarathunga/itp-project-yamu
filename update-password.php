<?php
    $page_title = "Update Password"; 
    session_start(); // Start the session
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
            <div class="profile-card card sticky">
                    <div class="avatar">
                        <img src="assets/images/uploads/avatar/<?php echo $_SESSION['user']['avatar'] ?>" alt="avatar">
                    </div>
                    <h4>Monica Lucas</h4>
                    <span><?php echo $_SESSION['user']['email'] ?></span>
                    <ul class="sidenav-list">
                        <li class="sidenav-item">
                            <a href="profile.php" class="nav-links">
                                <i class="ri-user-line"></i>
                                Profile Setting
                            </a>
                        </li>
                        <li class="sidenav-item">
                            <a href="update-password.php" class="nav-links active">
                                <i class="ri-lock-line"></i>
                                Change Password
                            </a>
                        </li>
                        <li class="sidenav-item">
                            <a href="my-booking.php" class="nav-links">
                                <i class="ri-book-line"></i>
                                My Booking
                            </a>
                        </li>
                        <li class="sidenav-item">
                            <a href="profile.php" class="nav-links">
                                <i class="ri-logout-box-line"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
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