<?php

function yamu_start_session()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function yamu_append_query_value($path, $key, $value)
{
    $separator = strpos($path, '?') === false ? '?' : '&';
    return $path . $separator . $key . '=' . urlencode($value);
}

function yamu_redirect($path)
{
    header('Location: ' . $path);
    exit();
}

function yamu_redirect_with_message($path, $type, $message)
{
    yamu_redirect(yamu_append_query_value($path, $type, $message));
}

function yamu_hash_password($password)
{
    return password_hash((string) $password, PASSWORD_DEFAULT);
}

function yamu_password_matches($plainPassword, $storedPassword)
{
    if ($storedPassword === null || $storedPassword === '') {
        return false;
    }

    if (strpos($storedPassword, '$2y$') === 0 || strpos($storedPassword, '$argon2') === 0) {
        return password_verify((string) $plainPassword, (string) $storedPassword);
    }

    return hash_equals(strtolower((string) $storedPassword), md5((string) $plainPassword));
}

function yamu_password_needs_rehash_upgrade($storedPassword)
{
    if ($storedPassword === null || $storedPassword === '') {
        return true;
    }

    if (strpos($storedPassword, '$2y$') === 0 || strpos($storedPassword, '$argon2') === 0) {
        return password_needs_rehash((string) $storedPassword, PASSWORD_DEFAULT);
    }

    return true;
}

function yamu_password_appears_truncated($storedPassword)
{
    $storedPassword = (string) $storedPassword;

    if ($storedPassword === '') {
        return false;
    }

    if (strpos($storedPassword, '$2y$') === 0 && strlen($storedPassword) < 60) {
        return true;
    }

    if (strpos($storedPassword, '$argon2') === 0 && strlen($storedPassword) < 90) {
        return true;
    }

    return false;
}

function yamu_escape($conn, $value)
{
    return mysqli_real_escape_string($conn, trim((string) $value));
}

function yamu_e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function yamu_money($amount)
{
    return number_format((float) $amount, 2);
}

function yamu_normalize_role($role)
{
    $role = strtolower(trim((string) $role));
    $allowedRoles = ['admin', 'staff', 'driver', 'customer'];

    return in_array($role, $allowedRoles, true) ? $role : 'customer';
}

function yamu_role_label($role)
{
    return ucfirst(yamu_normalize_role($role));
}

function yamu_default_account_status_for_role($role)
{
    $role = yamu_normalize_role($role);
    return in_array($role, ['driver', 'staff'], true) ? 'pending' : 'active';
}

function yamu_default_verification_status_for_role($role)
{
    $role = yamu_normalize_role($role);
    return in_array($role, ['driver', 'staff'], true) ? 'pending' : 'verified';
}

function yamu_is_admin_role($role)
{
    return yamu_normalize_role($role) === 'admin';
}

function yamu_is_admin_panel_role($role)
{
    return yamu_is_admin_role($role);
}

function yamu_normalize_account_status($status, $role = 'customer')
{
    $status = strtolower(trim((string) $status));
    $allowedStatuses = ['active', 'pending', 'verified', 'suspended', 'rejected', 'deactivated'];

    if (!in_array($status, $allowedStatuses, true)) {
        return yamu_default_account_status_for_role($role);
    }

    return $status;
}

function yamu_normalize_role_status($status, $role = 'customer')
{
    return yamu_normalize_account_status($status, $role);
}

function yamu_normalize_verification_status($status, $role = 'customer')
{
    $status = strtolower(trim((string) $status));
    $allowedStatuses = ['unverified', 'pending', 'approved', 'rejected', 'verified'];

    if (!in_array($status, $allowedStatuses, true)) {
        return yamu_default_verification_status_for_role($role);
    }

    return $status;
}

function yamu_is_role_pending($status)
{
    return yamu_normalize_role_status($status) === 'pending';
}

function yamu_role_allows_onboarding_status($status)
{
    return yamu_can_access_role_status($status, ['active', 'pending', 'verified']);
}

function yamu_role_allows_standard_status($status)
{
    return yamu_can_access_role_status($status, ['active', 'verified']);
}

function yamu_resolve_role_status_from_verification($role, $verificationStatus, $fallbackStatus = null)
{
    $role = yamu_normalize_role($role);
    $verificationStatus = yamu_normalize_verification_status($verificationStatus, $role);

    if ($fallbackStatus !== null) {
        $normalizedFallback = yamu_normalize_role_status($fallbackStatus, $role);

        if (yamu_is_role_blocked($normalizedFallback)) {
            return $normalizedFallback;
        }
    }

    if ($verificationStatus === 'rejected') {
        return 'rejected';
    }

    if (in_array($role, ['driver', 'staff'], true) && in_array($verificationStatus, ['pending', 'unverified'], true)) {
        return 'pending';
    }

    if ($verificationStatus === 'approved' || $verificationStatus === 'verified') {
        return 'active';
    }

    return $fallbackStatus !== null
        ? yamu_normalize_role_status($fallbackStatus, $role)
        : yamu_default_account_status_for_role($role);
}

