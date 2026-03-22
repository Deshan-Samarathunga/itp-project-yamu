<?php
require_once __DIR__ . '/../../includes/auth.php';
yamu_start_session();
include 'config.php';

if (isset($_POST['signin'])) {
    yamu_ensure_users_password_column($conn);

    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        yamu_redirect_with_message('../index.php', 'error', 'Email and password are required');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        yamu_redirect_with_message('../index.php', 'error', 'Please enter a valid email address');
    }

    $user = yamu_fetch_user_by_email($conn, $email);

    if ($user && yamu_password_appears_truncated($user['password'] ?? '')) {
        yamu_redirect_with_message('../index.php', 'error', 'Your password record is outdated. Reset password from the main sign-in page first.');
    }

    if ($user && yamu_password_matches($password, $user['password'])) {
        yamu_upgrade_password_hash_if_needed($conn, (int) $user['user_id'], $password, $user['password']);
        yamu_touch_user_last_login($conn, (int) $user['user_id']);
        $user = yamu_fetch_user_by_id($conn, (int) $user['user_id']) ?: $user;

        $roles = yamu_fetch_user_roles(
            $conn,
            (int) $user['user_id'],
            $user['role'] ?? 'customer',
            $user['account_status'] ?? 'active',
            $user['verification_status'] ?? 'verified'
        );

        if (isset($roles['admin'])) {
            $assignment = $roles['admin'];
            if (yamu_is_role_blocked($assignment['role_status'] ?? 'active')) {
                yamu_redirect_with_message('../index.php', 'error', 'Your admin role is currently suspended or unavailable');
            }

            if (!yamu_role_allows_standard_status($assignment['role_status'] ?? 'active')) {
                yamu_redirect_with_message('../index.php', 'error', 'Your admin role is not active yet');
            }

            yamu_set_user_session($user, $conn, 'admin');
            yamu_redirect_with_message('../dashboard.php', 'msg', 'Signin Successful');
        }
    }

    $stmt = $conn->prepare('SELECT * FROM admin WHERE email = ? LIMIT 1');

    if ($stmt) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $legacyAdmin = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if ($legacyAdmin && yamu_password_matches($password, $legacyAdmin['password'])) {
            yamu_set_admin_session_from_legacy_admin($legacyAdmin);
            yamu_redirect_with_message('../dashboard.php', 'msg', 'Signin Successful');
        }
    }

    yamu_redirect_with_message('../index.php', 'error', 'Invalid email or password');
}
