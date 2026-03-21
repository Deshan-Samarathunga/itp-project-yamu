<?php
require_once __DIR__ . '/../../includes/auth.php';
carzo_start_session();
include 'config.php';

if (isset($_POST['signin'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $user = carzo_fetch_user_by_email($conn, $email);

    $role = carzo_normalize_role($user['role'] ?? 'customer');

    if ($user && carzo_is_admin_panel_role($role) && carzo_password_matches($password, $user['password'])) {
        if (carzo_normalize_account_status($user['account_status'] ?? 'active', $role) === 'suspended') {
            carzo_redirect_with_message('../index.php', 'error', 'Your admin account is currently suspended');
        }

        carzo_set_admin_session_from_user($user);
        carzo_redirect_with_message('../dashboard.php', 'msg', 'Signin Successful');
    }

    $stmt = $conn->prepare('SELECT * FROM admin WHERE email = ? LIMIT 1');

    if ($stmt) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $legacyAdmin = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if ($legacyAdmin && carzo_password_matches($password, $legacyAdmin['password'])) {
            carzo_set_admin_session_from_legacy_admin($legacyAdmin);
            carzo_redirect_with_message('../dashboard.php', 'msg', 'Signin Successful');
        }
    }

    carzo_redirect_with_message('../index.php', 'error', 'Invalid email or password');
}
