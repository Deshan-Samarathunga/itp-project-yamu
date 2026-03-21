<?php
    require_once __DIR__ . '/includes/auth.php';
    carzo_start_session();

    if (!carzo_is_user_authenticated()) {
        carzo_redirect_with_message('signin.php', 'error', 'Please sign in to continue');
    }

    include 'includes/config.php';
    $page_title = "Choose Role";
    $currentUser = carzo_current_user();
    $userId = (int) ($currentUser['user_ID'] ?? 0);
    $assignments = carzo_fetch_user_roles(
        $conn,
        $userId,
        $currentUser['primary_role'] ?? $currentUser['role'] ?? 'customer',
        $currentUser['account_status'] ?? 'active',
        $currentUser['verification_status'] ?? 'verified'
    );
    $switchableRoles = [];

    foreach ($assignments as $roleKey => $assignment) {
        if (!carzo_is_role_blocked($assignment['role_status'] ?? 'active')) {
            $switchableRoles[] = $roleKey;
        }
    }

    if (count($switchableRoles) === 1) {
        $role = $switchableRoles[0];
        $errorMessage = null;
        if (carzo_switch_active_role($conn, $role, $errorMessage)) {
            carzo_redirect_with_message(carzo_public_home_path_for_role($role), 'msg', 'Role selected successfully');
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('includes/header.php'); ?>
</head>
<body>
    <?php include('includes/menu.php'); ?>

    <section class="register">
        <?php include('includes/alert.php'); ?>
        <div class="container">
            <div class="signup-content">
                <h3>Choose Active Role</h3>
                <p>Select the role you want to use for this session.</p>
                <?php foreach ($assignments as $roleKey => $assignment) { ?>
                    <?php
                        $roleStatus = carzo_normalize_role_status($assignment['role_status'] ?? 'active', $roleKey);
                        $verificationStatus = carzo_normalize_verification_status($assignment['verification_status'] ?? 'verified', $roleKey);
                        $canSwitch = !carzo_is_role_blocked($roleStatus);
                    ?>
                    <div class="card" style="margin-bottom: 12px; text-align: left;">
                        <h4><?php echo carzo_e(carzo_role_label($roleKey)); ?></h4>
                        <p>Status:
                            <span class="<?php echo carzo_e(carzo_badge_class($roleStatus)); ?>">
                                <?php echo carzo_e(ucfirst($roleStatus)); ?>
                            </span>
                        </p>
                        <p>Verification:
                            <span class="<?php echo carzo_e(carzo_badge_class($verificationStatus)); ?>">
                                <?php echo carzo_e(ucfirst(str_replace('_', ' ', $verificationStatus))); ?>
                            </span>
                        </p>
                        <?php if ($canSwitch) { ?>
                            <form action="includes/role-management.php" method="POST">
                                <input type="hidden" name="active_role" value="<?php echo carzo_e($roleKey); ?>" />
                                <input type="hidden" name="redirect_to" value="<?php echo carzo_e(carzo_public_home_path_for_role($roleKey)); ?>" />
                                <button type="submit" name="switchRole" class="btn main-btn">Use This Role</button>
                            </form>
                        <?php } else { ?>
                            <span class="Status-inactive-badge">This role is currently unavailable</span>
                        <?php } ?>
                    </div>
                <?php } ?>
                <p><a href="role-activation.php">Need another role? Activate here.</a></p>
            </div>
        </div>
    </section>

    <?php include('includes/footer.php'); ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
