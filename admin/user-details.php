<?php
    require_once __DIR__ . '/../includes/auth.php';
    carzo_start_session();
    carzo_require_admin('index.php', 'access-denied.php');
    include 'includes/config.php';
    $page_title = "User Details";

    $userId = (int) ($_GET['user_id'] ?? 0);
    $user = carzo_fetch_user_by_id($conn, $userId);

    if (!$user) {
        carzo_redirect_with_message('users.php', 'error', 'User not found');
    }

    $assignments = carzo_fetch_user_roles(
        $conn,
        $userId,
        $user['role'] ?? 'customer',
        $user['account_status'] ?? 'active',
        $user['verification_status'] ?? 'verified'
    );

    $customerProfile = carzo_fetch_role_profile($conn, $userId, 'customer');
    $driverProfile = carzo_fetch_role_profile($conn, $userId, 'driver');
    $staffProfile = carzo_fetch_role_profile($conn, $userId, 'staff');
    $adminProfile = carzo_fetch_role_profile($conn, $userId, 'admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('includes/header.php'); ?>
</head>
<body>
    <div class="grid-container">
        <?php include('includes/menu.php'); ?>
        <?php include('includes/aside.php'); ?>

        <main class="main">
            <?php include('../includes/alert.php'); ?>
            <h2>User Details</h2>
            <div class="main-cards">
                <div class="card">
                    <div class="row" style="align-items: center; gap: 20px;">
                        <img src="../assets/images/uploads/avatar/<?php echo carzo_e($user['profile_pic'] ?: 'avatar.png'); ?>" alt="avatar" class="table-avatar" style="width: 84px; height: 84px;">
                        <div>
                            <h3><?php echo carzo_e($user['full_name']); ?></h3>
                            <p><?php echo carzo_e($user['email']); ?></p>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label>Username:</label>
                        <input type="text" value="<?php echo carzo_e($user['username']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Phone:</label>
                        <input type="text" value="<?php echo carzo_e($user['phone']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Address:</label>
                        <textarea readonly><?php echo carzo_e($user['address']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Account Status:</label>
                        <input type="text" value="<?php echo carzo_e(ucfirst($user['account_status'])); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Created:</label>
                        <input type="text" value="<?php echo carzo_e($user['created_at'] ?: $user['rag_date']); ?>" readonly>
                    </div>
                    <h3>Roles</h3>
                    <table id="table">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Verification</th>
                                <th>Primary</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            <?php foreach ($assignments as $roleKey => $assignment) { ?>
                                <tr>
                                    <td><?php echo carzo_e(carzo_role_label($roleKey)); ?></td>
                                    <td><span class="<?php echo carzo_e(carzo_badge_class($assignment['role_status'] ?? 'active')); ?>"><?php echo carzo_e(ucfirst($assignment['role_status'] ?? 'active')); ?></span></td>
                                    <td><span class="<?php echo carzo_e(carzo_badge_class($assignment['verification_status'] ?? 'verified')); ?>"><?php echo carzo_e(ucfirst($assignment['verification_status'] ?? 'verified')); ?></span></td>
                                    <td><?php echo !empty($assignment['is_primary']) ? 'Yes' : 'No'; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                    <?php if ($driverProfile) { ?>
                        <h3>Driver Profile</h3>
                        <p>License: <?php echo carzo_e($driverProfile['driving_license_number'] ?? ''); ?></p>
                        <p>NIC: <?php echo carzo_e($driverProfile['nic_id'] ?? ''); ?></p>
                        <p>Service Area: <?php echo carzo_e($driverProfile['service_area'] ?? ''); ?></p>
                    <?php } ?>

                    <?php if ($staffProfile) { ?>
                        <h3>Staff Profile</h3>
                        <p>Store Name: <?php echo carzo_e($staffProfile['store_name'] ?? ''); ?></p>
                        <p>Store Owner: <?php echo carzo_e($staffProfile['store_owner'] ?? ''); ?></p>
                        <p>Business Reg No: <?php echo carzo_e($staffProfile['business_registration_number'] ?? ''); ?></p>
                    <?php } ?>

                    <?php if ($customerProfile) { ?>
                        <h3>Customer Profile</h3>
                        <p><?php echo carzo_e($customerProfile['preferences'] ?? 'No customer preferences provided'); ?></p>
                    <?php } ?>

                    <?php if ($adminProfile) { ?>
                        <h3>Admin Profile</h3>
                        <p>Permissions: <?php echo carzo_e($adminProfile['system_permissions'] ?? 'all'); ?></p>
                    <?php } ?>

                    <div class="form-submit" style="margin-top: 20px;">
                        <a href="user-roles.php?user_id=<?php echo $userId; ?>" class="btn main-btn">Assign / Update Role</a>
                        <a href="user-verify.php?user_id=<?php echo $userId; ?>" class="btn second-btn">Verify User</a>
                        <a href="user-status.php?user_id=<?php echo $userId; ?>" class="btn second-btn">Manage Status</a>
                    </div>
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