function yamu_table_exists($conn, $tableName)
{
    static $cache = [];

    if (!$conn) {
        return false;
    }

    $key = spl_object_hash($conn) . ':' . $tableName;

    if (isset($cache[$key])) {
        return $cache[$key];
    }

    $escapedTable = mysqli_real_escape_string($conn, (string) $tableName);
    $result = @mysqli_query($conn, "SHOW TABLES LIKE '{$escapedTable}'");
    $exists = $result instanceof mysqli_result && $result->num_rows > 0;

    if ($result instanceof mysqli_result) {
        $result->free();
    }

    $cache[$key] = $exists;
    return $exists;
}

function yamu_table_has_column($conn, $tableName, $columnName)
{
    static $cache = [];

    if (!$conn || !yamu_table_exists($conn, $tableName)) {
        return false;
    }

    $key = spl_object_hash($conn) . ':' . $tableName . ':' . $columnName;

    if (isset($cache[$key])) {
        return $cache[$key];
    }

    $escapedTable = mysqli_real_escape_string($conn, (string) $tableName);
    $escapedColumn = mysqli_real_escape_string($conn, (string) $columnName);
    $result = @mysqli_query($conn, "SHOW COLUMNS FROM `{$escapedTable}` LIKE '{$escapedColumn}'");
    $exists = $result instanceof mysqli_result && $result->num_rows > 0;

    if ($result instanceof mysqli_result) {
        $result->free();
    }

    $cache[$key] = $exists;
    return $exists;
}

function yamu_fetch_available_roles($conn = null)
{
    $defaults = [
        ['role_key' => 'customer', 'role_name' => 'Customer'],
        ['role_key' => 'driver', 'role_name' => 'Driver'],
        ['role_key' => 'staff', 'role_name' => 'Staff'],
        ['role_key' => 'admin', 'role_name' => 'Admin'],
    ];

    if (!$conn || !yamu_table_exists($conn, 'roles')) {
        return $defaults;
    }

    $result = $conn->query("SELECT role_key, role_name FROM roles ORDER BY FIELD(role_key, 'customer', 'driver', 'staff', 'admin')");

    if (!$result) {
        return $defaults;
    }

    $roles = [];

    while ($row = $result->fetch_assoc()) {
        $roles[] = [
            'role_key' => yamu_normalize_role($row['role_key'] ?? 'customer'),
            'role_name' => trim((string) ($row['role_name'] ?? yamu_role_label($row['role_key'] ?? 'customer'))),
        ];
    }

    $result->free();

    return !empty($roles) ? $roles : $defaults;
}

function yamu_fetch_user_roles($conn, $userId, $fallbackRole = 'customer', $fallbackAccountStatus = 'active', $fallbackVerificationStatus = 'verified')
{
    $assignments = [];
    $userId = (int) $userId;

    if ($conn && $userId > 0 && yamu_table_exists($conn, 'user_roles')) {
        $stmt = $conn->prepare(
            'SELECT role_key, role_status, verification_status, is_primary
             FROM user_roles
             WHERE user_id = ?
             ORDER BY is_primary DESC, created_at ASC'
        );

        if ($stmt) {
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $role = yamu_normalize_role($row['role_key'] ?? 'customer');
                    $assignments[$role] = [
                        'role' => $role,
                        'role_status' => yamu_normalize_role_status($row['role_status'] ?? yamu_default_account_status_for_role($role), $role),
                        'verification_status' => yamu_normalize_verification_status($row['verification_status'] ?? yamu_default_verification_status_for_role($role), $role),
                        'is_primary' => (int) ($row['is_primary'] ?? 0) === 1,
                    ];
                }
            }

            $stmt->close();
        }
    }

    if (!empty($assignments)) {
        return $assignments;
    }

    $role = yamu_normalize_role($fallbackRole);
    $assignments[$role] = [
        'role' => $role,
        'role_status' => yamu_normalize_role_status($fallbackAccountStatus, $role),
        'verification_status' => yamu_normalize_verification_status($fallbackVerificationStatus, $role),
        'is_primary' => true,
    ];

    return $assignments;
}

function yamu_get_user_role_assignment($roleAssignments, $role)
{
    $role = yamu_normalize_role($role);
    $roleAssignments = is_array($roleAssignments) ? $roleAssignments : [];
    return $roleAssignments[$role] ?? null;
}

function yamu_pick_active_role(array $roleAssignments, $preferredRole = null)
{
    $preferredRole = yamu_normalize_role($preferredRole);

    if ($preferredRole && isset($roleAssignments[$preferredRole])) {
        $preferredStatus = yamu_normalize_role_status($roleAssignments[$preferredRole]['role_status'] ?? 'active', $preferredRole);
        if (!yamu_is_role_blocked($preferredStatus)) {
            return $preferredRole;
        }
    }

    foreach ($roleAssignments as $assignmentRole => $assignment) {
        $status = yamu_normalize_role_status($assignment['role_status'] ?? 'active', $assignmentRole);
        if (!empty($assignment['is_primary']) && yamu_role_allows_standard_status($status)) {
            return yamu_normalize_role($assignmentRole);
        }
    }

    foreach ($roleAssignments as $assignmentRole => $assignment) {
        $status = yamu_normalize_role_status($assignment['role_status'] ?? 'active', $assignmentRole);
        if (yamu_role_allows_standard_status($status)) {
            return yamu_normalize_role($assignmentRole);
        }
    }

    foreach ($roleAssignments as $assignmentRole => $assignment) {
        $status = yamu_normalize_role_status($assignment['role_status'] ?? 'active', $assignmentRole);
        if (!empty($assignment['is_primary']) && !yamu_is_role_blocked($status)) {
            return yamu_normalize_role($assignmentRole);
        }
    }

    foreach ($roleAssignments as $assignmentRole => $assignment) {
        $status = yamu_normalize_role_status($assignment['role_status'] ?? 'active', $assignmentRole);
        if (!yamu_is_role_blocked($status)) {
            return yamu_normalize_role($assignmentRole);
        }
    }

    foreach ($roleAssignments as $assignmentRole => $assignment) {
        return yamu_normalize_role($assignmentRole);
    }

    return 'customer';
}

