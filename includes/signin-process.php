<?php
require_once __DIR__ . '/auth.php';
yamu_start_session();
include 'config.php';

if (isset($_POST['signin'])) {
    yamu_ensure_users_password_column($conn);

    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        yamu_redirect_with_message('../signin.php', 'error', 'Email and password are required');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        yamu_redirect_with_message('../signin.php', 'error', 'Please enter a valid email address');
    }

    $user = yamu_fetch_user_by_email($conn, $email);

    if ($user && yamu_password_appears_truncated($user['password'] ?? '')) {
        yamu_redirect_with_message('../forgot-password.php', 'error', 'Your password record was created before a schema fix. Please reset your password and try again.');
    }

    if (!$user || !yamu_password_matches($password, $user['password'])) {
        yamu_redirect_with_message('../signin.php', 'error', 'Invalid email or password');
    }

    yamu_upgrade_password_hash_if_needed($conn, (int) $user['user_id'], $password, $user['password']);
    yamu_touch_user_last_login($conn, (int) $user['user_id']);
    $user = yamu_fetch_user_by_id($conn, (int) $user['user_id']) ?: $user;

    $sessionUser = yamu_set_user_session($user, $conn);
    $roleAssignments = $sessionUser['role_assignments'] ?? [];
    $allowedRoleCount = 0;
    $switchableRoleCount = 0;

    foreach ($roleAssignments as $assignment) {
        $roleStatus = $assignment['role_status'] ?? 'active';

        if (!yamu_is_role_blocked($roleStatus)) {
            $allowedRoleCount++;
        }

        if (yamu_role_allows_onboarding_status($roleStatus)) {
            $switchableRoleCount++;
        }
    }

    if ($allowedRoleCount === 0) {
        yamu_logout_current_session();
        yamu_redirect_with_message('../signin.php', 'error', 'Your account is currently unavailable. Please contact support.');
    }

    $activeRole = $sessionUser['active_role'] ?? yamu_normalize_role($user['role'] ?? 'customer');
    $activeStatus = yamu_current_user_role_status($activeRole);

    if (yamu_is_role_blocked($activeStatus)) {
        yamu_redirect_with_message('../choose-role.php', 'error', 'Your current role is unavailable. Please switch to another role.');
    }

    if ($switchableRoleCount > 1) {
        yamu_redirect_with_message('../choose-role.php', 'msg', 'Select an active role to continue');
    }

    $redirectPath = yamu_public_home_path_for_role($activeRole);
    yamu_redirect_with_message('../' . $redirectPath, 'msg', 'Signin Successful');
}
