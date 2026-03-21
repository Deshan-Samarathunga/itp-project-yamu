<?php

function carzo_start_session()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function carzo_append_query_value($path, $key, $value)
{
    $separator = strpos($path, '?') === false ? '?' : '&';
    return $path . $separator . $key . '=' . urlencode($value);
}

function carzo_redirect($path)
{
    header('Location: ' . $path);
    exit();
}

function carzo_redirect_with_message($path, $type, $message)
{
    carzo_redirect(carzo_append_query_value($path, $type, $message));
}

function carzo_hash_password($password)
{
    return md5($password);
}

function carzo_password_matches($plainPassword, $storedPassword)
{
    if ($storedPassword === null || $storedPassword === '') {
        return false;
    }

    if (strpos($storedPassword, '$2y$') === 0 || strpos($storedPassword, '$argon2') === 0) {
        return password_verify($plainPassword, $storedPassword);
    }

    return hash_equals(strtolower($storedPassword), carzo_hash_password($plainPassword));
}

function carzo_escape($conn, $value)
{
    return mysqli_real_escape_string($conn, trim((string) $value));
}

function carzo_e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function carzo_money($amount)
{
    return number_format((float) $amount, 2);
}

function carzo_normalize_role($role)
{
    $role = strtolower(trim((string) $role));
    $allowedRoles = ['admin', 'staff', 'driver', 'customer'];

    return in_array($role, $allowedRoles, true) ? $role : 'customer';
}

function carzo_is_admin_panel_role($role)
{
    return in_array(carzo_normalize_role($role), ['admin', 'staff'], true);
}

function carzo_normalize_account_status($status, $role = 'customer')
{
    $status = strtolower(trim((string) $status));
    $allowedStatuses = ['active', 'pending', 'suspended'];

    if (!in_array($status, $allowedStatuses, true)) {
        return $role === 'driver' ? 'pending' : 'active';
    }

    return $status;
}

function carzo_normalize_verification_status($status, $role = 'customer')
{
    $status = strtolower(trim((string) $status));
    $allowedStatuses = ['unverified', 'pending', 'approved', 'rejected', 'verified'];

    if (!in_array($status, $allowedStatuses, true)) {
        return $role === 'driver' ? 'pending' : 'verified';
    }

    return $status;
}

function carzo_build_user_session(array $row)
{
    return [
        'user_ID' => $row['user_id'],
        'username' => $row['username'],
        'password' => $row['password'],
        'email' => $row['email'],
        'name' => $row['full_name'],
        'address' => $row['address'] ?? '',
        'city' => $row['city'] ?? '',
        'phone' => $row['phone'] ?? '',
        'dob' => $row['dob'] ?? '',
        'avatar' => !empty($row['profile_pic']) ? $row['profile_pic'] : 'avatar.png',
        'role' => carzo_normalize_role($row['role'] ?? 'customer'),
        'account_status' => carzo_normalize_account_status($row['account_status'] ?? 'active', $row['role'] ?? 'customer'),
        'license_or_nic' => $row['license_or_nic'] ?? '',
        'verification_status' => carzo_normalize_verification_status($row['verification_status'] ?? 'verified', $row['role'] ?? 'customer'),
        'bio' => $row['bio'] ?? '',
        'created_at' => $row['created_at'] ?? ($row['rag_date'] ?? ''),
        'updated_at' => $row['updated_at'] ?? '',
    ];
}

function carzo_build_admin_session_from_user(array $row)
{
    $role = carzo_normalize_role($row['role'] ?? 'admin');

    return [
        'admin_id' => $row['user_id'],
        'user_id' => $row['user_id'],
        'username' => $row['username'],
        'password' => $row['password'],
        'email' => $row['email'],
        'name' => $row['full_name'],
        'address' => $row['address'] ?? '',
        'city' => $row['city'] ?? '',
        'phone' => $row['phone'] ?? '',
        'avatar' => !empty($row['profile_pic']) ? $row['profile_pic'] : 'avatar.png',
        'role' => $role,
        'account_status' => carzo_normalize_account_status($row['account_status'] ?? 'active', $role),
        'verification_status' => carzo_normalize_verification_status($row['verification_status'] ?? 'verified', $role),
    ];
}

