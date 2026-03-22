<?php
require_once __DIR__ . '/includes/auth.php';
yamu_start_session();
yamu_require_authenticated_user('signin.php');
include 'includes/config.php';

$page_title = 'Choose Role';
$currentUser = yamu_current_user();
$userId = (int) ($currentUser['user_ID'] ?? 0);
$assignments = yamu_fetch_user_roles(
    $conn,
    $userId,
    $currentUser['primary_role'] ?? $currentUser['role'] ?? 'customer',
    $currentUser['account_status'] ?? 'active',
    $currentUser['verification_status'] ?? 'verified'
);
$switchableRoles = [];

foreach ($assignments as $roleKey => $assignment) {
    $roleStatus = yamu_normalize_role_status($assignment['role_status'] ?? 'active', $roleKey);

    if (!yamu_is_role_blocked($roleStatus)) {
        $switchableRoles[] = $roleKey;
    }
}

if (count($switchableRoles) === 1) {
    $role = $switchableRoles[0];
    $errorMessage = null;
    if (yamu_switch_active_role($conn, $role, $errorMessage)) {
        yamu_redirect_with_message(yamu_public_home_path_for_role($role), 'msg', 'Role selected successfully');
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
                <?php if (empty($switchableRoles)) { ?>
                    <p>No assigned roles are currently available. Please contact support or an administrator.</p>
                <?php } ?>
                <?php foreach ($assignments as $roleKey => $assignment) { ?>
                    <?php
                        $roleStatus = yamu_normalize_role_status($assignment['role_status'] ?? 'active', $roleKey);
                        $verificationStatus = yamu_normalize_verification_status($assignment['verification_status'] ?? 'verified', $roleKey);
                        $canSwitch = !yamu_is_role_blocked($roleStatus);
                    ?>
                    <div class="card" style="margin-bottom: 12px; text-align: left;">
                        <h4><?php echo yamu_e(yamu_role_label($roleKey)); ?></h4>
                        <p>Status:
                            <span class="<?php echo yamu_e(yamu_badge_class($roleStatus)); ?>">
                                <?php echo yamu_e(ucfirst($roleStatus)); ?>
                            </span>
                        </p>
                        <p>Verification:
                            <span class="<?php echo yamu_e(yamu_badge_class($verificationStatus)); ?>">
                                <?php echo yamu_e(ucfirst(str_replace('_', ' ', $verificationStatus))); ?>
                            </span>
                        </p>
                        <?php if ($roleStatus === 'pending') { ?>
                            <p>Pending roles can be used for onboarding only until they are approved.</p>
                        <?php } ?>
                        <?php if ($canSwitch) { ?>
                            <form action="includes/role-management.php" method="POST">
                                <input type="hidden" name="active_role" value="<?php echo yamu_e($roleKey); ?>" />
                                <input type="hidden" name="redirect_to" value="<?php echo yamu_e(yamu_public_home_path_for_role($roleKey)); ?>" />
                                <button type="submit" name="switchRole" class="btn main-btn">Use This Role</button>
                            </form>
                        <?php } else { ?>
                            <span class="Status-inactive-badge">This role is currently unavailable</span>
                        <?php } ?>
                    </div>
                <?php } ?>
                <?php if (yamu_current_user_has_assigned_role('customer') && !yamu_is_admin_panel_role(yamu_current_user_role())) { ?>
                    <p><a href="role-activation.php">Need another role? Apply here.</a></p>
                <?php } ?>
            </div>
        </div>
    </section>

    <?php include('includes/footer.php'); ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
