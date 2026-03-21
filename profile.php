<?php
    $page_title = "Profile"; 
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
                    <h4><?php echo $_SESSION['user']['name'] ?></h4>
                    <span><?php echo $_SESSION['user']['email'] ?></span>
                    <ul class="sidenav-list">
                        <li class="sidenav-item">
                            <a href="profile.php" class="nav-links active">
                                <i class="ri-user-line"></i>
                                Profile Setting
                            </a>
                        </li>
                        <li class="sidenav-item">
                            <a href="update-password.php" class="nav-links">
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
                    <form action="includes/profile-setting.php" method="POST" enctype="multipart/form-data" class="signup-form">
                        <h3>Update Profile Picture</h3>
                        <div class="row">
                            <div class="avatar">
                                <img src="assets/images/uploads/avatar/<?php echo $_SESSION['user']['avatar'] ?>" alt="avatar" id="profilePic">
                            </div>
                            <div>
                                <!-- <label for="txtName">ID:</label> -->
                                <input type="file" name="profileImage" class="avatar-input" id="imageInput">
                                <label for="imageInput" class="btn second-btn">Upload New photo</label>
                            </div>
                        </div>
                        <hr> 
                        <h3>Main Information</h3>
                        <div class="form-group">
                            <!-- <label for="txtName">ID:</label> -->
                            <input 
                                type="hidden" 
                                name="userID" 
                                id="userID" 
                                placeholder="Enter Name" 
                                value = "<?php echo $_SESSION['user']['user_ID'] ?>"
                                required
                            />
                        </div>
                        <div class="form-group">
                            <label for="fullName">Full Name:</label>
                            <input 
                                type="text" 
                                name="fullName" 
                                id="fullName" 
                                placeholder="Enter Name" 
                                value = "<?php echo $_SESSION['user']['name'] ?>"
                                required
                            />
                        </div>
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input 
                                type="text" 
                                name="username" 
                                id="username" 
                                placeholder="Enter Username" 
                                value = "<?php echo $_SESSION['user']['username'] ?>"
                                required
                            />
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address:</label>
                            <input 
                                type="email" 
                                name="email" 
                                id="email" 
                                placeholder="Enter Email Address" 
                                value = "<?php echo $_SESSION['user']['email'] ?>"
                                required
                            />
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number:</label>
                            <input 
                                type="tel" 
                                name="phone" 
                                id="phone" 
                                value = "<?php echo $_SESSION['user']['phone'] ?>"
                                placeholder="Enter Phone Number" 
                            />
                        </div>
                        <div class="form-group">
                            <label for="dob">Date of Birth:</label>
                            <input 
                                type="date" 
                                name="dob" 
                                id="dob" 
                                value = "<?php echo $_SESSION['user']['dob'] ?>"
                                placeholder="Your Birthday" 
                            />
                        </div>
                        <div class="form-group">
                            <label for="address">Address:</label>
                            <textarea name="address" id="address"><?php echo $_SESSION['user']['address'] ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="city">City:</label>
                            <input 
                                type="text" 
                                name="city" 
                                id="city" 
                                value = "<?php echo $_SESSION['user']['city'] ?>"
                                placeholder="Enter Your City" 
                            />
                        </div>
                        <input 
                            type="submit" 
                            value="Update Profile" 
                            class="btn main-btn" 
                            name="updateProfile" 
                            id="submit" 
                        />
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
    <script>
        // Get the file input and profile picture elements
        const imageInput = document.getElementById('imageInput');
        const profilePic = document.getElementById('profilePic');

        // Add an event listener for file input changes
        imageInput.addEventListener('change', function(event) {
            // Get the selected file
            const file = event.target.files[0];

            // Create a FileReader object
            const reader = new FileReader();

            // Set up the FileReader onload function
            reader.onload = function(e) {
                // Update the source attribute of the profile picture
                profilePic.src = e.target.result;
            }

            // Read the selected file as a Data URL
            reader.readAsDataURL(file);
        }) 
    </script>
</body>
</html>