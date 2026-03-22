<?php
require_once __DIR__ . '/includes/auth.php';
yamu_start_session();
yamu_require_authenticated_user('signin.php');
include 'includes/config.php';

$page_title = 'My Profile';
$currentUser = yamu_current_user();
$userId = (int) ($currentUser['user_ID'] ?? 0);
$userRow = yamu_fetch_user_by_id($conn, $userId);

if (!$userRow) {
    yamu_redirect_with_message('signin.php', 'error', 'Please sign in to continue');
}

$assignments = yamu_fetch_user_roles(
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
    <?php include 'includes/header.php'; ?>
</head>
<body>
    <?php include 'includes/menu.php'; ?>

    <section class="profile">
        <?php include 'includes/alert.php'; ?>
        <div class="container">
            <div class="row">
                <?php
                $currentAccountPage = 'my-profile';
                include 'includes/account-sidebar.php';
                ?>
                <div class="profile-details card">
                    <h3>My Profile</h3>
                    <div class="form-group">
                        <label>Full Name:</label>
                        <input type="text" value="<?php echo yamu_e($userRow['full_name'] ?? ''); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Username:</label>
                        <input type="text" value="<?php echo yamu_e($userRow['username'] ?? ''); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="text" value="<?php echo yamu_e($userRow['email'] ?? ''); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Phone:</label>
                        <input type="text" value="<?php echo yamu_e($userRow['phone'] ?? ''); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Address:</label>
                        <textarea readonly><?php echo yamu_e($userRow['address'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>City:</label>
                        <input type="text" value="<?php echo yamu_e($userRow['city'] ?? ''); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Date of Birth:</label>
                        <input type="text" value="<?php echo yamu_e($userRow['dob'] ?? ''); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Current Active Role:</label>
                        <input type="text" value="<?php echo yamu_e(yamu_role_label(yamu_current_user_role())); ?>" readonly>
                    </div>

                    <h3>Assigned Roles</h3>
                    <table id="table">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Verification</th>
                                <th>Access</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            <?php foreach ($assignments as $roleKey => $assignment) { ?>
                                <?php
                                $roleStatus = yamu_normalize_role_status($assignment['role_status'] ?? 'active', $roleKey);
                                $verificationStatus = yamu_normalize_verification_status($assignment['verification_status'] ?? 'verified', $roleKey);
                                $accessLabel = yamu_is_role_blocked($roleStatus)
                                    ? 'Blocked'
                                    : (yamu_is_role_pending($roleStatus) ? 'Onboarding only' : 'Full access');
                                ?>
                                <tr>
                                    <td><?php echo yamu_e(yamu_role_label($roleKey)); ?></td>
                                    <td><span class="<?php echo yamu_e(yamu_badge_class($roleStatus)); ?>"><?php echo yamu_e(ucfirst($roleStatus)); ?></span></td>
                                    <td><span class="<?php echo yamu_e(yamu_badge_class($verificationStatus)); ?>"><?php echo yamu_e(ucfirst(str_replace('_', ' ', $verificationStatus))); ?></span></td>
                                    <td><?php echo yamu_e($accessLabel); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                    <div class="form-submit" style="margin-top: 24px;">
                        <a href="edit-profile.php" class="btn main-btn">Edit Common Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
