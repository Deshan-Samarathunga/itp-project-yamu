<?php
require_once __DIR__ . '/includes/auth.php';
yamu_start_session();
yamu_require_authenticated_user('signin.php');
include 'includes/config.php';

$page_title = 'Role Switch';
$currentUser = yamu_current_user();
$userId = (int) ($currentUser['user_ID'] ?? 0);
$assignments = yamu_fetch_user_roles(
    $conn,
    $userId,
    $currentUser['primary_role'] ?? $currentUser['role'] ?? 'customer',
    $currentUser['account_status'] ?? 'active',
    $currentUser['verification_status'] ?? 'verified'
);
$activeRole = yamu_current_user_role();
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
                    <p>Current active role: <strong><?php echo yamu_e(yamu_role_label($activeRole)); ?></strong></p>
                    <p>Only assigned roles that are not blocked can be activated. Pending roles are limited to onboarding until verified.</p>
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
                                    $roleStatus = yamu_normalize_role_status($assignment['role_status'] ?? 'active', $roleKey);
                                    $verificationStatus = yamu_normalize_verification_status($assignment['verification_status'] ?? 'verified', $roleKey);
                                    $canSwitch = !yamu_is_role_blocked($roleStatus);
                                ?>
                                <tr>
                                    <td><?php echo yamu_e(yamu_role_label($roleKey)); ?></td>
                                    <td><span class="<?php echo yamu_e(yamu_badge_class($roleStatus)); ?>"><?php echo yamu_e(ucfirst($roleStatus)); ?></span></td>
                                    <td><span class="<?php echo yamu_e(yamu_badge_class($verificationStatus)); ?>"><?php echo yamu_e(ucfirst(str_replace('_', ' ', $verificationStatus))); ?></span></td>
                                    <td>
                                        <?php if ($activeRole === $roleKey) { ?>
                                            <span class="Status-conpleted-badge">Active</span>
                                        <?php } elseif ($canSwitch) { ?>
                                            <form action="includes/role-management.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="active_role" value="<?php echo yamu_e($roleKey); ?>" />
                                                <input type="hidden" name="redirect_to" value="<?php echo yamu_e(yamu_public_home_path_for_role($roleKey)); ?>" />
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
                    <?php if (yamu_current_user_has_assigned_role('customer') && !yamu_is_admin_panel_role(yamu_current_user_role())) { ?>
                        <div class="form-submit" style="margin-top: 24px;">
                            <a href="role-activation.php" class="btn main-btn">Apply For New Role</a>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>

    <?php include('includes/footer.php'); ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
