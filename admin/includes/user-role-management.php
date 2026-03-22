<?php
require_once __DIR__ . '/../../includes/auth.php';
yamu_start_session();
yamu_require_admin('../index.php', '../access-denied.php');
include 'config.php';

$adminUserId = (int) ($_SESSION['admin']['user_id'] ?? 0);
$defaultRedirect = '../users.php';

function yamu_admin_redirect_with_context($basePath, $userId, $type, $message)
{
    $target = $basePath !== '' ? $basePath : '../users.php';

    if ($userId > 0 && strpos($target, 'user_id=') === false) {
        $separator = strpos($target, '?') === false ? '?' : '&';
        $target .= $separator . 'user_id=' . (int) $userId;
    }

    yamu_redirect_with_message($target, $type, $message);
}

function yamu_admin_fetch_target_user($conn, $userId, $fallbackRedirect)
{
    $user = yamu_fetch_user_by_id($conn, $userId);

    if (!$user) {
        yamu_redirect_with_message($fallbackRedirect, 'error', 'User not found');
    }

    return $user;
}

function yamu_admin_fetch_target_assignments($conn, array $user)
{
    return yamu_fetch_user_roles(
        $conn,
        (int) ($user['user_id'] ?? 0),
        $user['role'] ?? 'customer',
        $user['account_status'] ?? 'active',
        $user['verification_status'] ?? 'verified'
    );
}

function yamu_admin_sync_actor_sessions($conn, $userId)
{
    $userId = (int) $userId;
    $sessionUserId = (int) ($_SESSION['user']['user_ID'] ?? 0);
    $sessionAdminUserId = (int) ($_SESSION['admin']['user_id'] ?? 0);
    $preferredUserRole = yamu_current_user_role();

    if ($sessionUserId === $userId) {
        yamu_refresh_user_session($conn, $userId, $preferredUserRole);
    }

    if ($sessionAdminUserId === $userId) {
        $updatedUser = yamu_fetch_user_by_id($conn, $userId);

        if (!$updatedUser) {
            unset($_SESSION['admin']);
            return;
        }

        $assignments = yamu_admin_fetch_target_assignments($conn, $updatedUser);
        $adminAssignment = $assignments['admin'] ?? null;

        if ($adminAssignment && yamu_role_allows_standard_status($adminAssignment['role_status'] ?? 'active')) {
            $adminSession = yamu_build_admin_session_from_user(
                $updatedUser,
                'admin',
                $adminAssignment['role_status'] ?? 'active',
                $adminAssignment['verification_status'] ?? 'verified'
            );

            if ($adminSession) {
                $_SESSION['admin'] = $adminSession;
                return;
            }
        }

        unset($_SESSION['admin']);
    }
}

function yamu_admin_update_role_profile_verification($conn, $userId, $role, $verificationStatus)
{
    $userId = (int) $userId;
    $role = yamu_normalize_role($role);
    $verificationStatus = yamu_normalize_verification_status($verificationStatus, $role);

    if ($role === 'driver' && yamu_table_exists($conn, 'driver_profiles')) {
        $stmt = $conn->prepare(
            'UPDATE driver_profiles
             SET verification_status = ?, verified_at = CASE WHEN ? = \'verified\' THEN NOW() ELSE NULL END, updated_at = NOW()
             WHERE user_id = ?'
        );

        if ($stmt) {
            $stmt->bind_param('ssi', $verificationStatus, $verificationStatus, $userId);
            $stmt->execute();
            $stmt->close();
        }
    }

    if ($role === 'staff' && yamu_table_exists($conn, 'staff_profiles')) {
        $stmt = $conn->prepare(
            'UPDATE staff_profiles
             SET verification_status = ?, verified_at = CASE WHEN ? = \'verified\' THEN NOW() ELSE NULL END, updated_at = NOW()
             WHERE user_id = ?'
        );

        if ($stmt) {
            $stmt->bind_param('ssi', $verificationStatus, $verificationStatus, $userId);
            $stmt->execute();
            $stmt->close();
        }
    }
}

function yamu_admin_update_all_role_statuses($conn, $userId, array $assignments, $newStatus)
{
    $userId = (int) $userId;

    foreach ($assignments as $roleKey => $assignment) {
        $nextStatus = $newStatus === 'active'
            ? yamu_resolve_role_status_from_verification(
                $roleKey,
                $assignment['verification_status'] ?? yamu_default_verification_status_for_role($roleKey),
                'active'
            )
            : yamu_normalize_role_status($newStatus, $roleKey);

        $stmt = $conn->prepare('UPDATE user_roles SET role_status = ?, updated_at = NOW() WHERE user_id = ? AND role_key = ?');

        if ($stmt) {
            $stmt->bind_param('sis', $nextStatus, $userId, $roleKey);
            $stmt->execute();
            $stmt->close();
        }
    }
}

