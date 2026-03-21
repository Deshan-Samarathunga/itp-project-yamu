<?php
require_once __DIR__ . '/auth.php';
carzo_start_session();
include 'config.php';

if (!carzo_is_user_authenticated()) {
    carzo_redirect_with_message('../signin.php', 'error', 'Please sign in to continue');
}

$userId = (int) ($_SESSION['user']['user_ID'] ?? 0);
$activeRole = carzo_current_user_role();

function carzo_role_profile_redirect($role)
{
    $map = [
        'customer' => '../customer-profile.php',
        'driver' => '../driver-profile.php',
        'staff' => '../staff-profile.php',
        'admin' => '../admin-profile.php',
    ];

    return $map[$role] ?? '../my-profile.php';
}

if (isset($_POST['updateCustomerProfile'])) {
    if (!carzo_current_user_has_assigned_role('customer')) {
        carzo_redirect_with_message('../access-denied.php', 'error', 'Customer role is not assigned to your account');
    }

    carzo_ensure_role_profile_row($conn, $userId, 'customer');
    $preferences = trim((string) ($_POST['preferences'] ?? ''));

    if (carzo_table_exists($conn, 'customer_profiles')) {
        $stmt = $conn->prepare('UPDATE customer_profiles SET preferences = ?, updated_at = NOW() WHERE user_id = ?');
        if ($stmt) {
            $stmt->bind_param('si', $preferences, $userId);
            $stmt->execute();
            $stmt->close();
        }
    }

    carzo_refresh_user_session($conn, $userId, $activeRole);
    carzo_redirect_with_message(carzo_role_profile_redirect('customer'), 'msg', 'Customer profile updated successfully');
}

if (isset($_POST['updateDriverProfile'])) {
    if (!carzo_current_user_has_assigned_role('driver')) {
        carzo_redirect_with_message('../access-denied.php', 'error', 'Driver role is not assigned to your account');
    }

    carzo_ensure_role_profile_row($conn, $userId, 'driver');
    $drivingLicenseNumber = trim((string) ($_POST['driving_license_number'] ?? ''));
    $licenseExpiryDate = trim((string) ($_POST['license_expiry_date'] ?? ''));
    $nicId = trim((string) ($_POST['nic_id'] ?? ''));
    $serviceArea = trim((string) ($_POST['service_area'] ?? ''));

    if ($drivingLicenseNumber === '') {
        carzo_redirect_with_message(carzo_role_profile_redirect('driver'), 'error', 'Driving license number is required');
    }

    if (carzo_table_exists($conn, 'driver_profiles')) {
        $stmt = $conn->prepare(
            'UPDATE driver_profiles
             SET driving_license_number = ?, license_expiry_date = ?, nic_id = ?, service_area = ?, updated_at = NOW()
             WHERE user_id = ?'
        );

        if ($stmt) {
            $stmt->bind_param('ssssi', $drivingLicenseNumber, $licenseExpiryDate, $nicId, $serviceArea, $userId);
            $stmt->execute();
            $stmt->close();
        }
    }

    $userLicenseOrNic = $nicId !== '' ? $nicId : $drivingLicenseNumber;
    $userStmt = $conn->prepare('UPDATE users SET license_or_nic = ?, updated_at = NOW() WHERE user_id = ?');
    if ($userStmt) {
        $userStmt->bind_param('si', $userLicenseOrNic, $userId);
        $userStmt->execute();
        $userStmt->close();
    }

    carzo_refresh_user_session($conn, $userId, $activeRole);
    carzo_redirect_with_message(carzo_role_profile_redirect('driver'), 'msg', 'Driver profile updated successfully');
}

if (isset($_POST['updateStaffProfile'])) {
    if (!carzo_current_user_has_assigned_role('staff')) {
        carzo_redirect_with_message('../access-denied.php', 'error', 'Staff role is not assigned to your account');
    }

    carzo_ensure_role_profile_row($conn, $userId, 'staff');
    $storeName = trim((string) ($_POST['store_name'] ?? ''));
    $storeOwner = trim((string) ($_POST['store_owner'] ?? ''));
    $businessRegistrationNumber = trim((string) ($_POST['business_registration_number'] ?? ''));
    $storeAddress = trim((string) ($_POST['store_address'] ?? ''));
    $storeContactNumber = trim((string) ($_POST['store_contact_number'] ?? ''));
    $storeEmail = trim((string) ($_POST['store_email'] ?? ''));

    if ($storeName === '') {
        carzo_redirect_with_message(carzo_role_profile_redirect('staff'), 'error', 'Store name is required');
    }

    if ($storeEmail !== '' && !filter_var($storeEmail, FILTER_VALIDATE_EMAIL)) {
        carzo_redirect_with_message(carzo_role_profile_redirect('staff'), 'error', 'Please enter a valid store email address');
    }

    if (carzo_table_exists($conn, 'staff_profiles')) {
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

    carzo_refresh_user_session($conn, $userId, $activeRole);
    carzo_redirect_with_message(carzo_role_profile_redirect('staff'), 'msg', 'Staff profile updated successfully');
}

if (isset($_POST['updateAdminProfile'])) {
    if (!carzo_current_user_has_assigned_role('admin')) {
        carzo_redirect_with_message('../access-denied.php', 'error', 'Admin role is not assigned to your account');
    }

    carzo_ensure_role_profile_row($conn, $userId, 'admin');
    $systemPermissions = trim((string) ($_POST['system_permissions'] ?? 'all'));

    if (carzo_table_exists($conn, 'admin_profiles')) {
        $stmt = $conn->prepare('UPDATE admin_profiles SET system_permissions = ?, updated_at = NOW() WHERE user_id = ?');
        if ($stmt) {
            $stmt->bind_param('si', $systemPermissions, $userId);
            $stmt->execute();
            $stmt->close();
        }
    }

    carzo_refresh_user_session($conn, $userId, $activeRole);
    carzo_redirect_with_message(carzo_role_profile_redirect('admin'), 'msg', 'Admin profile updated successfully');
}

carzo_redirect_with_message('../my-profile.php', 'error', 'Invalid role profile request');
