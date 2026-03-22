<?php
    require_once __DIR__ . '/../includes/auth.php';
    yamu_start_session();
    yamu_require_admin('index.php', 'access-denied.php');
    include 'includes/config.php';
    $page_title = "Verify User";

    $userId = (int) ($_GET['user_id'] ?? 0);
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
    $driverProfile = yamu_fetch_role_profile($conn, $userId, 'driver') ?? [];
    $staffProfile = yamu_fetch_role_profile($conn, $userId, 'staff') ?? [];
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
            <h2>Verify User</h2>
            <div class="main-cards">
                <div class="card">
                    <h3><?php echo yamu_e($user['full_name']); ?> (<?php echo yamu_e($user['email']); ?>)</h3>
                    <p>Verification is only for assigned driver and staff roles. Pending roles may complete onboarding, but operational access stays restricted until verified.</p>

                    <?php foreach (['driver', 'staff'] as $verificationRole) { ?>
                        <?php if (!isset($assignments[$verificationRole])) { continue; } ?>
                        <?php $assignment = $assignments[$verificationRole]; ?>
                        <hr>
                        <h3><?php echo yamu_e(yamu_role_label($verificationRole)); ?> Verification</h3>
                        <?php if ($verificationRole === 'driver') { ?>
                            <p>License: <?php echo yamu_e($driverProfile['driving_license_number'] ?? ''); ?></p>
                            <p>NIC: <?php echo yamu_e($driverProfile['nic_id'] ?? ''); ?></p>
                            <p>Service Area: <?php echo yamu_e($driverProfile['service_area'] ?? ''); ?></p>
                            <p>Provider Details: <?php echo yamu_e($driverProfile['provider_details'] ?? ''); ?></p>
                        <?php } ?>
                        <?php if ($verificationRole === 'staff') { ?>
                            <p>Store Name: <?php echo yamu_e($staffProfile['store_name'] ?? ''); ?></p>
                            <p>Store Owner: <?php echo yamu_e($staffProfile['store_owner'] ?? ''); ?></p>
                            <p>Business Reg No: <?php echo yamu_e($staffProfile['business_registration_number'] ?? ''); ?></p>
                        <?php } ?>
                        <p>Current Verification: <strong><?php echo yamu_e(ucfirst($assignment['verification_status'] ?? 'pending')); ?></strong></p>
                        <form action="includes/user-role-management.php" method="POST" class="signup-form">
                            <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                            <input type="hidden" name="role" value="<?php echo yamu_e($verificationRole); ?>">
                            <input type="hidden" name="redirect" value="../user-verify.php">
                            <div class="form-group">
                                <label for="verification_status_<?php echo yamu_e($verificationRole); ?>">Set Verification Status:</label>
                                <select name="verification_status" id="verification_status_<?php echo yamu_e($verificationRole); ?>">
                                    <option value="pending" <?php echo (($assignment['verification_status'] ?? '') === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="verified" <?php echo in_array(($assignment['verification_status'] ?? ''), ['verified', 'approved'], true) ? 'selected' : ''; ?>>Verified</option>
                                    <option value="rejected" <?php echo (($assignment['verification_status'] ?? '') === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                            <button type="submit" name="verifyUserRole" class="btn main-btn">Update Verification</button>
                        </form>
                    <?php } ?>

                    <?php if (!isset($assignments['driver']) && !isset($assignments['staff'])) { ?>
                        <p>This user does not currently have driver or staff roles assigned.</p>
                    <?php } ?>
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