function yamu_build_user_session(array $row, array $roleAssignments = [], $preferredRole = null)
{
    if (empty($roleAssignments)) {
        $fallbackRole = yamu_normalize_role($row['role'] ?? 'customer');
        $roleAssignments = [
            $fallbackRole => [
                'role' => $fallbackRole,
                'role_status' => yamu_normalize_role_status($row['account_status'] ?? yamu_default_account_status_for_role($fallbackRole), $fallbackRole),
                'verification_status' => yamu_normalize_verification_status($row['verification_status'] ?? yamu_default_verification_status_for_role($fallbackRole), $fallbackRole),
                'is_primary' => true,
            ],
        ];
    }

    $activeRole = yamu_pick_active_role($roleAssignments, $preferredRole);
    $activeAssignment = $roleAssignments[$activeRole] ?? null;
    $activeAccountStatus = yamu_normalize_account_status($activeAssignment['role_status'] ?? ($row['account_status'] ?? yamu_default_account_status_for_role($activeRole)), $activeRole);
    $activeVerificationStatus = yamu_normalize_verification_status($activeAssignment['verification_status'] ?? ($row['verification_status'] ?? yamu_default_verification_status_for_role($activeRole)), $activeRole);

    return [
        'user_ID' => (int) ($row['user_id'] ?? 0),
        'username' => (string) ($row['username'] ?? ''),
        'password' => (string) ($row['password'] ?? ''),
        'email' => (string) ($row['email'] ?? ''),
        'name' => (string) ($row['full_name'] ?? ''),
        'address' => $row['address'] ?? '',
        'city' => $row['city'] ?? '',
        'phone' => $row['phone'] ?? '',
        'dob' => $row['dob'] ?? '',
        'avatar' => !empty($row['profile_pic']) ? $row['profile_pic'] : 'avatar.png',
        'primary_role' => yamu_normalize_role($row['role'] ?? $activeRole),
        'active_role' => $activeRole,
        'role' => $activeRole,
        'roles' => array_keys($roleAssignments),
        'role_assignments' => $roleAssignments,
        'account_status' => $activeAccountStatus,
        'license_or_nic' => $row['license_or_nic'] ?? '',
        'verification_status' => $activeVerificationStatus,
        'bio' => $row['bio'] ?? '',
        'created_at' => $row['created_at'] ?? ($row['rag_date'] ?? ''),
        'updated_at' => $row['updated_at'] ?? '',
        'last_login_at' => $row['last_login_at'] ?? '',
    ];
}

function yamu_build_admin_session_from_user(array $row, $activeRole = null, $accountStatus = null, $verificationStatus = null)
{
    $role = yamu_normalize_role($activeRole ?? ($row['role'] ?? 'admin'));

    if (!yamu_is_admin_role($role)) {
        return null;
    }

    return [
        'admin_id' => (int) ($row['user_id'] ?? 0),
        'user_id' => (int) ($row['user_id'] ?? 0),
        'username' => (string) ($row['username'] ?? ''),
        'password' => (string) ($row['password'] ?? ''),
        'email' => (string) ($row['email'] ?? ''),
        'name' => (string) ($row['full_name'] ?? ''),
        'address' => $row['address'] ?? '',
        'city' => $row['city'] ?? '',
        'phone' => $row['phone'] ?? '',
        'avatar' => !empty($row['profile_pic']) ? $row['profile_pic'] : 'avatar.png',
        'role' => $role,
        'account_status' => yamu_normalize_account_status($accountStatus ?? ($row['account_status'] ?? yamu_default_account_status_for_role($role)), $role),
        'verification_status' => yamu_normalize_verification_status($verificationStatus ?? ($row['verification_status'] ?? yamu_default_verification_status_for_role($role)), $role),
    ];
}

function yamu_build_admin_session_from_legacy_admin(array $row)
{
    return [
        'admin_id' => (int) ($row['admin_id'] ?? 0),
        'username' => (string) ($row['username'] ?? ''),
        'password' => (string) ($row['password'] ?? ''),
        'email' => (string) ($row['email'] ?? ''),
        'name' => (string) ($row['name'] ?? ''),
        'address' => $row['address'] ?? '',
        'city' => $row['city'] ?? '',
        'phone' => $row['phone'] ?? '',
        'avatar' => !empty($row['profile_pic']) ? $row['profile_pic'] : 'avatar.png',
        'role' => 'admin',
        'account_status' => 'active',
        'verification_status' => 'verified',
    ];
}

