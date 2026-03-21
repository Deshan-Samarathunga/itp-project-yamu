<?php
require_once __DIR__ . '/auth.php';
carzo_start_session();
include 'config.php';

if (!carzo_is_user_authenticated()) {
    carzo_redirect_with_message('../signin.php', 'error', 'Please sign in to continue');
}

$currentUserId = (int) ($_SESSION['user']['user_ID'] ?? 0);
$fallbackRedirect = '../role-switch.php';

if (isset($_POST['switchRole'])) {
    $role = carzo_normalize_role($_POST['active_role'] ?? 'customer');
    $redirectTo = trim((string) ($_POST['redirect_to'] ?? ''));
    $errorMessage = null;

    if (!carzo_switch_active_role($conn, $role, $errorMessage)) {
        carzo_redirect_with_message('../role-switch.php', 'error', $errorMessage ?: 'Unable to switch role');
    }

    $target = $redirectTo !== '' ? $redirectTo : carzo_current_public_home_path();
    carzo_redirect_with_message('../' . ltrim($target, '/'), 'msg', 'Active role switched to ' . carzo_role_label($role));
}

if (isset($_POST['activateRole'])) {
    $role = carzo_normalize_role($_POST['role'] ?? 'customer');
    $roleNotes = trim((string) ($_POST['activation_notes'] ?? ''));
    $isSelfAdmin = (($_SESSION['admin']['user_id'] ?? 0) === $currentUserId) && carzo_is_admin_authenticated();

    if ($role === 'admin' && !$isSelfAdmin) {
        carzo_redirect_with_message('../role-activation.php', 'error', 'Admin role can only be assigned by an existing admin');
    }

    $existingAssignments = carzo_fetch_user_roles(
        $conn,
        $currentUserId,
        $_SESSION['user']['primary_role'] ?? $_SESSION['user']['role'] ?? 'customer',
        $_SESSION['user']['account_status'] ?? 'active',
        $_SESSION['user']['verification_status'] ?? 'verified'
    );

    if (isset($existingAssignments[$role])) {
        carzo_redirect_with_message('../role-activation.php', 'error', 'This role is already assigned to your account');
    }

    $roleStatus = carzo_default_account_status_for_role($role);
    $verificationStatus = carzo_default_verification_status_for_role($role);

    if (!carzo_upsert_user_role_assignment($conn, $currentUserId, $role, $roleStatus, $verificationStatus, false, $currentUserId, $roleNotes)) {
        carzo_redirect_with_message('../role-activation.php', 'error', 'Unable to activate role at the moment');
    }

    $userRow = carzo_fetch_user_by_id($conn, $currentUserId);
    carzo_ensure_role_profile_row($conn, $currentUserId, $role, $userRow);

    if ($role === 'driver' && carzo_table_exists($conn, 'driver_profiles')) {
        $licenseNumber = trim((string) ($_POST['driving_license_number'] ?? ''));
        $licenseExpiryDate = trim((string) ($_POST['license_expiry_date'] ?? ''));
        $nicId = trim((string) ($_POST['nic_id'] ?? ''));
        $serviceArea = trim((string) ($_POST['service_area'] ?? ''));

        $stmt = $conn->prepare(
            'UPDATE driver_profiles
             SET driving_license_number = ?, license_expiry_date = ?, nic_id = ?, service_area = ?, verification_status = ?, updated_at = NOW()
             WHERE user_id = ?'
        );

        if ($stmt) {
            $stmt->bind_param('sssssi', $licenseNumber, $licenseExpiryDate, $nicId, $serviceArea, $verificationStatus, $currentUserId);
            $stmt->execute();
            $stmt->close();
        }
    }

    if ($role === 'staff' && carzo_table_exists($conn, 'staff_profiles')) {
        $storeName = trim((string) ($_POST['store_name'] ?? ''));
        $storeOwner = trim((string) ($_POST['store_owner'] ?? ''));
        $businessRegNo = trim((string) ($_POST['business_registration_number'] ?? ''));
        $storeAddress = trim((string) ($_POST['store_address'] ?? ''));
        $storeContact = trim((string) ($_POST['store_contact_number'] ?? ''));
        $storeEmail = trim((string) ($_POST['store_email'] ?? ''));

        $stmt = $conn->prepare(
            'UPDATE staff_profiles
             SET store_name = ?, store_owner = ?, business_registration_number = ?, store_address = ?, store_contact_number = ?, store_email = ?, verification_status = ?, updated_at = NOW()
             WHERE user_id = ?'
        );

        if ($stmt) {
            $stmt->bind_param('sssssssi', $storeName, $storeOwner, $businessRegNo, $storeAddress, $storeContact, $storeEmail, $verificationStatus, $currentUserId);
            $stmt->execute();
            $stmt->close();
        }
    }

    carzo_refresh_user_session($conn, $currentUserId, carzo_current_user_role());

    $message = in_array($role, ['driver', 'staff'], true)
        ? carzo_role_label($role) . ' role request submitted. Verification is pending.'
        : carzo_role_label($role) . ' role activated successfully.';

    carzo_redirect_with_message('../role-switch.php', 'msg', $message);
}

carzo_redirect_with_message($fallbackRedirect, 'error', 'Invalid role management request');
