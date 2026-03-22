<?php
require_once __DIR__ . '/auth.php';
yamu_start_session();
yamu_require_authenticated_user('../signin.php');
include 'config.php';

if (!isset($_POST['updateProfile'])) {
    yamu_redirect_with_message('../edit-profile.php', 'error', 'Invalid profile update request');
}

$sessionUserId = (int) ($_SESSION['user']['user_ID'] ?? 0);
$activeRole = yamu_current_user_role();
$currentUser = yamu_fetch_user_by_id($conn, $sessionUserId);

if ($sessionUserId <= 0 || !$currentUser) {
    yamu_redirect_with_message('../signin.php', 'error', 'Please sign in to continue');
}

$fullName = trim((string) ($_POST['fullName'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$username = trim((string) ($_POST['username'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$dob = trim((string) ($_POST['dob'] ?? ''));
$address = trim((string) ($_POST['address'] ?? ''));
$city = trim((string) ($_POST['city'] ?? ''));

if ($fullName === '' || $email === '' || $username === '') {
    yamu_redirect_with_message('../edit-profile.php', 'error', 'Please complete the required profile fields');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    yamu_redirect_with_message('../edit-profile.php', 'error', 'Please enter a valid email address');
}

$duplicateEmailStmt = $conn->prepare('SELECT user_id FROM users WHERE email = ? AND user_id != ? LIMIT 1');
if ($duplicateEmailStmt) {
    $duplicateEmailStmt->bind_param('si', $email, $sessionUserId);
    $duplicateEmailStmt->execute();
    $duplicateEmailResult = $duplicateEmailStmt->get_result();

    if ($duplicateEmailResult && $duplicateEmailResult->num_rows > 0) {
        $duplicateEmailStmt->close();
        yamu_redirect_with_message('../edit-profile.php', 'error', 'Email address already exists');
    }

    $duplicateEmailStmt->close();
}

$duplicateUsernameStmt = $conn->prepare('SELECT user_id FROM users WHERE username = ? AND user_id != ? LIMIT 1');
if ($duplicateUsernameStmt) {
    $duplicateUsernameStmt->bind_param('si', $username, $sessionUserId);
    $duplicateUsernameStmt->execute();
    $duplicateUsernameResult = $duplicateUsernameStmt->get_result();

    if ($duplicateUsernameResult && $duplicateUsernameResult->num_rows > 0) {
        $duplicateUsernameStmt->close();
        yamu_redirect_with_message('../edit-profile.php', 'error', 'Username already exists');
    }

    $duplicateUsernameStmt->close();
}

$profileImageName = $currentUser['profile_pic'] ?? 'avatar.png';

if (isset($_FILES['profileImage']) && !empty($_FILES['profileImage']['name']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
    $uploadedName = basename((string) $_FILES['profileImage']['name']);
    $safeName = preg_replace('/[^A-Za-z0-9._-]/', '', $uploadedName);
    $profileImageName = uniqid('avatar_', true) . '_' . $safeName;
    $profileDirectory = '../assets/images/uploads/avatar/';
    $profileImageDestination = $profileDirectory . $profileImageName;

    if (!is_dir($profileDirectory)) {
        mkdir($profileDirectory, 0777, true);
    }

    if (!move_uploaded_file($_FILES['profileImage']['tmp_name'], $profileImageDestination)) {
        yamu_redirect_with_message('../edit-profile.php', 'error', 'Failed to upload the profile image');
    }
}

$stmt = $conn->prepare(
    'UPDATE users
     SET username = ?, full_name = ?, email = ?, address = ?, city = ?, phone = ?, dob = ?, profile_pic = ?, updated_at = NOW()
     WHERE user_id = ?'
);

if (!$stmt) {
    yamu_redirect_with_message('../edit-profile.php', 'error', 'Profile update failed');
}

$stmt->bind_param(
    'ssssssssi',
    $username,
    $fullName,
    $email,
    $address,
    $city,
    $phone,
    $dob,
    $profileImageName,
    $sessionUserId
);

if (!$stmt->execute()) {
    $stmt->close();
    yamu_redirect_with_message('../edit-profile.php', 'error', 'Profile update failed');
}

$stmt->close();

$updatedUser = yamu_fetch_user_by_id($conn, $sessionUserId);

if ($updatedUser) {
    yamu_set_user_session($updatedUser, $conn, $activeRole);
}

yamu_redirect_with_message('../my-profile.php', 'msg', 'Profile updated successfully');
