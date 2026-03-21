<?php
    $page_title = "Profile Setting"; 
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

    <div class="grid-container">
        <!-- Navbar -->
        <?php
            include('includes/menu.php');
        ?>

        <!-- Aside Section -->
        <?php
            include('includes/aside.php');
        ?>

        <main class="main">
            <div class="main-cards">
                <div class="profile-details card">
                    <form action="includes/profile-setting.php" method="POST" enctype="multipart/form-data" class="signup-form">
                        <h3>Main Information</h3>
                        <div class="form-group">
                            <!-- <label for="txtName">ID:</label> -->
                            <input 
                                type="hidden" 
                                name="userID" 
                                id="userID" 
                                placeholder="Enter Name" 
                                value = "<?php echo $_SESSION['admin']['admin_id'] ?>"
                                disabled
                            />
                        </div>
                        <div class="form-group">
                            <label for="fullName">Full Name:</label>
                            <input 
                                type="text" 
                                name="fullName" 
                                id="fullName" 
                                placeholder="Enter Name" 
                                value = "<?php echo $_SESSION['admin']['name'] ?>"
                                disabled
                            />
                        </div>
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input 
                                type="text" 
                                name="username" 
                                id="username" 
                                placeholder="Enter Username" 
                                value = "<?php echo $_SESSION['admin']['username'] ?>"
                                disabled
                            />
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address:</label>
                            <input 
                                type="email" 
                                name="email" 
                                id="email" 
                                placeholder="Enter Email Address" 
                                value = "<?php echo $_SESSION['admin']['email'] ?>"
                                disabled
                            />
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number:</label>
                            <input 
                                type="tel" 
                                name="phone" 
                                id="phone" 
                                value = "<?php echo $_SESSION['admin']['phone'] ?>"
                                placeholder="Enter Phone Number" 
                                disabled
                            />
                        </div>
                        <div class="form-group">
                            <label for="address">Address:</label>
                            <textarea name="address" id="address" disabled><?php echo $_SESSION['admin']['address'] ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="city">City:</label>
                            <input 
                                type="text" 
                                name="city" 
                                id="city" 
                                value = "<?php echo $_SESSION['admin']['city'] ?>"
                                placeholder="Enter Your City" 
                                disabled
                            />
                        </div>
                    </form>
                </div>
            </div>
        </main>

        <footer class="footer">
            <div class="footer__copyright">&copy; 2023 EM</div>
            <div class="footer__signature">Made with love by pure genius</div>
        </footer>
    </div>

    <script src="assets/js/main.js"></script>
    
</body>
</html>