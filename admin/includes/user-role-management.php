<?php
require_once __DIR__ . '/../../includes/auth.php';
carzo_start_session();
carzo_require_admin('../index.php', '../access-denied.php');
include 'config.php';

$adminUserId = (int) ($_SESSION['admin']['user_id'] ?? 0);
$defaultRedirect = '../users.php';

function carzo_admin_redirect_with_context($basePath, $userId, $type, $message)
{
    $target = $basePath;
    if ($userId > 0 && strpos($basePath, 'user_id=') === false) {
        $separator = strpos($basePath, '?') === false ? '?' : '&';
        $target .= $separator . 'user_id=' . (int) $userId;
    }
    carzo_redirect_with_message($target, $type, $message);
}

function carzo_admin_fetch_target_user($conn, $userId, $fallbackRedirect)
{
    $user = carzo_fetch_user_by_id($conn, $userId);
    if (!$user) {
        carzo_redirect_with_message($fallbackRedirect, 'error', 'User not found');
    }
    return $user;
}

function carzo_admin_sync_actor_sessions($conn, $userId)
{
    $userId = (int) $userId;
    $sessionUserId = (int) ($_SESSION['user']['user_ID'] ?? 0);
    $sessionAdminUserId = (int) ($_SESSION['admin']['user_id'] ?? 0);

    if ($sessionUserId === $userId) {
        carzo_refresh_user_session($conn, $userId, carzo_current_user_role());
    }

    if ($sessionAdminUserId === $userId) {
        $updatedAdminUser = carzo_fetch_user_by_id($conn, $userId);

        if ($updatedAdminUser) {
            $adminRole = carzo_normalize_role($updatedAdminUser['role'] ?? 'admin');
            $adminStatus = carzo_normalize_account_status($updatedAdminUser['account_status'] ?? 'active', $adminRole);

            if (carzo_is_admin_panel_role($adminRole) && !carzo_is_role_blocked($adminStatus)) {
                carzo_set_admin_session_from_user($updatedAdminUser, $adminRole);
            } else {
                unset($_SESSION['admin']);
            }
        }
    }
}

if (isset($_POST['assignRole'])) {
    $userId = (int) ($_POST['user_id'] ?? 0);
    $role = carzo_normalize_role($_POST['role'] ?? 'customer');
    $roleStatus = carzo_normalize_role_status($_POST['role_status'] ?? carzo_default_account_status_for_role($role), $role);
    $verificationStatus = carzo_normalize_verification_status($_POST['verification_status'] ?? carzo_default_verification_status_for_role($role), $role);
    $isPrimary = isset($_POST['is_primary']) ? (int) $_POST['is_primary'] === 1 : false;
    $notes = trim((string) ($_POST['notes'] ?? ''));
    $redirectPath = trim((string) ($_POST['redirect'] ?? '../user-roles.php'));

    $user = carzo_admin_fetch_target_user($conn, $userId, $defaultRedirect);

    if ($role === 'admin' && ($_SESSION['admin']['role'] ?? 'staff') !== 'admin') {
        carzo_admin_redirect_with_context($redirectPath, $userId, 'error', 'Only admin users can assign the Admin role');
    }

    if (carzo_table_exists($conn, 'user_roles')) {
        if (!carzo_upsert_user_role_assignment($conn, $userId, $role, $roleStatus, $verificationStatus, $isPrimary, $adminUserId, $notes)) {
            carzo_admin_redirect_with_context($redirectPath, $userId, 'error', 'Unable to assign role');
        }

        carzo_ensure_role_profile_row($conn, $userId, $role, $user);
        carzo_sync_user_primary_role_snapshot($conn, $userId);
    } else {
        $stmt = $conn->prepare('UPDATE users SET role = ?, account_status = ?, verification_status = ?, updated_at = NOW() WHERE user_id = ?');
        if (!$stmt) {
            carzo_admin_redirect_with_context($redirectPath, $userId, 'error', 'Unable to assign role');
        }
        $stmt->bind_param('sssi', $role, $roleStatus, $verificationStatus, $userId);
        $stmt->execute();
        $stmt->close();
    }

    carzo_admin_sync_actor_sessions($conn, $userId);
    carzo_admin_redirect_with_context($redirectPath, $userId, 'msg', 'Role updated successfully');
}

if (isset($_POST['removeRole'])) {
    $userId = (int) ($_POST['user_id'] ?? 0);
    $role = carzo_normalize_role($_POST['role'] ?? 'customer');
    $redirectPath = trim((string) ($_POST['redirect'] ?? '../user-roles.php'));

    carzo_admin_fetch_target_user($conn, $userId, $defaultRedirect);

    if (!carzo_remove_user_role_assignment($conn, $userId, $role)) {
        carzo_admin_redirect_with_context($redirectPath, $userId, 'error', 'Unable to remove role. Keep at least one role assigned.');
    }

    carzo_sync_user_primary_role_snapshot($conn, $userId);
    carzo_admin_sync_actor_sessions($conn, $userId);
    carzo_admin_redirect_with_context($redirectPath, $userId, 'msg', 'Role removed successfully');
}