function yamu_set_user_session(array $row, $conn = null, $preferredRole = null)
{
    $roleAssignments = yamu_fetch_user_roles(
        $conn,
        (int) ($row['user_id'] ?? 0),
        $row['role'] ?? 'customer',
        $row['account_status'] ?? yamu_default_account_status_for_role($row['role'] ?? 'customer'),
        $row['verification_status'] ?? yamu_default_verification_status_for_role($row['role'] ?? 'customer')
    );

    $sessionUser = yamu_build_user_session($row, $roleAssignments, $preferredRole);
    $_SESSION['user'] = $sessionUser;
    $_SESSION['authenticated'] = true;

    if (yamu_is_admin_role($sessionUser['active_role']) && yamu_role_allows_standard_status($sessionUser['account_status'])) {
        $adminSession = yamu_build_admin_session_from_user(
            $row,
            $sessionUser['active_role'],
            $sessionUser['account_status'],
            $sessionUser['verification_status']
        );

        if ($adminSession) {
            $_SESSION['admin'] = $adminSession;
        } else {
            unset($_SESSION['admin']);
        }
    } else {
        unset($_SESSION['admin']);
    }

    return $sessionUser;
}

function yamu_refresh_user_session($conn, $userId, $preferredRole = null)
{
    $user = yamu_fetch_user_by_id($conn, $userId);

    if (!$user) {
        return null;
    }

    return yamu_set_user_session($user, $conn, $preferredRole);
}

function yamu_set_admin_session_from_user(array $row, $activeRole = null)
{
    $adminSession = yamu_build_admin_session_from_user($row, $activeRole);

    if ($adminSession && yamu_role_allows_standard_status($adminSession['account_status'] ?? 'active')) {
        $_SESSION['admin'] = $adminSession;
        return;
    }

    unset($_SESSION['admin']);
}

function yamu_set_admin_session_from_legacy_admin(array $row)
{
    $_SESSION['admin'] = yamu_build_admin_session_from_legacy_admin($row);
}

function yamu_is_user_authenticated()
{
    return isset($_SESSION['authenticated'], $_SESSION['user']) && $_SESSION['authenticated'] === true;
}

function yamu_is_admin_authenticated()
{
    return isset($_SESSION['admin']) && yamu_is_admin_panel_role($_SESSION['admin']['role'] ?? null);
}

function yamu_current_user()
{
    return $_SESSION['user'] ?? null;
}

function yamu_current_user_role()
{
    return $_SESSION['user']['active_role'] ?? ($_SESSION['user']['role'] ?? null);
}

function yamu_current_user_roles()
{
    $roles = $_SESSION['user']['roles'] ?? [];
    return is_array($roles) ? array_values($roles) : [];
}

function yamu_current_user_role_assignments()
{
    $assignments = $_SESSION['user']['role_assignments'] ?? [];
    return is_array($assignments) ? $assignments : [];
}

function yamu_current_user_has_assigned_role($role)
{
    $role = yamu_normalize_role($role);
    $assignments = yamu_current_user_role_assignments();
    return isset($assignments[$role]);
}

function yamu_current_user_role_status($role = null)
{
    $role = $role ? yamu_normalize_role($role) : yamu_current_user_role();
    $assignments = yamu_current_user_role_assignments();
    $assignment = $assignments[$role] ?? null;

    if (!$assignment) {
        return yamu_normalize_account_status($_SESSION['user']['account_status'] ?? 'active', $role ?: 'customer');
    }

    return yamu_normalize_role_status($assignment['role_status'] ?? 'active', $role);
}

function yamu_can_access_role_status($status, $allowedStatuses = ['active', 'pending', 'verified'])
{
    $status = yamu_normalize_role_status($status);
    return in_array($status, (array) $allowedStatuses, true);
}

function yamu_is_role_blocked($status)
{
    $status = yamu_normalize_role_status($status);
    return in_array($status, ['suspended', 'rejected', 'deactivated'], true);
}

function yamu_can_switch_to_role($role)
{
    $role = yamu_normalize_role($role);
    $assignments = yamu_current_user_role_assignments();
    $assignment = $assignments[$role] ?? null;

    if (!$assignment) {
        return false;
    }

    return !yamu_is_role_blocked($assignment['role_status'] ?? 'active');
}

function yamu_switch_active_role($conn, $role, &$errorMessage = null)
{
    yamu_start_session();

    if (!yamu_is_user_authenticated()) {
        $errorMessage = 'Please sign in to switch roles';
        return false;
    }

    $role = yamu_normalize_role($role);
    $currentUser = yamu_current_user();
    $userId = (int) ($currentUser['user_ID'] ?? 0);

    if ($userId <= 0) {
        $errorMessage = 'Invalid session state';
        return false;
    }

    $assignments = yamu_fetch_user_roles(
        $conn,
        $userId,
        $currentUser['primary_role'] ?? $currentUser['role'] ?? 'customer',
        $currentUser['account_status'] ?? 'active',
        $currentUser['verification_status'] ?? 'verified'
    );

    $targetAssignment = $assignments[$role] ?? null;

    if (!$targetAssignment) {
        $errorMessage = 'Selected role is not assigned to your account';
        return false;
    }

    if (yamu_is_role_blocked($targetAssignment['role_status'] ?? 'active')) {
        $errorMessage = 'Selected role is currently unavailable';
        return false;
    }

    $userRow = yamu_fetch_user_by_id($conn, $userId);

    if (!$userRow) {
        $errorMessage = 'User account not found';
        return false;
    }

    yamu_set_user_session($userRow, $conn, $role);
    return true;
}

