<?php
require_once __DIR__ . '/includes/auth.php';
yamu_start_session();
yamu_require_authenticated_user('signin.php');
include 'includes/config.php';

$page_title = 'Edit Profile';
$userId = (int) ($_SESSION['user']['user_ID'] ?? 0);
$user = yamu_fetch_user_by_id($conn, $userId);

if (!$user) {
    yamu_redirect_with_message('signin.php', 'error', 'Please sign in to continue');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
</head>
<body>
    <?php include 'includes/menu.php'; ?>

    <section class="profile">
        <?php include 'includes/alert.php'; ?>
        <div class="container">
            <div class="row">
                <?php
                $currentAccountPage = 'edit-profile';
                include 'includes/account-sidebar.php';
                ?>
                <div class="profile-details card">
                    <form action="includes/profile-setting.php" method="POST" enctype="multipart/form-data" class="signup-form">
                        <h3>Update Profile Picture</h3>
                        <div class="row">
                            <div class="avatar">
                                <img src="<?php echo yamu_e(yamu_profile_avatar_path($user['profile_pic'] ?? 'avatar.png')); ?>" alt="avatar" id="profilePic">
                            </div>
                            <div>
                                <input type="file" name="profileImage" class="avatar-input" id="imageInput">
                                <label for="imageInput" class="btn second-btn">Upload New Photo</label>
                            </div>
                        </div>
                        <hr>
                        <h3>Common Profile Information</h3>
                        <div class="form-group">
                            <label for="fullName">Full Name:</label>
                            <input type="text" name="fullName" id="fullName" placeholder="Enter Name" value="<?php echo yamu_e($user['full_name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" name="username" id="username" placeholder="Enter Username" value="<?php echo yamu_e($user['username'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address:</label>
                            <input type="email" name="email" id="email" placeholder="Enter Email Address" value="<?php echo yamu_e($user['email'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number:</label>
                            <input type="tel" name="phone" id="phone" value="<?php echo yamu_e($user['phone'] ?? ''); ?>" placeholder="Enter Phone Number">
                        </div>
                        <div class="form-group">
                            <label for="dob">Date of Birth:</label>
                            <input type="date" name="dob" id="dob" value="<?php echo yamu_e($user['dob'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="address">Address:</label>
                            <textarea name="address" id="address"><?php echo yamu_e($user['address'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="city">City:</label>
                            <input type="text" name="city" id="city" value="<?php echo yamu_e($user['city'] ?? ''); ?>" placeholder="Enter Your City">
                        </div>
                        <div class="form-submit">
                            <input type="submit" value="Update Profile" class="btn main-btn" name="updateProfile" id="submit">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
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