function carzo_build_admin_session_from_legacy_admin(array $row)
{
    return [
        'admin_id' => $row['admin_id'],
        'username' => $row['username'],
        'password' => $row['password'],
        'email' => $row['email'],
        'name' => $row['name'],
        'address' => $row['address'] ?? '',
        'city' => $row['city'] ?? '',
        'phone' => $row['phone'] ?? '',
        'avatar' => !empty($row['profile_pic']) ? $row['profile_pic'] : 'avatar.png',
        'role' => 'admin',
        'account_status' => 'active',
        'verification_status' => 'verified',
    ];
}

function carzo_set_user_session(array $row)
{
    $_SESSION['user'] = carzo_build_user_session($row);
    $_SESSION['authenticated'] = true;
}

function carzo_set_admin_session_from_user(array $row)
{
    $_SESSION['admin'] = carzo_build_admin_session_from_user($row);
}

function carzo_set_admin_session_from_legacy_admin(array $row)
{
    $_SESSION['admin'] = carzo_build_admin_session_from_legacy_admin($row);
}

function carzo_is_user_authenticated()
{
    return isset($_SESSION['authenticated'], $_SESSION['user']) && $_SESSION['authenticated'] === true;
}

function carzo_is_admin_authenticated()
{
    return isset($_SESSION['admin']) && carzo_is_admin_panel_role($_SESSION['admin']['role'] ?? null);
}

function carzo_current_user()
{
    return $_SESSION['user'] ?? null;
}

function carzo_current_user_role()
{
    return $_SESSION['user']['role'] ?? null;
}

function carzo_public_home_path_for_role($role)
{
    $role = carzo_normalize_role($role);

    if (carzo_is_admin_panel_role($role)) {
        return 'admin/dashboard.php';
    }

    if ($role === 'driver') {
        return 'driver-dashboard.php';
    }

    return 'index.php';
}

function carzo_current_public_home_path()
{
    if (carzo_is_admin_authenticated()) {
        return 'admin/dashboard.php';
    }

    if (carzo_is_user_authenticated()) {
        return carzo_public_home_path_for_role(carzo_current_user_role());
    }

    return 'index.php';
}

function carzo_redirect_authenticated_actor()
{
    if (carzo_is_admin_authenticated() || carzo_is_user_authenticated()) {
        carzo_redirect(carzo_current_public_home_path());
    }
}

function carzo_logout_current_session()
{
    carzo_start_session();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function carzo_has_user_role($roles)
{
    $roles = (array) $roles;
    return in_array(carzo_current_user_role(), $roles, true);
}

function carzo_require_user_roles($roles, $redirect = 'signin.php', $allowedStatuses = ['active', 'pending'], $forbiddenRedirect = 'index.php')
{
    carzo_start_session();

    if (!carzo_is_user_authenticated()) {
        carzo_redirect_with_message($redirect, 'error', 'Please sign in to continue');
    }

    if (!carzo_has_user_role($roles)) {
        carzo_redirect_with_message($forbiddenRedirect, 'error', 'You do not have permission to access that page');
    }

    $accountStatus = $_SESSION['user']['account_status'] ?? 'active';

    if (!in_array($accountStatus, (array) $allowedStatuses, true)) {
        carzo_redirect_with_message($forbiddenRedirect, 'error', 'Your account is currently unavailable');
    }
}

function carzo_require_admin($redirect = 'index.php')
{
    carzo_start_session();

    if (!carzo_is_admin_authenticated()) {
        carzo_redirect_with_message($redirect, 'error', 'Please sign in as admin to continue');
    }
}

function carzo_badge_class($status)
{
    $status = strtolower(trim((string) $status));

    if (in_array($status, ['active', 'approved', 'verified', 'confirmed', 'completed', 'paid', 'available', 'good', 'visible', 'resolved'], true)) {
        return 'Status-conpleted-badge';
    }

    if (in_array($status, ['pending', 'under_review', 'unverified', 'booked', 'due soon', 'open', 'flagged', 'busy', 'on_request'], true)) {
        return 'Status-pending-badge';
    }

    return 'Status-inactive-badge';
}

function carzo_fetch_user_by_email($conn, $email)
{
    $stmt = $conn->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $row ?: null;
}

function carzo_fetch_user_by_id($conn, $userId)
{
    $stmt = $conn->prepare('SELECT * FROM users WHERE user_id = ? LIMIT 1');

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $row ?: null;
}

function carzo_profile_avatar_path($fileName)
{
    $fileName = !empty($fileName) ? $fileName : 'avatar.png';
    return 'assets/images/uploads/avatar/' . $fileName;
}