function yamu_public_home_path_for_role($role)
{
    $role = yamu_normalize_role($role);

    if (yamu_is_admin_panel_role($role)) {
        return 'admin/dashboard.php';
    }

    if ($role === 'driver') {
        return 'driver-dashboard.php';
    }

    if ($role === 'staff') {
        return 'staff-dashboard.php';
    }

    return 'index.php';
}

function yamu_current_public_home_path()
{
    if (yamu_is_user_authenticated()) {
        return yamu_public_home_path_for_role(yamu_current_user_role());
    }

    if (yamu_is_admin_authenticated()) {
        return 'admin/dashboard.php';
    }

    return 'index.php';
}

function yamu_redirect_authenticated_actor()
{
    if (yamu_is_admin_authenticated() || yamu_is_user_authenticated()) {
        yamu_redirect(yamu_current_public_home_path());
    }
}

function yamu_logout_current_session()
{
    yamu_start_session();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function yamu_has_user_role($roles)
{
    $roles = array_map('yamu_normalize_role', (array) $roles);
    return in_array(yamu_current_user_role(), $roles, true);
}

function yamu_has_any_assigned_role($roles)
{
    $roles = array_map('yamu_normalize_role', (array) $roles);
    $assignedRoles = yamu_current_user_roles();

    foreach ($roles as $role) {
        if (in_array($role, $assignedRoles, true)) {
            return true;
        }
    }

    return false;
}

function yamu_require_user_roles($roles, $redirect = 'signin.php', $allowedStatuses = ['active', 'pending', 'verified'], $forbiddenRedirect = 'access-denied.php')
{
    yamu_require_active_user_role($roles, $redirect, $allowedStatuses, $forbiddenRedirect);
}

function yamu_require_authenticated_user($redirect = 'signin.php')
{
    yamu_start_session();

    if (!yamu_is_user_authenticated()) {
        yamu_redirect_with_message($redirect, 'error', 'Please sign in to continue');
    }
}

function yamu_require_active_user_role($roles, $redirect = 'signin.php', $allowedStatuses = ['active', 'pending', 'verified'], $forbiddenRedirect = 'access-denied.php')
{
    yamu_require_authenticated_user($redirect);

    if (!yamu_has_user_role($roles)) {
        yamu_redirect_with_message($forbiddenRedirect, 'error', 'You do not have permission to access that page');
    }

    $accountStatus = yamu_current_user_role_status();

    if (!yamu_can_access_role_status($accountStatus, $allowedStatuses)) {
        yamu_redirect_with_message($forbiddenRedirect, 'error', 'Your selected role is currently unavailable');
    }
}

function yamu_require_any_assigned_user_role($roles, $redirect = 'signin.php', $forbiddenRedirect = 'access-denied.php')
{
    yamu_require_assigned_user_role($roles, $redirect, ['active', 'pending', 'verified'], $forbiddenRedirect);
}

function yamu_require_assigned_user_role($roles, $redirect = 'signin.php', $allowedStatuses = ['active', 'pending', 'verified'], $forbiddenRedirect = 'access-denied.php')
{
    yamu_require_authenticated_user($redirect);

    if (!yamu_has_any_assigned_role($roles)) {
        yamu_redirect_with_message($forbiddenRedirect, 'error', 'You do not have permission to access that page');
    }

    $roles = array_map('yamu_normalize_role', (array) $roles);

    foreach ($roles as $role) {
        if (!yamu_current_user_has_assigned_role($role)) {
            continue;
        }

        $status = yamu_current_user_role_status($role);

        if (yamu_can_access_role_status($status, $allowedStatuses)) {
            return;
        }
    }

    yamu_redirect_with_message($forbiddenRedirect, 'error', 'Your assigned role is currently unavailable');
}

function yamu_require_admin($redirect = 'index.php', $forbiddenRedirect = 'access-denied.php')
{
    yamu_start_session();

    if (!yamu_is_admin_authenticated()) {
        if (yamu_is_user_authenticated()) {
            yamu_redirect_with_message($forbiddenRedirect, 'error', 'You do not have permission to access that page');
        }

        yamu_redirect_with_message($redirect, 'error', 'Please sign in as admin to continue');
    }

    $adminStatus = yamu_normalize_account_status($_SESSION['admin']['account_status'] ?? 'active', $_SESSION['admin']['role'] ?? 'admin');
    if (!yamu_role_allows_standard_status($adminStatus)) {
        yamu_redirect_with_message($redirect, 'error', 'Your admin role is currently unavailable');
    }
}

function yamu_badge_class($status)
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

function yamu_fetch_user_by_email($conn, $email)
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

function yamu_fetch_user_by_id($conn, $userId)
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

function yamu_touch_user_last_login($conn, $userId)
{
    if (!$conn || (int) $userId <= 0) {
        return;
    }

    if (!yamu_ensure_users_last_login_column($conn)) {
        return;
    }

    $stmt = $conn->prepare('UPDATE users SET last_login_at = NOW(), updated_at = NOW() WHERE user_id = ?');

    if (!$stmt) {
        return;
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->close();
}

function yamu_upgrade_password_hash_if_needed($conn, $userId, $plainPassword, $storedPassword)
{
    if (!yamu_password_needs_rehash_upgrade($storedPassword)) {
        return;
    }

    $newHash = yamu_hash_password($plainPassword);
    $stmt = $conn->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE user_id = ?');

    if (!$stmt) {
        return;
    }

    $stmt->bind_param('si', $newHash, $userId);
    $stmt->execute();
    $stmt->close();
}

function yamu_upsert_user_role_assignment($conn, $userId, $role, $roleStatus = null, $verificationStatus = null, $isPrimary = false, $assignedByUserId = null, $notes = null)
{
    if (!$conn || !yamu_table_exists($conn, 'user_roles')) {
        return false;
    }

    $userId = (int) $userId;
    $role = yamu_normalize_role($role);
    $roleStatus = yamu_normalize_role_status($roleStatus ?? yamu_default_account_status_for_role($role), $role);
    $verificationStatus = yamu_normalize_verification_status($verificationStatus ?? yamu_default_verification_status_for_role($role), $role);
    $isPrimary = $isPrimary ? 1 : 0;
    $assignedByUserId = $assignedByUserId !== null ? (int) $assignedByUserId : null;
    $notes = $notes !== null ? trim((string) $notes) : null;

    if ($isPrimary === 1) {
        $unsetStmt = $conn->prepare('UPDATE user_roles SET is_primary = 0, updated_at = NOW() WHERE user_id = ?');
        if ($unsetStmt) {
            $unsetStmt->bind_param('i', $userId);
            $unsetStmt->execute();
            $unsetStmt->close();
        }
    }

    $existingStmt = $conn->prepare('SELECT user_role_id, is_primary FROM user_roles WHERE user_id = ? AND role_key = ? LIMIT 1');
    if (!$existingStmt) {
        return false;
    }

    $existingStmt->bind_param('is', $userId, $role);
    $existingStmt->execute();
    $existingResult = $existingStmt->get_result();
    $existing = $existingResult ? $existingResult->fetch_assoc() : null;
    $existingStmt->close();

    if ($existing) {
        if ($isPrimary !== 1) {
            $isPrimary = (int) ($existing['is_primary'] ?? 0) === 1 ? 1 : 0;
        }

        $updateStmt = $conn->prepare(
            'UPDATE user_roles
             SET role_status = ?, verification_status = ?, is_primary = ?, assigned_by_user_id = ?, notes = ?, updated_at = NOW()
             WHERE user_role_id = ?'
        );

        if (!$updateStmt) {
            return false;
        }

        $userRoleId = (int) $existing['user_role_id'];
        $updateStmt->bind_param('ssiisi', $roleStatus, $verificationStatus, $isPrimary, $assignedByUserId, $notes, $userRoleId);
        $ok = $updateStmt->execute();
        $updateStmt->close();
        return $ok;
    }

    $insertStmt = $conn->prepare(
        'INSERT INTO user_roles (user_id, role_key, role_status, verification_status, is_primary, assigned_by_user_id, notes, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
    );

    if (!$insertStmt) {
        return false;
    }

    $insertStmt->bind_param('isssiis', $userId, $role, $roleStatus, $verificationStatus, $isPrimary, $assignedByUserId, $notes);
    $ok = $insertStmt->execute();
    $insertStmt->close();

    if ($ok) {
        yamu_ensure_user_primary_role_assignment($conn, $userId, $isPrimary === 1 ? $role : null);
    }

    return $ok;
}

function yamu_ensure_user_primary_role_assignment($conn, $userId, $preferredRole = null)
{
    if (!$conn || !yamu_table_exists($conn, 'user_roles')) {
        return false;
    }

    $userId = (int) $userId;
    $preferredRole = $preferredRole !== null ? yamu_normalize_role($preferredRole) : null;
    $assignments = yamu_fetch_user_roles($conn, $userId);

    if (empty($assignments)) {
        return false;
    }

    $primaryRole = null;

    if ($preferredRole !== null && isset($assignments[$preferredRole])) {
        $primaryRole = $preferredRole;
    }

    if ($primaryRole === null) {
        foreach ($assignments as $assignmentRole => $assignment) {
            if (!empty($assignment['is_primary'])) {
                $primaryRole = $assignmentRole;
                break;
            }
        }
    }

    if ($primaryRole === null) {
        foreach ($assignments as $assignmentRole => $assignment) {
            $status = yamu_normalize_role_status($assignment['role_status'] ?? 'active', $assignmentRole);
            if (!yamu_is_role_blocked($status)) {
                $primaryRole = $assignmentRole;
                break;
            }
        }
    }

    if ($primaryRole === null) {
        $primaryRole = array_key_first($assignments);
    }

    $resetStmt = $conn->prepare('UPDATE user_roles SET is_primary = 0, updated_at = NOW() WHERE user_id = ?');
    if (!$resetStmt) {
        return false;
    }
    $resetStmt->bind_param('i', $userId);
    $resetStmt->execute();
    $resetStmt->close();

    $setStmt = $conn->prepare('UPDATE user_roles SET is_primary = 1, updated_at = NOW() WHERE user_id = ? AND role_key = ? LIMIT 1');
    if (!$setStmt) {
        return false;
    }

    $setStmt->bind_param('is', $userId, $primaryRole);
    $ok = $setStmt->execute();
    $setStmt->close();

    return $ok;
}

function yamu_remove_user_role_assignment($conn, $userId, $role)
{
    if (!$conn || !yamu_table_exists($conn, 'user_roles')) {
        return false;
    }

    $userId = (int) $userId;
    $role = yamu_normalize_role($role);

    $countStmt = $conn->prepare('SELECT COUNT(*) AS role_count FROM user_roles WHERE user_id = ?');
    if (!$countStmt) {
        return false;
    }
    $countStmt->bind_param('i', $userId);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $countRow = $countResult ? $countResult->fetch_assoc() : ['role_count' => 0];
    $countStmt->close();

    if ((int) ($countRow['role_count'] ?? 0) <= 1) {
        return false;
    }

    $stmt = $conn->prepare('DELETE FROM user_roles WHERE user_id = ? AND role_key = ?');
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('is', $userId, $role);
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) {
        yamu_ensure_user_primary_role_assignment($conn, $userId);
    }

    return $ok;
}

function yamu_sync_user_primary_role_snapshot($conn, $userId)
{
    if (!$conn || !yamu_table_exists($conn, 'user_roles')) {
        return false;
    }

    $userId = (int) $userId;
    yamu_ensure_user_primary_role_assignment($conn, $userId);
    $stmt = $conn->prepare(
        'SELECT role_key, role_status, verification_status
         FROM user_roles
         WHERE user_id = ?
         ORDER BY is_primary DESC, created_at ASC
         LIMIT 1'
    );

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$row) {
        return false;
    }

    $role = yamu_normalize_role($row['role_key'] ?? 'customer');
    $roleStatus = yamu_normalize_role_status($row['role_status'] ?? yamu_default_account_status_for_role($role), $role);
    $verificationStatus = yamu_normalize_verification_status($row['verification_status'] ?? yamu_default_verification_status_for_role($role), $role);

    $updateStmt = $conn->prepare(
        'UPDATE users
         SET role = ?, account_status = ?, verification_status = ?, updated_at = NOW()
         WHERE user_id = ?'
    );

    if (!$updateStmt) {
        return false;
    }

    $updateStmt->bind_param('sssi', $role, $roleStatus, $verificationStatus, $userId);
    $ok = $updateStmt->execute();
    $updateStmt->close();

    return $ok;
}

