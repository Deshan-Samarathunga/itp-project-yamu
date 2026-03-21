<?php
    require_once __DIR__ . '/includes/auth.php';
    carzo_start_session();

    if (!carzo_is_user_authenticated()) {
        carzo_redirect_with_message('signin.php', 'error', 'Please sign in to continue');
    }

    include 'includes/config.php';
    $page_title = "Role Switch";
    $currentUser = carzo_current_user();
    $userId = (int) ($currentUser['user_ID'] ?? 0);
    $assignments = carzo_fetch_user_roles(
        $conn,
        $userId,
        $currentUser['primary_role'] ?? $currentUser['role'] ?? 'customer',
        $currentUser['account_status'] ?? 'active',
        $currentUser['verification_status'] ?? 'verified'
    );
    $activeRole = carzo_current_user_role();
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
                    $currentAccountPage = 'role-switch';
                    include('includes/account-sidebar.php');
                ?>
                <div class="profile-details card">
                    <h3>Switch Role</h3>
                    <p>Current active role: <strong><?php echo carzo_e(carzo_role_label($activeRole)); ?></strong></p>
                    <table id="table">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Verification</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            <?php foreach ($assignments as $roleKey => $assignment) { ?>
                                <?php
                                    $roleStatus = carzo_normalize_role_status($assignment['role_status'] ?? 'active', $roleKey);
                                    $verificationStatus = carzo_normalize_verification_status($assignment['verification_status'] ?? 'verified', $roleKey);
                                    $canSwitch = !carzo_is_role_blocked($roleStatus);
                                ?>
                                <tr>
                                    <td><?php echo carzo_e(carzo_role_label($roleKey)); ?></td>
                                    <td><span class="<?php echo carzo_e(carzo_badge_class($roleStatus)); ?>"><?php echo carzo_e(ucfirst($roleStatus)); ?></span></td>
                                    <td><span class="<?php echo carzo_e(carzo_badge_class($verificationStatus)); ?>"><?php echo carzo_e(ucfirst(str_replace('_', ' ', $verificationStatus))); ?></span></td>
                                    <td>
                                        <?php if ($activeRole === $roleKey) { ?>
                                            <span class="Status-conpleted-badge">Active</span>
                                        <?php } elseif ($canSwitch) { ?>
                                            <form action="includes/role-management.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="active_role" value="<?php echo carzo_e($roleKey); ?>" />
                                                <input type="hidden" name="redirect_to" value="<?php echo carzo_e(carzo_public_home_path_for_role($roleKey)); ?>" />
                                                <button type="submit" name="switchRole" class="btn second-btn">Switch</button>
                                            </form>
                                        <?php } else { ?>
                                            <span class="Status-inactive-badge">Unavailable</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <div class="form-submit" style="margin-top: 24px;">
                        <a href="role-activation.php" class="btn main-btn">Activate New Role</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include('includes/footer.php'); ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