if (isset($_POST['verifyUserRole'])) {
    $userId = (int) ($_POST['user_id'] ?? 0);
    $role = carzo_normalize_role($_POST['role'] ?? 'driver');
    $verificationStatus = strtolower(trim((string) ($_POST['verification_status'] ?? 'pending')));
    $redirectPath = trim((string) ($_POST['redirect'] ?? '../user-verify.php'));

    if (!in_array($role, ['driver', 'staff'], true)) {
        carzo_admin_redirect_with_context($redirectPath, $userId, 'error', 'Only driver and staff roles can be verified from this page');
    }

    $user = carzo_admin_fetch_target_user($conn, $userId, $defaultRedirect);

    if (!in_array($verificationStatus, ['pending', 'verified', 'approved', 'rejected'], true)) {
        $verificationStatus = 'pending';
    }

    $normalizedVerification = $verificationStatus === 'approved' ? 'verified' : $verificationStatus;
    $roleStatus = $normalizedVerification === 'verified' ? 'active' : ($normalizedVerification === 'rejected' ? 'rejected' : 'pending');

    if (!carzo_upsert_user_role_assignment($conn, $userId, $role, $roleStatus, $normalizedVerification, false, $adminUserId, 'Verification updated by admin')) {
        carzo_admin_redirect_with_context($redirectPath, $userId, 'error', 'Unable to update verification');
    }

    carzo_ensure_role_profile_row($conn, $userId, $role, $user);

    if ($role === 'driver' && carzo_table_exists($conn, 'driver_profiles')) {
        $stmt = $conn->prepare('UPDATE driver_profiles SET verification_status = ?, verified_at = CASE WHEN ? = \'verified\' THEN NOW() ELSE NULL END, updated_at = NOW() WHERE user_id = ?');
        if ($stmt) {
            $stmt->bind_param('ssi', $normalizedVerification, $normalizedVerification, $userId);
            $stmt->execute();
            $stmt->close();
        }
    }

    if ($role === 'staff' && carzo_table_exists($conn, 'staff_profiles')) {
        $stmt = $conn->prepare('UPDATE staff_profiles SET verification_status = ?, verified_at = CASE WHEN ? = \'verified\' THEN NOW() ELSE NULL END, updated_at = NOW() WHERE user_id = ?');
        if ($stmt) {
            $stmt->bind_param('ssi', $normalizedVerification, $normalizedVerification, $userId);
            $stmt->execute();
            $stmt->close();
        }
    }

    carzo_sync_user_primary_role_snapshot($conn, $userId);
    carzo_admin_sync_actor_sessions($conn, $userId);
    carzo_admin_redirect_with_context($redirectPath, $userId, 'msg', ucfirst($role) . ' verification updated successfully');
}

if (isset($_POST['updateUserStatus'])) {
    $userId = (int) ($_POST['user_id'] ?? 0);
    $newStatus = carzo_normalize_account_status($_POST['account_status'] ?? 'active');
    $redirectPath = trim((string) ($_POST['redirect'] ?? '../user-status.php'));

    carzo_admin_fetch_target_user($conn, $userId, $defaultRedirect);

    $stmt = $conn->prepare('UPDATE users SET account_status = ?, updated_at = NOW() WHERE user_id = ?');
    if (!$stmt) {
        carzo_admin_redirect_with_context($redirectPath, $userId, 'error', 'Unable to update account status');
    }
    $stmt->bind_param('si', $newStatus, $userId);
    $stmt->execute();
    $stmt->close();

    if (carzo_table_exists($conn, 'user_roles')) {
        if (in_array($newStatus, ['suspended', 'rejected', 'deactivated'], true)) {
            $roleStmt = $conn->prepare('UPDATE user_roles SET role_status = ?, updated_at = NOW() WHERE user_id = ?');
            if ($roleStmt) {
                $roleStmt->bind_param('si', $newStatus, $userId);
                $roleStmt->execute();
                $roleStmt->close();
            }
        } else {
            $primaryStmt = $conn->prepare('UPDATE user_roles SET role_status = ?, updated_at = NOW() WHERE user_id = ? AND is_primary = 1');
            if ($primaryStmt) {
                $primaryStmt->bind_param('si', $newStatus, $userId);
                $primaryStmt->execute();
                $primaryStmt->close();
            }
        }

        carzo_sync_user_primary_role_snapshot($conn, $userId);
    }

    carzo_admin_sync_actor_sessions($conn, $userId);
    carzo_admin_redirect_with_context($redirectPath, $userId, 'msg', 'Account status updated successfully');
}

carzo_redirect_with_message($defaultRedirect, 'error', 'Invalid user role management action');
