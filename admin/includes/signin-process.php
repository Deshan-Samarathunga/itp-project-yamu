<?php
require_once __DIR__ . '/../../includes/auth.php';
carzo_start_session();
include 'config.php';

if (isset($_POST['signin'])) {
    carzo_ensure_users_password_column($conn);

    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        carzo_redirect_with_message('../index.php', 'error', 'Email and password are required');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        carzo_redirect_with_message('../index.php', 'error', 'Please enter a valid email address');
    }

    $user = carzo_fetch_user_by_email($conn, $email);

    if ($user && carzo_password_appears_truncated($user['password'] ?? '')) {
        carzo_redirect_with_message('../index.php', 'error', 'Your password record is outdated. Reset password from the main sign-in page first.');
    }

    if ($user && carzo_password_matches($password, $user['password'])) {
        carzo_upgrade_password_hash_if_needed($conn, (int) $user['user_id'], $password, $user['password']);
        carzo_touch_user_last_login($conn, (int) $user['user_id']);
        $user = carzo_fetch_user_by_id($conn, (int) $user['user_id']) ?: $user;

        $roles = carzo_fetch_user_roles(
            $conn,
            (int) $user['user_id'],
            $user['role'] ?? 'customer',
            $user['account_status'] ?? 'active',
            $user['verification_status'] ?? 'verified'
        );

        $preferredAdminRole = null;
        if (isset($roles['admin'])) {
            $preferredAdminRole = 'admin';
        } elseif (isset($roles['staff'])) {
            $preferredAdminRole = 'staff';
        }

        if ($preferredAdminRole !== null) {
            $assignment = $roles[$preferredAdminRole];
            if (carzo_is_role_blocked($assignment['role_status'] ?? 'active')) {
                carzo_redirect_with_message('../index.php', 'error', 'Your admin role is currently suspended or unavailable');
            }

            carzo_set_user_session($user, $conn, $preferredAdminRole);
            carzo_redirect_with_message('../dashboard.php', 'msg', 'Signin Successful');
        }
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
