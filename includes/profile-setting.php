<?php
require_once __DIR__ . '/auth.php';
carzo_start_session();
carzo_require_user_roles(['customer', 'driver'], '../signin.php', ['active', 'pending'], '../index.php');
include 'config.php';

if (isset($_POST['updateProfile'])) {
    $sessionUserId = (int) ($_SESSION['user']['user_ID'] ?? 0);
    $userId = (int) ($_POST['userID'] ?? 0);

    if ($sessionUserId !== $userId) {
        carzo_redirect_with_message('../profile.php', 'error', 'You can only update your own profile');
    }

    $currentUser = carzo_fetch_user_by_id($conn, $userId);

    if (!$currentUser) {
        carzo_redirect_with_message('../profile.php', 'error', 'User not found');
    }

    $fullName = trim($_POST['fullName']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $role = carzo_normalize_role($currentUser['role'] ?? 'customer');
    $licenseOrNic = $role === 'driver' ? trim($_POST['license_or_nic'] ?? '') : ($currentUser['license_or_nic'] ?? null);
    $bio = $role === 'driver' ? trim($_POST['bio'] ?? '') : ($currentUser['bio'] ?? null);

    if ($role === 'driver' && $licenseOrNic === '') {
        carzo_redirect_with_message('../profile.php', 'error', 'Driver accounts require a license number or NIC');
    }

    $duplicateStmt = $conn->prepare('SELECT user_id FROM users WHERE email = ? AND user_id != ? LIMIT 1');

    if ($duplicateStmt) {
        $duplicateStmt->bind_param('si', $email, $userId);
        $duplicateStmt->execute();
        $duplicateResult = $duplicateStmt->get_result();

        if ($duplicateResult && $duplicateResult->num_rows > 0) {
            $duplicateStmt->close();
            carzo_redirect_with_message('../profile.php', 'error', 'Email Id Already Exists');
        }

        $duplicateStmt->close();
    }

    $profileImageName = $currentUser['profile_pic'] ?? 'avatar.png';

    if (isset($_FILES['profileImage']) && !empty($_FILES['profileImage']['name']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        $uploadedName = basename($_FILES['profileImage']['name']);
        $profileImageName = uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '', $uploadedName);
        $profileImageDestination = '../assets/images/uploads/avatar/' . $profileImageName;

        if (!move_uploaded_file($_FILES['profileImage']['tmp_name'], $profileImageDestination)) {
            carzo_redirect_with_message('../profile.php', 'error', 'Failed to upload the profile image');
        }
    }

    $stmt = $conn->prepare(
        'UPDATE users
         SET username = ?, full_name = ?, email = ?, address = ?, city = ?, phone = ?, dob = ?, profile_pic = ?, license_or_nic = ?, bio = ?, updated_at = NOW()
         WHERE user_id = ?'
    );

    if (!$stmt) {
        carzo_redirect_with_message('../profile.php', 'error', 'Profile update failed');
    }

    $stmt->bind_param(
        'ssssssssssi',
        $username,
        $fullName,
        $email,
        $address,
        $city,
        $phone,
        $dob,
        $profileImageName,
        $licenseOrNic,
        $bio,
        $userId
    );

    if (!$stmt->execute()) {
        $stmt->close();
        carzo_redirect_with_message('../profile.php', 'error', 'Profile update failed');
    }

    $stmt->close();

    $updatedUser = carzo_fetch_user_by_id($conn, $userId);

    if ($updatedUser) {
        carzo_set_user_session($updatedUser);
    }

    carzo_redirect_with_message('../profile.php', 'msg', 'Profile Updated Successfully');
}
