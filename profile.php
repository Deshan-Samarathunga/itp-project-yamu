<?php
    require_once __DIR__ . '/includes/auth.php';
    carzo_start_session();
    carzo_require_user_roles(['customer', 'driver'], 'signin.php', ['active', 'pending'], 'index.php');
    $page_title = "Profile"; 
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
                    $currentAccountPage = 'profile';
                    include('includes/account-sidebar.php');
                ?>
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
                            <label for="role">Account Type:</label>
                            <input 
                                type="text" 
                                id="role" 
                                value = "<?php echo ucfirst($_SESSION['user']['role']) ?>"
                                readonly
                            />
                        </div>
                        <div class="form-group">
                            <label for="account_status">Account Status:</label>
                            <input 
                                type="text" 
                                id="account_status" 
                                value = "<?php echo ucfirst($_SESSION['user']['account_status']) ?>"
                                readonly
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
                        <?php if (($_SESSION['user']['role'] ?? 'customer') === 'driver') { ?>
                            <div class="form-group">
                                <label for="verification_status">Verification Status:</label>
                                <input 
                                    type="text" 
                                    id="verification_status" 
                                    value = "<?php echo ucfirst(str_replace('_', ' ', $_SESSION['user']['verification_status'])) ?>"
                                    readonly
                                />
                            </div>
                            <div class="form-group">
                                <label for="license_or_nic">License / NIC:</label>
                                <input 
                                    type="text" 
                                    name="license_or_nic" 
                                    id="license_or_nic" 
                                    value = "<?php echo $_SESSION['user']['license_or_nic'] ?>"
                                    placeholder="Enter License Number or NIC" 
                                    required
                                />
                            </div>
                            <div class="form-group">
                                <label for="bio">Driver Bio:</label>
                                <textarea name="bio" id="bio" placeholder="Tell customers a little about yourself"><?php echo $_SESSION['user']['bio'] ?></textarea>
                            </div>
                        <?php } ?>
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
