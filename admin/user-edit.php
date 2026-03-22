<?php
    require_once __DIR__ . '/../includes/auth.php';
    yamu_start_session();
    yamu_require_admin('index.php');
    include 'includes/config.php';
    $page_title = "Users";

    $userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
    $user = yamu_fetch_user_by_id($conn, $userId);

    if (!$user) {
        yamu_redirect_with_message('users.php', 'error', 'User not found');
    }

    $assignments = yamu_fetch_user_roles(
        $conn,
        $userId,
        $user['role'] ?? 'customer',
        $user['account_status'] ?? 'active',
        $user['verification_status'] ?? 'verified'
    );
    $isSeededAdminUser = isset($assignments['admin']);
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
        <?php
            include('includes/menu.php');
        ?>

        <?php
            include('includes/aside.php');
        ?>

        <main class="main">
            <?php
                include('../includes/alert.php');
            ?>
            <h2>Edit User</h2>

            <div class="main-cards">
                <div class="card">
                    <form action="includes/user-process.php" method="POST" enctype="multipart/form-data" class="signup-form">
                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>" />
                        <div class="form-group">
                            <label for="fullName">Full Name:</label>
                            <input type="text" name="fullName" id="fullName" value="<?php echo yamu_e($user['full_name']); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address:</label>
                            <input type="email" name="email" id="email" value="<?php echo yamu_e($user['email']); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" name="username" id="username" value="<?php echo yamu_e($user['username']); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="password">New Password:</label>
                            <input type="password" name="password" id="password" placeholder="Leave blank to keep current password" />
                        </div>
                        <div class="form-group">
                            <label for="role">Role:</label>
                            <?php if ($isSeededAdminUser) { ?>
                                <input type="hidden" name="role" id="role" value="admin" />
                                <input type="text" value="Admin (DB-seeded only)" readonly />
                            <?php } else { ?>
                                <select name="role" id="role" onchange="toggleDriverFields()">
                                    <option value="customer" <?php echo ($user['role'] === 'customer') ? 'selected' : ''; ?>>Customer</option>
                                    <option value="driver" <?php echo ($user['role'] === 'driver') ? 'selected' : ''; ?>>Driver</option>
                                    <option value="staff" <?php echo ($user['role'] === 'staff') ? 'selected' : ''; ?>>Staff</option>
                                </select>
                            <?php } ?>
                        </div>
                        <div class="form-group">
                            <label for="account_status">Account Status:</label>
                            <select name="account_status" id="account_status">
                                <option value="active" <?php echo ($user['account_status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="pending" <?php echo ($user['account_status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="verified" <?php echo ($user['account_status'] === 'verified') ? 'selected' : ''; ?>>Verified</option>
                                <option value="suspended" <?php echo ($user['account_status'] === 'suspended') ? 'selected' : ''; ?>>Suspended</option>
                                <option value="rejected" <?php echo ($user['account_status'] === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                                <option value="deactivated" <?php echo ($user['account_status'] === 'deactivated') ? 'selected' : ''; ?>>Deactivated</option>
                            </select>
                        </div>
                        <div class="form-group" id="verification-row">
                            <label for="verification_status">Verification Status:</label>
                            <select name="verification_status" id="verification_status">
                                <option value="verified" <?php echo ($user['verification_status'] === 'verified') ? 'selected' : ''; ?>>Verified</option>
                                <option value="pending" <?php echo ($user['verification_status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo ($user['verification_status'] === 'approved') ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo ($user['verification_status'] === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                                <option value="unverified" <?php echo ($user['verification_status'] === 'unverified') ? 'selected' : ''; ?>>Unverified</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number:</label>
                            <input type="tel" name="phone" id="phone" value="<?php echo yamu_e($user['phone']); ?>" />
                        </div>
                        <div class="form-group">
                            <label for="dob">Date of Birth:</label>
                            <input type="date" name="dob" id="dob" value="<?php echo yamu_e($user['dob']); ?>" />
                        </div>
                        <div class="form-group">
                            <label for="city">City:</label>
                            <input type="text" name="city" id="city" value="<?php echo yamu_e($user['city']); ?>" />
                        </div>
                        <div class="form-group">
                            <label for="address">Address:</label>
                            <textarea name="address" id="address"><?php echo yamu_e($user['address']); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="profileImage">Profile Image:</label>
                            <input type="file" name="profileImage" id="profileImage" class="custom" />
                        </div>
                        <div id="driver-fields">
                            <div class="form-group">
                                <label for="license_or_nic">License / NIC:</label>
                                <input type="text" name="license_or_nic" id="license_or_nic" value="<?php echo yamu_e($user['license_or_nic']); ?>" />
                            </div>
                            <div class="form-group">
                                <label for="bio">Driver Bio:</label>
                                <textarea name="bio" id="bio"><?php echo yamu_e($user['bio']); ?></textarea>
                            </div>
                        </div>
                        <input type="reset" value="Cancel" class="btn second-btn" />
                        <input type="submit" value="Update User" class="btn main-btn" name="updateUser" />
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
    <script>
        function toggleDriverFields() {
            const roleField = document.getElementById("role");
            const role = roleField ? roleField.value : "customer";
            const driverFields = document.getElementById("driver-fields");
            const verificationRow = document.getElementById("verification-row");
            const licenseInput = document.getElementById("license_or_nic");

            if (role === "driver") {
                driverFields.style.display = "block";
                verificationRow.style.display = "flex";
                licenseInput.required = true;
            } else if (role === "staff") {
                driverFields.style.display = "none";
                verificationRow.style.display = "flex";
                licenseInput.required = false;
            } else {
                driverFields.style.display = "none";
                verificationRow.style.display = "none";
                licenseInput.required = false;
                document.getElementById("verification_status").value = "verified";
            }
        }

        toggleDriverFields();
    </script>
</body>
</html>
