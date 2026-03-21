<?php
    require_once __DIR__ . '/includes/auth.php';
    carzo_start_session();
    carzo_require_user_roles(['customer', 'driver', 'staff', 'admin'], 'signin.php', ['active', 'pending', 'verified'], 'access-denied.php');
    include 'includes/config.php';
    $page_title = "My Profile";
    $currentUser = carzo_current_user();
    $userId = (int) ($currentUser['user_ID'] ?? 0);
    $userRow = carzo_fetch_user_by_id($conn, $userId);
    $assignments = carzo_fetch_user_roles(
        $conn,
        $userId,
        $currentUser['primary_role'] ?? $currentUser['role'] ?? 'customer',
        $currentUser['account_status'] ?? 'active',
        $currentUser['verification_status'] ?? 'verified'
    );
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
                    $currentAccountPage = 'my-profile';
                    include('includes/account-sidebar.php');
                ?>
                <div class="profile-details card">
                    <h3>My Profile</h3>
                    <div class="form-group">
                        <label>Full Name:</label>
                        <input type="text" value="<?php echo carzo_e($userRow['full_name'] ?? ''); ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="text" value="<?php echo carzo_e($userRow['email'] ?? ''); ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Phone:</label>
                        <input type="text" value="<?php echo carzo_e($userRow['phone'] ?? ''); ?>" readonly />
                    </div>
                    <div class="form-group">
                        <label>Address:</label>
                        <textarea readonly><?php echo carzo_e($userRow['address'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Current Role:</label>
                        <input type="text" value="<?php echo carzo_e(carzo_role_label(carzo_current_user_role())); ?>" readonly />
                    </div>
                    <h3>Assigned Roles</h3>
                    <table id="table">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Verification</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            <?php foreach ($assignments as $roleKey => $assignment) { ?>
                                <?php
                                    $roleStatus = carzo_normalize_role_status($assignment['role_status'] ?? 'active', $roleKey);
                                    $verificationStatus = carzo_normalize_verification_status($assignment['verification_status'] ?? 'verified', $roleKey);
                                ?>
                                <tr>
                                    <td><?php echo carzo_e(carzo_role_label($roleKey)); ?></td>
                                    <td><span class="<?php echo carzo_e(carzo_badge_class($roleStatus)); ?>"><?php echo carzo_e(ucfirst($roleStatus)); ?></span></td>
                                    <td><span class="<?php echo carzo_e(carzo_badge_class($verificationStatus)); ?>"><?php echo carzo_e(ucfirst(str_replace('_', ' ', $verificationStatus))); ?></span></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <div class="form-submit" style="margin-top: 24px;">
                        <a href="edit-profile.php" class="btn main-btn">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include('includes/footer.php'); ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
