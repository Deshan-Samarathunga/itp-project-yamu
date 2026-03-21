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
    return password_hash((string) $password, PASSWORD_DEFAULT);
}

function carzo_password_matches($plainPassword, $storedPassword)
{
    if ($storedPassword === null || $storedPassword === '') {
        return false;
    }

    if (strpos($storedPassword, '$2y$') === 0 || strpos($storedPassword, '$argon2') === 0) {
        return password_verify((string) $plainPassword, (string) $storedPassword);
    }

    return hash_equals(strtolower((string) $storedPassword), md5((string) $plainPassword));
}

function carzo_password_needs_rehash_upgrade($storedPassword)
{
    if ($storedPassword === null || $storedPassword === '') {
        return true;
    }

    if (strpos($storedPassword, '$2y$') === 0 || strpos($storedPassword, '$argon2') === 0) {
        return password_needs_rehash((string) $storedPassword, PASSWORD_DEFAULT);
    }

    return true;
}

function carzo_password_appears_truncated($storedPassword)
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

function carzo_role_label($role)
{
    return ucfirst(carzo_normalize_role($role));
}

function carzo_default_account_status_for_role($role)
{
    $role = carzo_normalize_role($role);
    return in_array($role, ['driver', 'staff'], true) ? 'pending' : 'active';
}

function carzo_default_verification_status_for_role($role)
{
    $role = carzo_normalize_role($role);
    return in_array($role, ['driver', 'staff'], true) ? 'pending' : 'verified';
}

function carzo_is_admin_panel_role($role)
{
    return in_array(carzo_normalize_role($role), ['admin', 'staff'], true);
}

function carzo_normalize_account_status($status, $role = 'customer')
{
    $status = strtolower(trim((string) $status));
    $allowedStatuses = ['active', 'pending', 'verified', 'suspended', 'rejected', 'deactivated'];

    if (!in_array($status, $allowedStatuses, true)) {
        return carzo_default_account_status_for_role($role);
    }

    return $status;
}

function carzo_normalize_role_status($status, $role = 'customer')
{
    return carzo_normalize_account_status($status, $role);
}

function carzo_normalize_verification_status($status, $role = 'customer')
{
    $status = strtolower(trim((string) $status));
    $allowedStatuses = ['unverified', 'pending', 'approved', 'rejected', 'verified'];

    if (!in_array($status, $allowedStatuses, true)) {
        return carzo_default_verification_status_for_role($role);
    }

    return $status;
}

function carzo_table_exists($conn, $tableName)
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

function carzo_fetch_available_roles($conn = null)
{
    $defaults = [
        ['role_key' => 'customer', 'role_name' => 'Customer'],
        ['role_key' => 'driver', 'role_name' => 'Driver'],
        ['role_key' => 'staff', 'role_name' => 'Staff'],
        ['role_key' => 'admin', 'role_name' => 'Admin'],
    ];

    if (!$conn || !carzo_table_exists($conn, 'roles')) {
        return $defaults;
    }

    $result = $conn->query("SELECT role_key, role_name FROM roles ORDER BY FIELD(role_key, 'customer', 'driver', 'staff', 'admin')");

    if (!$result) {
        return $defaults;
    }

    $roles = [];

    while ($row = $result->fetch_assoc()) {
        $roles[] = [
            'role_key' => carzo_normalize_role($row['role_key'] ?? 'customer'),
            'role_name' => trim((string) ($row['role_name'] ?? carzo_role_label($row['role_key'] ?? 'customer'))),
        ];
    }

    $result->free();

    return !empty($roles) ? $roles : $defaults;
}

