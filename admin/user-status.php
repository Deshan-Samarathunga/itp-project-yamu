<?php
    require_once __DIR__ . '/../includes/auth.php';
    carzo_start_session();
    carzo_require_admin('index.php', 'access-denied.php');
    include 'includes/config.php';
    $page_title = "User Status";

    $userId = (int) ($_GET['user_id'] ?? 0);
    $user = carzo_fetch_user_by_id($conn, $userId);

    if (!$user) {
        carzo_redirect_with_message('users.php', 'error', 'User not found');
    }

    $statuses = ['active', 'pending', 'verified', 'suspended', 'rejected', 'deactivated'];
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
            <h2>Account Status Management</h2>
            <div class="main-cards">
                <div class="card">
                    <h3><?php echo carzo_e($user['full_name']); ?> (<?php echo carzo_e($user['email']); ?>)</h3>
                    <p>Current Status:
                        <span class="<?php echo carzo_e(carzo_badge_class($user['account_status'])); ?>">
                            <?php echo carzo_e(ucfirst($user['account_status'])); ?>
                        </span>
                    </p>

                    <form action="includes/user-role-management.php" method="POST" class="signup-form">
                        <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                        <input type="hidden" name="redirect" value="../user-status.php">
                        <div class="form-group">
                            <label for="account_status">Set New Status:</label>
                            <select name="account_status" id="account_status">
                                <?php foreach ($statuses as $status) { ?>
                                    <option value="<?php echo carzo_e($status); ?>" <?php echo ($user['account_status'] === $status) ? 'selected' : ''; ?>>
                                        <?php echo carzo_e(ucfirst($status)); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <button type="submit" name="updateUserStatus" class="btn main-btn">Update Status</button>
                    </form>

                    <div class="form-submit" style="margin-top: 16px;">
                        <a href="user-details.php?user_id=<?php echo $userId; ?>" class="btn second-btn">Back to User Details</a>
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