function yamu_admin_effective_role_status_for_user(array $user, $role, $requestedRoleStatus, $requestedVerificationStatus)
{
    $role = yamu_normalize_role($role);
    $userStatus = yamu_normalize_account_status($user['account_status'] ?? 'active', $role);

    if (yamu_is_role_blocked($userStatus)) {
        return $userStatus;
    }

    if (in_array($role, ['driver', 'staff'], true)) {
        return yamu_resolve_role_status_from_verification($role, $requestedVerificationStatus, $requestedRoleStatus);
    }

    if ($requestedVerificationStatus === 'rejected') {
        return 'rejected';
    }

    return yamu_normalize_role_status($requestedRoleStatus, $role);
}

if (isset($_POST['assignRole'])) {
    $userId = (int) ($_POST['user_id'] ?? 0);
    $role = yamu_normalize_role($_POST['role'] ?? 'customer');
    $requestedRoleStatus = yamu_normalize_role_status($_POST['role_status'] ?? yamu_default_account_status_for_role($role), $role);
    $requestedVerificationStatus = yamu_normalize_verification_status($_POST['verification_status'] ?? yamu_default_verification_status_for_role($role), $role);
    $requestedPrimary = isset($_POST['is_primary']) && (int) $_POST['is_primary'] === 1;
    $notes = trim((string) ($_POST['notes'] ?? ''));
    $redirectPath = trim((string) ($_POST['redirect'] ?? '../user-roles.php'));

    $user = yamu_admin_fetch_target_user($conn, $userId, $defaultRedirect);
    $assignments = yamu_admin_fetch_target_assignments($conn, $user);

    if ($role === 'admin') {
        yamu_admin_redirect_with_context($redirectPath, $userId, 'error', 'Admin roles must be seeded in the database and cannot be assigned here');
    }

    $requestedRoleStatus = yamu_admin_effective_role_status_for_user($user, $role, $requestedRoleStatus, $requestedVerificationStatus);

    if ($userId === $adminUserId && $role === 'admin' && !yamu_role_allows_standard_status($requestedRoleStatus)) {
        yamu_admin_redirect_with_context($redirectPath, $userId, 'error', 'You cannot make your own admin role unavailable');
    }

    $isPrimary = $requestedPrimary || empty($assignments);

    if (yamu_table_exists($conn, 'user_roles')) {
        if (!yamu_upsert_user_role_assignment($conn, $userId, $role, $requestedRoleStatus, $requestedVerificationStatus, $isPrimary, $adminUserId, $notes)) {
            yamu_admin_redirect_with_context($redirectPath, $userId, 'error', 'Unable to save role changes');
        }

        yamu_ensure_role_profile_row($conn, $userId, $role, $user);
        yamu_admin_update_role_profile_verification($conn, $userId, $role, $requestedVerificationStatus);
        yamu_sync_user_primary_role_snapshot($conn, $userId);
    } else {
        $stmt = $conn->prepare('UPDATE users SET role = ?, account_status = ?, verification_status = ?, updated_at = NOW() WHERE user_id = ?');

        if (!$stmt) {
            yamu_admin_redirect_with_context($redirectPath, $userId, 'error', 'Unable to save role changes');
        }

        $stmt->bind_param('sssi', $role, $requestedRoleStatus, $requestedVerificationStatus, $userId);
        $stmt->execute();
        $stmt->close();
    }

    yamu_admin_sync_actor_sessions($conn, $userId);
    yamu_admin_redirect_with_context($redirectPath, $userId, 'msg', 'Role saved successfully');
}

if (isset($_POST['removeRole'])) {
    $userId = (int) ($_POST['user_id'] ?? 0);
    $role = yamu_normalize_role($_POST['role'] ?? 'customer');
    $redirectPath = trim((string) ($_POST['redirect'] ?? '../user-roles.php'));

    $user = yamu_admin_fetch_target_user($conn, $userId, $defaultRedirect);
    $assignments = yamu_admin_fetch_target_assignments($conn, $user);

    if (!isset($assignments[$role])) {
        yamu_admin_redirect_with_context($redirectPath, $userId, 'error', 'That role is not assigned to this user');
    }

    if ($userId === $adminUserId && $role === 'admin') {
        yamu_admin_redirect_with_context($redirectPath, $userId, 'error', 'You cannot remove your own admin role while using the admin panel');
    }

    if ($role === 'admin') {
        yamu_admin_redirect_with_context($redirectPath, $userId, 'error', 'Admin roles must be managed in the database, not removed from this page');
    }

    if (count($assignments) <= 1) {
        yamu_admin_redirect_with_context($redirectPath, $userId, 'error', 'Each user must keep at least one assigned role');
    }

    if (!yamu_remove_user_role_assignment($conn, $userId, $role)) {
        yamu_admin_redirect_with_context($redirectPath, $userId, 'error', 'Unable to remove the selected role');
    }

    yamu_sync_user_primary_role_snapshot($conn, $userId);
    yamu_admin_sync_actor_sessions($conn, $userId);
    yamu_admin_redirect_with_context($redirectPath, $userId, 'msg', 'Role removed successfully');
}

