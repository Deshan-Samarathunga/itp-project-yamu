<?php
require_once __DIR__ . '/auth.php';
yamu_start_session();
include 'config.php';

yamu_require_authenticated_user('../signin.php');

$currentUserId = (int) ($_SESSION['user']['user_ID'] ?? 0);

if ($currentUserId <= 0) {
    yamu_redirect_with_message('../signin.php', 'error', 'Please sign in to continue');
}

if (isset($_POST['switchRole'])) {
    $role = yamu_normalize_role($_POST['active_role'] ?? 'customer');
    $redirectTo = trim((string) ($_POST['redirect_to'] ?? ''));
    $errorMessage = null;

    if (!yamu_switch_active_role($conn, $role, $errorMessage)) {
        yamu_redirect_with_message('../role-switch.php', 'error', $errorMessage ?: 'Unable to switch role');
    }

    $target = $redirectTo !== '' ? $redirectTo : yamu_current_public_home_path();
    yamu_redirect_with_message('../' . ltrim($target, '/'), 'msg', 'Active role switched to ' . yamu_role_label($role));
}

if (isset($_POST['activateRole'])) {
    if (!yamu_current_user_has_assigned_role('customer')) {
        yamu_redirect_with_message('../access-denied.php', 'error', 'Only customer accounts can request provider roles');
    }

    if (yamu_is_admin_panel_role(yamu_current_user_role())) {
        yamu_redirect_with_message('../role-switch.php', 'error', 'Switch to your customer role before requesting another role');
    }

    $role = yamu_normalize_role($_POST['role'] ?? 'customer');
    $roleNotes = trim((string) ($_POST['activation_notes'] ?? ''));
    $redirectPath = '../role-activation.php';

    if (!in_array($role, ['driver', 'staff'], true)) {
        yamu_redirect_with_message($redirectPath, 'error', 'Selected role cannot be activated from this page');
    }

    $existingAssignments = yamu_fetch_user_roles(
        $conn,
        $currentUserId,
        $_SESSION['user']['primary_role'] ?? $_SESSION['user']['role'] ?? 'customer',
        $_SESSION['user']['account_status'] ?? 'active',
        $_SESSION['user']['verification_status'] ?? 'verified'
    );

    if (isset($existingAssignments[$role])) {
        yamu_redirect_with_message($redirectPath, 'error', 'This role is already assigned to your account');
    }

    $roleStatus = yamu_default_account_status_for_role($role);
    $verificationStatus = yamu_default_verification_status_for_role($role);
    $userRow = yamu_fetch_user_by_id($conn, $currentUserId);

    if (!$userRow) {
        yamu_redirect_with_message('../signin.php', 'error', 'User account not found');
    }

    if ($role === 'driver') {
        $drivingLicenseNumber = trim((string) ($_POST['driving_license_number'] ?? ''));
        $licenseExpiryDate = trim((string) ($_POST['license_expiry_date'] ?? ''));
        $nicId = trim((string) ($_POST['nic_id'] ?? ''));
        $serviceArea = trim((string) ($_POST['service_area'] ?? ''));
        $providerDetails = trim((string) ($_POST['provider_details'] ?? ''));

        if ($drivingLicenseNumber === '' || $nicId === '' || $serviceArea === '' || $providerDetails === '') {
            yamu_redirect_with_message($redirectPath, 'error', 'Please complete all required driver application details');
        }
    }

    if ($role === 'staff') {
        $storeName = trim((string) ($_POST['store_name'] ?? ''));
        $storeOwner = trim((string) ($_POST['store_owner'] ?? ($userRow['full_name'] ?? '')));
        $businessRegistrationNumber = trim((string) ($_POST['business_registration_number'] ?? ''));
        $storeAddress = trim((string) ($_POST['store_address'] ?? ($userRow['address'] ?? '')));
        $storeContactNumber = trim((string) ($_POST['store_contact_number'] ?? ($userRow['phone'] ?? '')));
        $storeEmail = trim((string) ($_POST['store_email'] ?? ($userRow['email'] ?? '')));

        if (
            $storeName === ''
            || $storeOwner === ''
            || $businessRegistrationNumber === ''
            || $storeAddress === ''
            || $storeContactNumber === ''
            || $storeEmail === ''
        ) {
            yamu_redirect_with_message($redirectPath, 'error', 'Please complete all required staff application details');
        }

        if (!filter_var($storeEmail, FILTER_VALIDATE_EMAIL)) {
            yamu_redirect_with_message($redirectPath, 'error', 'Please enter a valid store email address');
        }
    }

    if (!yamu_upsert_user_role_assignment($conn, $currentUserId, $role, $roleStatus, $verificationStatus, false, null, $roleNotes)) {
        yamu_redirect_with_message($redirectPath, 'error', 'Unable to activate role at the moment');
    }

    yamu_ensure_role_profile_row($conn, $currentUserId, $role, $userRow);
    yamu_sync_user_primary_role_snapshot($conn, $currentUserId);

    if ($role === 'driver' && yamu_table_exists($conn, 'driver_profiles')) {
        $drivingLicenseNumber = trim((string) ($_POST['driving_license_number'] ?? ''));
        $licenseExpiryDate = trim((string) ($_POST['license_expiry_date'] ?? ''));
        $nicId = trim((string) ($_POST['nic_id'] ?? ''));
        $serviceArea = trim((string) ($_POST['service_area'] ?? ''));
        $providerDetails = trim((string) ($_POST['provider_details'] ?? ''));
        $hasProviderDetails = yamu_table_has_column($conn, 'driver_profiles', 'provider_details');

        $sql = $hasProviderDetails
            ? 'UPDATE driver_profiles
               SET driving_license_number = ?, license_expiry_date = ?, nic_id = ?, service_area = ?, provider_details = ?, verification_status = ?, verified_at = NULL, updated_at = NOW()
               WHERE user_id = ?'
            : 'UPDATE driver_profiles
               SET driving_license_number = ?, license_expiry_date = ?, nic_id = ?, service_area = ?, verification_status = ?, verified_at = NULL, updated_at = NOW()
               WHERE user_id = ?';
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            if ($hasProviderDetails) {
                $stmt->bind_param('ssssssi', $drivingLicenseNumber, $licenseExpiryDate, $nicId, $serviceArea, $providerDetails, $verificationStatus, $currentUserId);
            } else {
                $stmt->bind_param('sssssi', $drivingLicenseNumber, $licenseExpiryDate, $nicId, $serviceArea, $verificationStatus, $currentUserId);
            }
            $stmt->execute();
            $stmt->close();
        }
    }

    if ($role === 'staff' && yamu_table_exists($conn, 'staff_profiles')) {
        $storeName = trim((string) ($_POST['store_name'] ?? ''));
        $storeOwner = trim((string) ($_POST['store_owner'] ?? ($userRow['full_name'] ?? '')));
        $businessRegistrationNumber = trim((string) ($_POST['business_registration_number'] ?? ''));
        $storeAddress = trim((string) ($_POST['store_address'] ?? ($userRow['address'] ?? '')));
        $storeContactNumber = trim((string) ($_POST['store_contact_number'] ?? ($userRow['phone'] ?? '')));
        $storeEmail = trim((string) ($_POST['store_email'] ?? ($userRow['email'] ?? '')));

        $stmt = $conn->prepare(
            'UPDATE staff_profiles
             SET store_name = ?, store_owner = ?, business_registration_number = ?, store_address = ?, store_contact_number = ?, store_email = ?, verification_status = ?, verified_at = NULL, updated_at = NOW()
             WHERE user_id = ?'
        );

        if ($stmt) {
            $stmt->bind_param('sssssssi', $storeName, $storeOwner, $businessRegistrationNumber, $storeAddress, $storeContactNumber, $storeEmail, $verificationStatus, $currentUserId);
            $stmt->execute();
            $stmt->close();
        }
    }

    yamu_refresh_user_session($conn, $currentUserId, yamu_current_user_role());

    $message = in_array($role, ['driver', 'staff'], true)
        ? yamu_role_label($role) . ' role request submitted. You can complete your onboarding while verification is pending.'
        : yamu_role_label($role) . ' role activated successfully.';

    yamu_redirect_with_message('../role-switch.php', 'msg', $message);
}

yamu_redirect_with_message('../role-switch.php', 'error', 'Invalid role management request');
