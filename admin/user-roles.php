<?php
    require_once __DIR__ . '/../includes/auth.php';
    carzo_start_session();
    carzo_require_admin('index.php', 'access-denied.php');
    include 'includes/config.php';
    $page_title = "User Roles";

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
    $allRoles = carzo_fetch_available_roles($conn);
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
            <h2>Assign / Update Role</h2>
            <div class="main-cards">
                <div class="card">
                    <h3><?php echo carzo_e($user['full_name']); ?> (<?php echo carzo_e($user['email']); ?>)</h3>
                    <table id="table">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Verification</th>
                                <th>Primary</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            <?php foreach ($assignments as $roleKey => $assignment) { ?>
                                <tr>
                                    <td><?php echo carzo_e(carzo_role_label($roleKey)); ?></td>
                                    <td><?php echo carzo_e(ucfirst($assignment['role_status'] ?? 'active')); ?></td>
                                    <td><?php echo carzo_e(ucfirst($assignment['verification_status'] ?? 'verified')); ?></td>
                                    <td><?php echo !empty($assignment['is_primary']) ? 'Yes' : 'No'; ?></td>
                                    <td>
                                        <?php if (count($assignments) > 1) { ?>
                                            <form action="includes/user-role-management.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                                                <input type="hidden" name="role" value="<?php echo carzo_e($roleKey); ?>">
                                                <input type="hidden" name="redirect" value="../user-roles.php">
                                                <button type="submit" name="removeRole" class="btn second-btn">Remove</button>
                                            </form>
                                        <?php } else { ?>
                                            <span class="Status-pending-badge">Required</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <hr>
                    <h3>Assign New / Update Role</h3>
                    <form action="includes/user-role-management.php" method="POST" class="signup-form">
                        <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                        <input type="hidden" name="redirect" value="../user-roles.php">
                        <div class="form-group">
                            <label for="role">Role:</label>
                            <select name="role" id="role" required>
                                <?php foreach ($allRoles as $role) { ?>
                                    <option value="<?php echo carzo_e($role['role_key']); ?>"><?php echo carzo_e($role['role_name']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="role_status">Role Status:</label>
                            <select name="role_status" id="role_status">
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="verified">Verified</option>
                                <option value="suspended">Suspended</option>
                                <option value="rejected">Rejected</option>
                                <option value="deactivated">Deactivated</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="verification_status">Verification Status:</label>
                            <select name="verification_status" id="verification_status">
                                <option value="verified">Verified</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="unverified">Unverified</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="is_primary">Set As Primary Role:</label>
                            <select name="is_primary" id="is_primary">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="notes">Notes:</label>
                            <textarea name="notes" id="notes"></textarea>
                        </div>
                        <input type="submit" value="Save Role" class="btn main-btn" name="assignRole">
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
</body>
</html>