function yamu_ensure_role_profile_row($conn, $userId, $role, $sourceUser = null)
{
    if (!$conn) {
        return false;
    }

    $userId = (int) $userId;
    $role = yamu_normalize_role($role);
    $sourceUser = is_array($sourceUser) ? $sourceUser : yamu_fetch_user_by_id($conn, $userId);

    if ($role === 'customer' && yamu_table_exists($conn, 'customer_profiles')) {
        $stmt = $conn->prepare('INSERT IGNORE INTO customer_profiles (user_id, created_at, updated_at) VALUES (?, NOW(), NOW())');
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('i', $userId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    if ($role === 'driver' && yamu_table_exists($conn, 'driver_profiles')) {
        $licenseOrNic = trim((string) ($sourceUser['license_or_nic'] ?? ''));
        $verificationStatus = yamu_normalize_verification_status($sourceUser['verification_status'] ?? 'pending', 'driver');
        $stmt = $conn->prepare(
            'INSERT IGNORE INTO driver_profiles (user_id, driving_license_number, nic_id, verification_status, created_at, updated_at)
             VALUES (?, ?, ?, ?, NOW(), NOW())'
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('isss', $userId, $licenseOrNic, $licenseOrNic, $verificationStatus);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    if ($role === 'staff' && yamu_table_exists($conn, 'staff_profiles')) {
        $storeOwner = trim((string) ($sourceUser['full_name'] ?? ''));
        $storeAddress = trim((string) ($sourceUser['address'] ?? ''));
        $storeContact = trim((string) ($sourceUser['phone'] ?? ''));
        $storeEmail = trim((string) ($sourceUser['email'] ?? ''));
        $verificationStatus = yamu_normalize_verification_status($sourceUser['verification_status'] ?? 'pending', 'staff');
        $stmt = $conn->prepare(
            'INSERT IGNORE INTO staff_profiles (user_id, store_owner, store_address, store_contact_number, store_email, verification_status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('isssss', $userId, $storeOwner, $storeAddress, $storeContact, $storeEmail, $verificationStatus);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    if ($role === 'admin' && yamu_table_exists($conn, 'admin_profiles')) {
        $permissions = 'all';
        $stmt = $conn->prepare('INSERT IGNORE INTO admin_profiles (user_id, system_permissions, created_at, updated_at) VALUES (?, ?, NOW(), NOW())');
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('is', $userId, $permissions);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    return false;
}

function yamu_fetch_role_profile($conn, $userId, $role)
{
    if (!$conn) {
        return null;
    }

    $userId = (int) $userId;
    $role = yamu_normalize_role($role);

    $tableByRole = [
        'customer' => 'customer_profiles',
        'driver' => 'driver_profiles',
        'staff' => 'staff_profiles',
        'admin' => 'admin_profiles',
    ];

    $table = $tableByRole[$role] ?? null;

    if (!$table || !yamu_table_exists($conn, $table)) {
        return null;
    }

    $stmt = $conn->prepare("SELECT * FROM {$table} WHERE user_id = ? LIMIT 1");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $profile = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $profile ?: null;
}

function yamu_create_password_reset_token($conn, $userId, $email, $expiryMinutes = 30)
{
    if (!$conn || !yamu_ensure_password_resets_table($conn)) {
        return null;
    }

    $userId = (int) $userId;
    $email = trim((string) $email);

    if ($email === '') {
        return null;
    }

    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);

    $cleanupStmt = $conn->prepare('DELETE FROM password_resets WHERE email = ? OR expires_at < NOW()');
    if ($cleanupStmt) {
        $cleanupStmt->bind_param('s', $email);
        $cleanupStmt->execute();
        $cleanupStmt->close();
    }

    $stmt = $conn->prepare(
        'INSERT INTO password_resets (user_id, email, token_hash, expires_at, created_at)
         VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE), NOW())'
    );

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('issi', $userId, $email, $tokenHash, $expiryMinutes);

    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }

    $stmt->close();
    return $token;
}

function yamu_fetch_password_reset_by_token($conn, $token)
{
    if (!$conn || !yamu_ensure_password_resets_table($conn)) {
        return null;
    }

    $token = trim((string) $token);

    if ($token === '') {
        return null;
    }

    $tokenHash = hash('sha256', $token);
    $stmt = $conn->prepare(
        'SELECT * FROM password_resets
         WHERE token_hash = ?
           AND used_at IS NULL
           AND expires_at >= NOW()
         LIMIT 1'
    );

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('s', $tokenHash);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $row ?: null;
}

function yamu_mark_password_reset_used($conn, $passwordResetId)
{
    if (!$conn || !yamu_ensure_password_resets_table($conn)) {
        return false;
    }

    $passwordResetId = (int) $passwordResetId;
    $stmt = $conn->prepare('UPDATE password_resets SET used_at = NOW() WHERE password_reset_id = ?');

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('i', $passwordResetId);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function yamu_users_password_column_length($conn)
{
    if (!$conn || !yamu_table_exists($conn, 'users')) {
        return 0;
    }

    $result = @mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'password'");

    if (!$result || $result->num_rows === 0) {
        if ($result instanceof mysqli_result) {
            $result->free();
        }
        return 0;
    }

    $row = $result->fetch_assoc();
    $result->free();
    $type = strtolower((string) ($row['Type'] ?? ''));

    if (preg_match('/varchar\\((\\d+)\\)/', $type, $matches)) {
        return (int) ($matches[1] ?? 0);
    }

    return 0;
}

function yamu_users_has_column($conn, $columnName)
{
    if (!$conn || !yamu_table_exists($conn, 'users')) {
        return false;
    }

    $columnName = trim((string) $columnName);
    if ($columnName === '') {
        return false;
    }

    $escapedColumn = mysqli_real_escape_string($conn, $columnName);
    $result = @mysqli_query($conn, "SHOW COLUMNS FROM users LIKE '{$escapedColumn}'");

    if (!$result) {
        return false;
    }

    $exists = $result->num_rows > 0;
    $result->free();

    return $exists;
}

function yamu_ensure_users_password_column($conn)
{
    if (!$conn || !yamu_table_exists($conn, 'users')) {
        return false;
    }

    $currentLength = yamu_users_password_column_length($conn);

    if ($currentLength >= 255) {
        return true;
    }

    return (bool) $conn->query('ALTER TABLE users MODIFY COLUMN password VARCHAR(255) DEFAULT NULL');
}

function yamu_ensure_users_last_login_column($conn)
{
    if (!$conn || !yamu_table_exists($conn, 'users')) {
        return false;
    }

    if (yamu_users_has_column($conn, 'last_login_at')) {
        return true;
    }

    return (bool) $conn->query('ALTER TABLE users ADD COLUMN last_login_at DATETIME DEFAULT NULL AFTER updated_at');
}

function yamu_ensure_password_resets_table($conn)
{
    if (!$conn) {
        return false;
    }

    if (yamu_table_exists($conn, 'password_resets')) {
        return true;
    }

    $sql = "CREATE TABLE IF NOT EXISTS `password_resets` (
        `password_reset_id` INT(11) NOT NULL AUTO_INCREMENT,
        `user_id` INT(11) DEFAULT NULL,
        `email` VARCHAR(255) NOT NULL,
        `token_hash` CHAR(64) NOT NULL,
        `expires_at` DATETIME NOT NULL,
        `used_at` DATETIME DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`password_reset_id`),
        UNIQUE KEY `uk_password_resets_token_hash` (`token_hash`),
        KEY `idx_password_resets_email` (`email`),
        KEY `idx_password_resets_expires` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    return (bool) $conn->query($sql);
}

function yamu_profile_avatar_path($fileName)
{
    $fileName = !empty($fileName) ? $fileName : 'avatar.png';
    return 'assets/images/uploads/avatar/' . $fileName;
}
