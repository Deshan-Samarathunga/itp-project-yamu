<?php
require_once __DIR__ . '/auth.php';
yamu_start_session();
yamu_require_authenticated_user('../signin.php');
include 'config.php';

$userId = (int) ($_SESSION['user']['user_ID'] ?? 0);
$activeRole = yamu_current_user_role();

function yamu_role_profile_redirect($role)
{
    $map = [
        'customer' => '../customer-profile.php',
        'driver' => '../driver-profile.php',
        'staff' => '../staff-profile.php',
        'admin' => '../admin-profile.php',
    ];

    return $map[$role] ?? '../my-profile.php';
}

function yamu_require_self_role_profile_access($role)
{
    yamu_require_assigned_user_role([$role], '../signin.php', ['active', 'pending', 'verified'], '../access-denied.php');
}

if ($userId <= 0) {
    yamu_redirect_with_message('../signin.php', 'error', 'Please sign in to continue');
}

if (isset($_POST['updateCustomerProfile'])) {
    yamu_require_self_role_profile_access('customer');
    yamu_ensure_role_profile_row($conn, $userId, 'customer');

    $preferences = trim((string) ($_POST['preferences'] ?? ''));

    if (yamu_table_exists($conn, 'customer_profiles')) {
        $stmt = $conn->prepare('UPDATE customer_profiles SET preferences = ?, updated_at = NOW() WHERE user_id = ?');
        if ($stmt) {
            $stmt->bind_param('si', $preferences, $userId);
            $stmt->execute();
            $stmt->close();
        }
    }

    yamu_refresh_user_session($conn, $userId, $activeRole);
    yamu_redirect_with_message(yamu_role_profile_redirect('customer'), 'msg', 'Customer profile updated successfully');
}

if (isset($_POST['updateDriverProfile'])) {
    yamu_require_self_role_profile_access('driver');
    yamu_ensure_role_profile_row($conn, $userId, 'driver');

    $drivingLicenseNumber = trim((string) ($_POST['driving_license_number'] ?? ''));
    $licenseExpiryDate = trim((string) ($_POST['license_expiry_date'] ?? ''));
    $nicId = trim((string) ($_POST['nic_id'] ?? ''));
    $serviceArea = trim((string) ($_POST['service_area'] ?? ''));
    $providerDetails = trim((string) ($_POST['provider_details'] ?? ''));

    if ($drivingLicenseNumber === '' || $nicId === '' || $serviceArea === '' || $providerDetails === '') {
        yamu_redirect_with_message(yamu_role_profile_redirect('driver'), 'error', 'Please complete all required driver profile fields');
    }

    if (yamu_table_exists($conn, 'driver_profiles')) {
        $hasProviderDetails = yamu_table_has_column($conn, 'driver_profiles', 'provider_details');
        $sql = $hasProviderDetails
            ? 'UPDATE driver_profiles
               SET driving_license_number = ?, license_expiry_date = ?, nic_id = ?, service_area = ?, provider_details = ?, updated_at = NOW()
               WHERE user_id = ?'
            : 'UPDATE driver_profiles
               SET driving_license_number = ?, license_expiry_date = ?, nic_id = ?, service_area = ?, updated_at = NOW()
               WHERE user_id = ?';
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            if ($hasProviderDetails) {
                $stmt->bind_param('sssssi', $drivingLicenseNumber, $licenseExpiryDate, $nicId, $serviceArea, $providerDetails, $userId);
            } else {
                $stmt->bind_param('ssssi', $drivingLicenseNumber, $licenseExpiryDate, $nicId, $serviceArea, $userId);
            }
            $stmt->execute();
            $stmt->close();
        }
    }

    yamu_refresh_user_session($conn, $userId, $activeRole);
    yamu_redirect_with_message(yamu_role_profile_redirect('driver'), 'msg', 'Driver profile updated successfully');
}

if (isset($_POST['updateStaffProfile'])) {
    yamu_require_self_role_profile_access('staff');
    yamu_ensure_role_profile_row($conn, $userId, 'staff');

    $storeName = trim((string) ($_POST['store_name'] ?? ''));
    $storeOwner = trim((string) ($_POST['store_owner'] ?? ''));
    $businessRegistrationNumber = trim((string) ($_POST['business_registration_number'] ?? ''));
    $storeAddress = trim((string) ($_POST['store_address'] ?? ''));
    $storeContactNumber = trim((string) ($_POST['store_contact_number'] ?? ''));
    $storeEmail = trim((string) ($_POST['store_email'] ?? ''));

    if (
        $storeName === ''
        || $storeOwner === ''
        || $businessRegistrationNumber === ''
        || $storeAddress === ''
        || $storeContactNumber === ''
        || $storeEmail === ''
    ) {
        yamu_redirect_with_message(yamu_role_profile_redirect('staff'), 'error', 'Please complete all required staff profile fields');
    }

    if (!filter_var($storeEmail, FILTER_VALIDATE_EMAIL)) {
        yamu_redirect_with_message(yamu_role_profile_redirect('staff'), 'error', 'Please enter a valid store email address');
    }

    if (yamu_table_exists($conn, 'staff_profiles')) {
        $stmt = $conn->prepare(
            'UPDATE staff_profiles
             SET store_name = ?, store_owner = ?, business_registration_number = ?, store_address = ?, store_contact_number = ?, store_email = ?, updated_at = NOW()
             WHERE user_id = ?'
        );

        if ($stmt) {
            $stmt->bind_param('ssssssi', $storeName, $storeOwner, $businessRegistrationNumber, $storeAddress, $storeContactNumber, $storeEmail, $userId);
            $stmt->execute();
            $stmt->close();
        }
    }

    yamu_refresh_user_session($conn, $userId, $activeRole);
    yamu_redirect_with_message(yamu_role_profile_redirect('staff'), 'msg', 'Staff profile updated successfully');
}

if (isset($_POST['updateAdminProfile'])) {
    yamu_require_user_roles(['admin'], '../signin.php', ['active', 'verified'], '../access-denied.php');
    yamu_ensure_role_profile_row($conn, $userId, 'admin');

    $systemPermissions = trim((string) ($_POST['system_permissions'] ?? 'all'));
    if ($systemPermissions === '') {
        $systemPermissions = 'all';
    }

    if (yamu_table_exists($conn, 'admin_profiles')) {
        $stmt = $conn->prepare('UPDATE admin_profiles SET system_permissions = ?, updated_at = NOW() WHERE user_id = ?');
        if ($stmt) {
            $stmt->bind_param('si', $systemPermissions, $userId);
            $stmt->execute();
            $stmt->close();
        }
    }

    yamu_refresh_user_session($conn, $userId, $activeRole);
    yamu_redirect_with_message(yamu_role_profile_redirect('admin'), 'msg', 'Admin profile updated successfully');
}

yamu_redirect_with_message('../my-profile.php', 'error', 'Invalid role profile request');
