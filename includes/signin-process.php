<?php
require_once __DIR__ . '/auth.php';
carzo_start_session();
include 'config.php';

if (isset($_POST['signin'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $user = carzo_fetch_user_by_email($conn, $email);

    if (!$user || !carzo_password_matches($password, $user['password'])) {
        carzo_redirect_with_message('../signin.php', 'error', 'Invalid username or password');
    }

    $role = carzo_normalize_role($user['role'] ?? 'customer');
    $accountStatus = carzo_normalize_account_status($user['account_status'] ?? 'active', $role);

    if ($accountStatus === 'suspended') {
        carzo_redirect_with_message('../signin.php', 'error', 'Your account has been suspended. Please contact support.');
    }

    if (carzo_is_admin_panel_role($role)) {
        carzo_set_admin_session_from_user($user);
        carzo_redirect_with_message('../admin/dashboard.php', 'msg', 'Signin Successful');
    }

    carzo_set_user_session($user);

    if ($role === 'driver') {
        $message = $accountStatus === 'pending'
            ? 'Driver account created. Your verification is still pending.'
            : 'Signin Successful';

        carzo_redirect_with_message('../driver-dashboard.php', 'msg', $message);
    }

    carzo_redirect_with_message('../index.php', 'msg', 'Signin Successful');
}