if (isset($_POST['verifyUserRole'])) {
    $userId = (int) ($_POST['user_id'] ?? 0);
    $role = yamu_normalize_role($_POST['role'] ?? 'driver');
    $redirectPath = trim((string) ($_POST['redirect'] ?? '../user-verify.php'));
    $verificationStatus = strtolower(trim((string) ($_POST['verification_status'] ?? 'pending')));

    if (!in_array($role, ['driver', 'staff'], true)) {
        yamu_admin_redirect_with_context($redirectPath, $userId, 'error', 'Only driver and staff roles can be verified from this page');
    }

    $user = yamu_admin_fetch_target_user($conn, $userId, $defaultRedirect);
    $assignments = yamu_admin_fetch_target_assignments($conn, $user);
    $assignment = $assignments[$role] ?? null;

    if (!$assignment) {
        yamu_admin_redirect_with_context($redirectPath, $userId, 'error', 'Assign the role before updating verification');
    }

    if (!in_array($verificationStatus, ['pending', 'verified', 'approved', 'rejected'], true)) {
        $verificationStatus = 'pending';
    }

    $normalizedVerification = $verificationStatus === 'approved' ? 'verified' : $verificationStatus;
    $roleStatus = yamu_admin_effective_role_status_for_user($user, $role, $assignment['role_status'] ?? null, $normalizedVerification);

    if (!yamu_upsert_user_role_assignment(
        $conn,
        $userId,
        $role,
        $roleStatus,
        $normalizedVerification,
        !empty($assignment['is_primary']),
        $adminUserId,
        'Verification updated by admin'
    )) {
        yamu_admin_redirect_with_context($redirectPath, $userId, 'error', 'Unable to update verification');
    }

    yamu_ensure_role_profile_row($conn, $userId, $role, $user);
    yamu_admin_update_role_profile_verification($conn, $userId, $role, $normalizedVerification);
    yamu_sync_user_primary_role_snapshot($conn, $userId);
    yamu_admin_sync_actor_sessions($conn, $userId);
    yamu_admin_redirect_with_context($redirectPath, $userId, 'msg', ucfirst($role) . ' verification updated successfully');
}

if (isset($_POST['updateUserStatus'])) {
    $userId = (int) ($_POST['user_id'] ?? 0);
    $redirectPath = trim((string) ($_POST['redirect'] ?? '../user-status.php'));
    $newStatus = strtolower(trim((string) ($_POST['account_status'] ?? 'active')));
    $allowedUserStatuses = ['active', 'suspended', 'deactivated'];

    if (!in_array($newStatus, $allowedUserStatuses, true)) {
        yamu_admin_redirect_with_context($redirectPath, $userId, 'error', 'Invalid user status');
    }

    $user = yamu_admin_fetch_target_user($conn, $userId, $defaultRedirect);
    $assignments = yamu_admin_fetch_target_assignments($conn, $user);

    if ($userId === $adminUserId && $newStatus !== 'active') {
        yamu_admin_redirect_with_context($redirectPath, $userId, 'error', 'You cannot deactivate or suspend your own active admin session');
    }

    $stmt = $conn->prepare('UPDATE users SET account_status = ?, updated_at = NOW() WHERE user_id = ?');

    if (!$stmt) {
        yamu_admin_redirect_with_context($redirectPath, $userId, 'error', 'Unable to update user status');
    }

    $stmt->bind_param('si', $newStatus, $userId);
    $stmt->execute();
    $stmt->close();

    if (yamu_table_exists($conn, 'user_roles')) {
        yamu_admin_update_all_role_statuses($conn, $userId, $assignments, $newStatus);
        yamu_sync_user_primary_role_snapshot($conn, $userId);
    }

    yamu_admin_sync_actor_sessions($conn, $userId);
    yamu_admin_redirect_with_context($redirectPath, $userId, 'msg', 'User status updated successfully');
}

yamu_redirect_with_message($defaultRedirect, 'error', 'Invalid user role management action');
