<?php
require_once __DIR__ . '/auth.php';
carzo_start_session();
include 'config.php';

if (isset($_POST['signin'])) {
    carzo_ensure_users_password_column($conn);

    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        carzo_redirect_with_message('../signin.php', 'error', 'Email and password are required');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        carzo_redirect_with_message('../signin.php', 'error', 'Please enter a valid email address');
    }

    $user = carzo_fetch_user_by_email($conn, $email);

    if ($user && carzo_password_appears_truncated($user['password'] ?? '')) {
        carzo_redirect_with_message('../forgot-password.php', 'error', 'Your password record was created before a schema fix. Please reset your password and try again.');
    }

    if (!$user || !carzo_password_matches($password, $user['password'])) {
        carzo_redirect_with_message('../signin.php', 'error', 'Invalid email or password');
    }

    carzo_upgrade_password_hash_if_needed($conn, (int) $user['user_id'], $password, $user['password']);
    carzo_touch_user_last_login($conn, (int) $user['user_id']);
    $user = carzo_fetch_user_by_id($conn, (int) $user['user_id']) ?: $user;

    $sessionUser = carzo_set_user_session($user, $conn);
    $roleAssignments = $sessionUser['role_assignments'] ?? [];
    $allowedRoleCount = 0;

    foreach ($roleAssignments as $assignment) {
        if (!carzo_is_role_blocked($assignment['role_status'] ?? 'active')) {
            $allowedRoleCount++;
        }
    }

    if ($allowedRoleCount === 0) {
        carzo_logout_current_session();
        carzo_redirect_with_message('../signin.php', 'error', 'Your account is currently unavailable. Please contact support.');
    }

    $activeRole = $sessionUser['active_role'] ?? carzo_normalize_role($user['role'] ?? 'customer');
    $activeStatus = carzo_current_user_role_status($activeRole);

    if (carzo_is_role_blocked($activeStatus)) {
        carzo_redirect_with_message('../choose-role.php', 'error', 'Your current role is unavailable. Please switch to another role.');
    }

    if (!empty($sessionUser['roles']) && count((array) $sessionUser['roles']) > 1) {
        carzo_redirect_with_message('../choose-role.php', 'msg', 'Select an active role to continue');
    }

    $redirectPath = carzo_public_home_path_for_role($activeRole);
    carzo_redirect_with_message('../' . $redirectPath, 'msg', 'Signin Successful');
}