function carzo_fetch_user_roles($conn, $userId, $fallbackRole = 'customer', $fallbackAccountStatus = 'active', $fallbackVerificationStatus = 'verified')
{
    $assignments = [];
    $userId = (int) $userId;

    if ($conn && $userId > 0 && carzo_table_exists($conn, 'user_roles')) {
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
                    $role = carzo_normalize_role($row['role_key'] ?? 'customer');
                    $assignments[$role] = [
                        'role' => $role,
                        'role_status' => carzo_normalize_role_status($row['role_status'] ?? carzo_default_account_status_for_role($role), $role),
                        'verification_status' => carzo_normalize_verification_status($row['verification_status'] ?? carzo_default_verification_status_for_role($role), $role),
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

    $role = carzo_normalize_role($fallbackRole);
    $assignments[$role] = [
        'role' => $role,
        'role_status' => carzo_normalize_role_status($fallbackAccountStatus, $role),
        'verification_status' => carzo_normalize_verification_status($fallbackVerificationStatus, $role),
        'is_primary' => true,
    ];

    return $assignments;
}

function carzo_get_user_role_assignment($roleAssignments, $role)
{
    $role = carzo_normalize_role($role);
    $roleAssignments = is_array($roleAssignments) ? $roleAssignments : [];
    return $roleAssignments[$role] ?? null;
}

function carzo_pick_active_role(array $roleAssignments, $preferredRole = null)
{
    $preferredRole = carzo_normalize_role($preferredRole);

    if ($preferredRole && isset($roleAssignments[$preferredRole])) {
        return $preferredRole;
    }

    foreach ($roleAssignments as $assignmentRole => $assignment) {
        $status = carzo_normalize_role_status($assignment['role_status'] ?? 'active', $assignmentRole);
        if (!empty($assignment['is_primary']) && !carzo_is_role_blocked($status)) {
            return carzo_normalize_role($assignmentRole);
        }
    }

    foreach ($roleAssignments as $assignmentRole => $assignment) {
        $status = carzo_normalize_role_status($assignment['role_status'] ?? 'active', $assignmentRole);
        if (in_array($status, ['active', 'pending', 'verified'], true)) {
            return carzo_normalize_role($assignmentRole);
        }
    }

    foreach ($roleAssignments as $assignmentRole => $assignment) {
        return carzo_normalize_role($assignmentRole);
    }

    return 'customer';
}

function carzo_build_user_session(array $row, array $roleAssignments = [], $preferredRole = null)
{
    if (empty($roleAssignments)) {
        $fallbackRole = carzo_normalize_role($row['role'] ?? 'customer');
        $roleAssignments = [
            $fallbackRole => [
                'role' => $fallbackRole,
                'role_status' => carzo_normalize_role_status($row['account_status'] ?? carzo_default_account_status_for_role($fallbackRole), $fallbackRole),
                'verification_status' => carzo_normalize_verification_status($row['verification_status'] ?? carzo_default_verification_status_for_role($fallbackRole), $fallbackRole),
                'is_primary' => true,
            ],
        ];
    }

    $activeRole = carzo_pick_active_role($roleAssignments, $preferredRole);
    $activeAssignment = $roleAssignments[$activeRole] ?? null;
    $activeAccountStatus = carzo_normalize_account_status($activeAssignment['role_status'] ?? ($row['account_status'] ?? carzo_default_account_status_for_role($activeRole)), $activeRole);
    $activeVerificationStatus = carzo_normalize_verification_status($activeAssignment['verification_status'] ?? ($row['verification_status'] ?? carzo_default_verification_status_for_role($activeRole)), $activeRole);

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
        'primary_role' => carzo_normalize_role($row['role'] ?? $activeRole),
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

function carzo_build_admin_session_from_user(array $row, $activeRole = null, $accountStatus = null, $verificationStatus = null)
{
    $role = carzo_normalize_role($activeRole ?? ($row['role'] ?? 'admin'));

    if (!carzo_is_admin_panel_role($role)) {
        $role = 'admin';
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
        'account_status' => carzo_normalize_account_status($accountStatus ?? ($row['account_status'] ?? carzo_default_account_status_for_role($role)), $role),
        'verification_status' => carzo_normalize_verification_status($verificationStatus ?? ($row['verification_status'] ?? carzo_default_verification_status_for_role($role)), $role),
    ];
}

function carzo_build_admin_session_from_legacy_admin(array $row)
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

function carzo_set_user_session(array $row, $conn = null, $preferredRole = null)
{
    $roleAssignments = carzo_fetch_user_roles(
        $conn,
        (int) ($row['user_id'] ?? 0),
        $row['role'] ?? 'customer',
        $row['account_status'] ?? carzo_default_account_status_for_role($row['role'] ?? 'customer'),
        $row['verification_status'] ?? carzo_default_verification_status_for_role($row['role'] ?? 'customer')
    );

    $sessionUser = carzo_build_user_session($row, $roleAssignments, $preferredRole);
    $_SESSION['user'] = $sessionUser;
    $_SESSION['authenticated'] = true;

    if (carzo_is_admin_panel_role($sessionUser['active_role'])) {
        $_SESSION['admin'] = carzo_build_admin_session_from_user(
            $row,
            $sessionUser['active_role'],
            $sessionUser['account_status'],
            $sessionUser['verification_status']
        );
    } else {
        unset($_SESSION['admin']);
    }

    return $sessionUser;
}

function carzo_refresh_user_session($conn, $userId, $preferredRole = null)
{
    $user = carzo_fetch_user_by_id($conn, $userId);

    if (!$user) {
        return null;
    }

    return carzo_set_user_session($user, $conn, $preferredRole);
}

function carzo_set_admin_session_from_user(array $row, $activeRole = null)
{
    $_SESSION['admin'] = carzo_build_admin_session_from_user($row, $activeRole);
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
    return $_SESSION['user']['active_role'] ?? ($_SESSION['user']['role'] ?? null);
}

function carzo_current_user_roles()
{
    $roles = $_SESSION['user']['roles'] ?? [];
    return is_array($roles) ? array_values($roles) : [];
}

function carzo_current_user_role_assignments()
{
    $assignments = $_SESSION['user']['role_assignments'] ?? [];
    return is_array($assignments) ? $assignments : [];
}

function carzo_current_user_has_assigned_role($role)
{
    $role = carzo_normalize_role($role);
    $assignments = carzo_current_user_role_assignments();
    return isset($assignments[$role]);
}

function carzo_current_user_role_status($role = null)
{
    $role = $role ? carzo_normalize_role($role) : carzo_current_user_role();
    $assignments = carzo_current_user_role_assignments();
    $assignment = $assignments[$role] ?? null;

    if (!$assignment) {
        return carzo_normalize_account_status($_SESSION['user']['account_status'] ?? 'active', $role ?: 'customer');
    }

    return carzo_normalize_role_status($assignment['role_status'] ?? 'active', $role);
}

function carzo_can_access_role_status($status, $allowedStatuses = ['active', 'pending', 'verified'])
{
    $status = carzo_normalize_role_status($status);
    return in_array($status, (array) $allowedStatuses, true);
}

function carzo_is_role_blocked($status)
{
    $status = carzo_normalize_role_status($status);
    return in_array($status, ['suspended', 'rejected', 'deactivated'], true);
}

function carzo_can_switch_to_role($role)
{
    $role = carzo_normalize_role($role);
    $assignments = carzo_current_user_role_assignments();
    $assignment = $assignments[$role] ?? null;

    if (!$assignment) {
        return false;
    }

    return !carzo_is_role_blocked($assignment['role_status'] ?? 'active');
}

function carzo_switch_active_role($conn, $role, &$errorMessage = null)
{
    carzo_start_session();

    if (!carzo_is_user_authenticated()) {
        $errorMessage = 'Please sign in to switch roles';
        return false;
    }

    $role = carzo_normalize_role($role);
    $currentUser = carzo_current_user();
    $userId = (int) ($currentUser['user_ID'] ?? 0);

    if ($userId <= 0) {
        $errorMessage = 'Invalid session state';
        return false;
    }

    $assignments = carzo_fetch_user_roles(
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

    if (carzo_is_role_blocked($targetAssignment['role_status'] ?? 'active')) {
        $errorMessage = 'Selected role is currently unavailable';
        return false;
    }

    $userRow = carzo_fetch_user_by_id($conn, $userId);

    if (!$userRow) {
        $errorMessage = 'User account not found';
        return false;
    }

    carzo_set_user_session($userRow, $conn, $role);
    return true;
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
    if (carzo_is_user_authenticated()) {
        return carzo_public_home_path_for_role(carzo_current_user_role());
    }

    if (carzo_is_admin_authenticated()) {
        return 'admin/dashboard.php';
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
    $roles = array_map('carzo_normalize_role', (array) $roles);
    return in_array(carzo_current_user_role(), $roles, true);
}

function carzo_has_any_assigned_role($roles)
{
    $roles = array_map('carzo_normalize_role', (array) $roles);
    $assignedRoles = carzo_current_user_roles();

    foreach ($roles as $role) {
        if (in_array($role, $assignedRoles, true)) {
            return true;
        }
    }

    return false;
}

function carzo_require_user_roles($roles, $redirect = 'signin.php', $allowedStatuses = ['active', 'pending', 'verified'], $forbiddenRedirect = 'access-denied.php')
{
    carzo_start_session();

    if (!carzo_is_user_authenticated()) {
        carzo_redirect_with_message($redirect, 'error', 'Please sign in to continue');
    }

    if (!carzo_has_user_role($roles)) {
        carzo_redirect_with_message($forbiddenRedirect, 'error', 'You do not have permission to access that page');
    }

    $accountStatus = carzo_current_user_role_status();

    if (!carzo_can_access_role_status($accountStatus, $allowedStatuses)) {
        carzo_redirect_with_message($forbiddenRedirect, 'error', 'Your selected role is currently unavailable');
    }
}

function carzo_require_any_assigned_user_role($roles, $redirect = 'signin.php', $forbiddenRedirect = 'access-denied.php')
{
    carzo_start_session();

    if (!carzo_is_user_authenticated()) {
        carzo_redirect_with_message($redirect, 'error', 'Please sign in to continue');
    }

    if (!carzo_has_any_assigned_role($roles)) {
        carzo_redirect_with_message($forbiddenRedirect, 'error', 'You do not have permission to access that page');
    }
}

function carzo_require_admin($redirect = 'index.php', $forbiddenRedirect = 'access-denied.php')
{
    carzo_start_session();

    if (!carzo_is_admin_authenticated()) {
        if (carzo_is_user_authenticated()) {
            carzo_redirect_with_message($forbiddenRedirect, 'error', 'You do not have permission to access that page');
        }

        carzo_redirect_with_message($redirect, 'error', 'Please sign in as admin to continue');
    }

    $adminStatus = carzo_normalize_account_status($_SESSION['admin']['account_status'] ?? 'active', $_SESSION['admin']['role'] ?? 'admin');
    if (carzo_is_role_blocked($adminStatus)) {
        carzo_redirect_with_message($redirect, 'error', 'Your admin role is currently unavailable');
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

function carzo_touch_user_last_login($conn, $userId)
{
    if (!$conn || (int) $userId <= 0) {
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

function carzo_upgrade_password_hash_if_needed($conn, $userId, $plainPassword, $storedPassword)
{
    if (!carzo_password_needs_rehash_upgrade($storedPassword)) {
        return;
    }

    $newHash = carzo_hash_password($plainPassword);
    $stmt = $conn->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE user_id = ?');

    if (!$stmt) {
        return;
    }

    $stmt->bind_param('si', $newHash, $userId);
    $stmt->execute();
    $stmt->close();
}

function carzo_upsert_user_role_assignment($conn, $userId, $role, $roleStatus = null, $verificationStatus = null, $isPrimary = false, $assignedByUserId = null, $notes = null)
{
    if (!$conn || !carzo_table_exists($conn, 'user_roles')) {
        return false;
    }

    $userId = (int) $userId;
    $role = carzo_normalize_role($role);
    $roleStatus = carzo_normalize_role_status($roleStatus ?? carzo_default_account_status_for_role($role), $role);
    $verificationStatus = carzo_normalize_verification_status($verificationStatus ?? carzo_default_verification_status_for_role($role), $role);
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

    $existingStmt = $conn->prepare('SELECT user_role_id FROM user_roles WHERE user_id = ? AND role_key = ? LIMIT 1');
    if (!$existingStmt) {
        return false;
    }

    $existingStmt->bind_param('is', $userId, $role);
    $existingStmt->execute();
    $existingResult = $existingStmt->get_result();
    $existing = $existingResult ? $existingResult->fetch_assoc() : null;
    $existingStmt->close();

    if ($existing) {
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

    return $ok;
}

function carzo_remove_user_role_assignment($conn, $userId, $role)
{
    if (!$conn || !carzo_table_exists($conn, 'user_roles')) {
        return false;
    }

    $userId = (int) $userId;
    $role = carzo_normalize_role($role);

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
    return $ok;
}

function carzo_sync_user_primary_role_snapshot($conn, $userId)
{
    if (!$conn || !carzo_table_exists($conn, 'user_roles')) {
        return false;
    }

    $userId = (int) $userId;
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

    $role = carzo_normalize_role($row['role_key'] ?? 'customer');
    $roleStatus = carzo_normalize_role_status($row['role_status'] ?? carzo_default_account_status_for_role($role), $role);
    $verificationStatus = carzo_normalize_verification_status($row['verification_status'] ?? carzo_default_verification_status_for_role($role), $role);

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

function carzo_ensure_role_profile_row($conn, $userId, $role, $sourceUser = null)
{
    if (!$conn) {
        return false;
    }

    $userId = (int) $userId;
    $role = carzo_normalize_role($role);
    $sourceUser = is_array($sourceUser) ? $sourceUser : carzo_fetch_user_by_id($conn, $userId);

    if ($role === 'customer' && carzo_table_exists($conn, 'customer_profiles')) {
        $stmt = $conn->prepare('INSERT IGNORE INTO customer_profiles (user_id, created_at, updated_at) VALUES (?, NOW(), NOW())');
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('i', $userId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    if ($role === 'driver' && carzo_table_exists($conn, 'driver_profiles')) {
        $licenseOrNic = trim((string) ($sourceUser['license_or_nic'] ?? ''));
        $verificationStatus = carzo_normalize_verification_status($sourceUser['verification_status'] ?? 'pending', 'driver');
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

    if ($role === 'staff' && carzo_table_exists($conn, 'staff_profiles')) {
        $storeOwner = trim((string) ($sourceUser['full_name'] ?? ''));
        $storeAddress = trim((string) ($sourceUser['address'] ?? ''));
        $storeContact = trim((string) ($sourceUser['phone'] ?? ''));
        $storeEmail = trim((string) ($sourceUser['email'] ?? ''));
        $verificationStatus = carzo_normalize_verification_status($sourceUser['verification_status'] ?? 'pending', 'staff');
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

    if ($role === 'admin' && carzo_table_exists($conn, 'admin_profiles')) {
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

function carzo_fetch_role_profile($conn, $userId, $role)
{
    if (!$conn) {
        return null;
    }

    $userId = (int) $userId;
    $role = carzo_normalize_role($role);

    $tableByRole = [
        'customer' => 'customer_profiles',
        'driver' => 'driver_profiles',
        'staff' => 'staff_profiles',
        'admin' => 'admin_profiles',
    ];

    $table = $tableByRole[$role] ?? null;

    if (!$table || !carzo_table_exists($conn, $table)) {
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

function carzo_create_password_reset_token($conn, $userId, $email, $expiryMinutes = 30)
{
    if (!$conn || !carzo_table_exists($conn, 'password_resets')) {
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

function carzo_fetch_password_reset_by_token($conn, $token)
{
    if (!$conn || !carzo_table_exists($conn, 'password_resets')) {
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

function carzo_mark_password_reset_used($conn, $passwordResetId)
{
    if (!$conn || !carzo_table_exists($conn, 'password_resets')) {
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

function carzo_profile_avatar_path($fileName)
{
    $fileName = !empty($fileName) ? $fileName : 'avatar.png';
    return 'assets/images/uploads/avatar/' . $fileName;
}
