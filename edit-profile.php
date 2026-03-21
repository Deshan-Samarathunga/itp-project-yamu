<?php
    require_once __DIR__ . '/includes/auth.php';
    carzo_start_session();
    carzo_require_user_roles(['customer', 'driver', 'staff', 'admin'], 'signin.php', ['active', 'pending', 'verified'], 'access-denied.php');
    $page_title = "Edit Profile";
    $activeRole = carzo_current_user_role();
    $assignedRoles = carzo_current_user_roles();
    $hasDriverRole = in_array('driver', $assignedRoles, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('includes/header.php'); ?>
</head>
<body>
    <?php include('includes/menu.php'); ?>

    <section class="profile">
        <?php include('includes/alert.php'); ?>
        <div class="container">
            <div class="row">
                <?php
                    $currentAccountPage = 'edit-profile';
                    include('includes/account-sidebar.php');
                ?>
                <div class="profile-details card">
                    <form action="includes/profile-setting.php" method="POST" enctype="multipart/form-data" class="signup-form">
                        <h3>Update Profile Picture</h3>
                        <div class="row">
                            <div class="avatar">
                                <img src="assets/images/uploads/avatar/<?php echo carzo_e($_SESSION['user']['avatar']); ?>" alt="avatar" id="profilePic">
                            </div>
                            <div>
                                <input type="file" name="profileImage" class="avatar-input" id="imageInput">
                                <label for="imageInput" class="btn second-btn">Upload New photo</label>
                            </div>
                        </div>
                        <hr>
                        <h3>Main Information</h3>
                        <input type="hidden" name="userID" id="userID" value="<?php echo (int) $_SESSION['user']['user_ID']; ?>" required />
                        <div class="form-group">
                            <label for="role">Current Active Role:</label>
                            <input type="text" id="role" value="<?php echo carzo_e(carzo_role_label($activeRole)); ?>" readonly />
                        </div>
                        <div class="form-group">
                            <label for="account_status">Role Status:</label>
                            <input type="text" id="account_status" value="<?php echo carzo_e(ucfirst($_SESSION['user']['account_status'])); ?>" readonly />
                        </div>
                        <div class="form-group">
                            <label for="fullName">Full Name:</label>
                            <input type="text" name="fullName" id="fullName" placeholder="Enter Name" value="<?php echo carzo_e($_SESSION['user']['name']); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" name="username" id="username" placeholder="Enter Username" value="<?php echo carzo_e($_SESSION['user']['username']); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address:</label>
                            <input type="email" name="email" id="email" placeholder="Enter Email Address" value="<?php echo carzo_e($_SESSION['user']['email']); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number:</label>
                            <input type="tel" name="phone" id="phone" value="<?php echo carzo_e($_SESSION['user']['phone']); ?>" placeholder="Enter Phone Number" />
                        </div>
                        <div class="form-group">
                            <label for="dob">Date of Birth:</label>
                            <input type="date" name="dob" id="dob" value="<?php echo carzo_e($_SESSION['user']['dob']); ?>" />
                        </div>
                        <div class="form-group">
                            <label for="address">Address:</label>
                            <textarea name="address" id="address"><?php echo carzo_e($_SESSION['user']['address']); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="city">City:</label>
                            <input type="text" name="city" id="city" value="<?php echo carzo_e($_SESSION['user']['city']); ?>" placeholder="Enter Your City" />
                        </div>
                        <?php if ($hasDriverRole) { ?>
                            <div class="form-group">
                                <label for="verification_status">Verification Status:</label>
                                <input type="text" id="verification_status" value="<?php echo carzo_e(ucfirst(str_replace('_', ' ', $_SESSION['user']['verification_status']))); ?>" readonly />
                            </div>
                            <div class="form-group">
                                <label for="license_or_nic">License / NIC:</label>
                                <input type="text" name="license_or_nic" id="license_or_nic" value="<?php echo carzo_e($_SESSION['user']['license_or_nic']); ?>" placeholder="Enter License Number or NIC" />
                            </div>
                            <div class="form-group">
                                <label for="bio">Driver Bio:</label>
                                <textarea name="bio" id="bio" placeholder="Tell customers a little about yourself"><?php echo carzo_e($_SESSION['user']['bio']); ?></textarea>
                            </div>
                        <?php } ?>
                        <input type="submit" value="Update Profile" class="btn main-btn" name="updateProfile" id="submit" />
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php include('includes/footer.php'); ?>
    <script src="assets/js/main.js"></script>
    <script>
        const imageInput = document.getElementById('imageInput');
        const profilePic = document.getElementById('profilePic');

        imageInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (!file) {
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                profilePic.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    </script>
</body>
</html>
