<?php
require_once __DIR__ . '/auth.php';
carzo_start_session();
include 'config.php';

if (isset($_POST['signup'])) {
    $fullName = trim((string) ($_POST['fullName'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['conPassword'] ?? '');
    $role = carzo_normalize_role($_POST['role'] ?? 'customer');
    $phone = trim($_POST['phone'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $licenseOrNic = trim($_POST['license_or_nic'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    if ($fullName === '' || $email === '' || $username === '' || $password === '') {
        carzo_redirect_with_message('../signup.php', 'error', 'Please fill in all required fields');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        carzo_redirect_with_message('../signup.php', 'error', 'Please enter a valid email address');
    }

    if (strlen($password) < 8) {
        carzo_redirect_with_message('../signup.php', 'error', 'Password must contain at least 8 characters');
    }

    if ($role === 'admin') {
        $role = 'customer';
    }

    if ($password !== $confirmPassword) {
        carzo_redirect_with_message('../signup.php', 'error', 'Password confirmation failed');
    }

    if ($role === 'driver' && $licenseOrNic === '') {
        carzo_redirect_with_message('../signup.php', 'error', 'Driver registration requires a license number or NIC');
    }

    if (carzo_fetch_user_by_email($conn, $email)) {
        carzo_redirect_with_message('../signup.php', 'error', 'Email Id Already Exists');
    }

    $usernameStmt = $conn->prepare('SELECT user_id FROM users WHERE username = ? LIMIT 1');
    if ($usernameStmt) {
        $usernameStmt->bind_param('s', $username);
        $usernameStmt->execute();
        $usernameResult = $usernameStmt->get_result();
        if ($usernameResult && $usernameResult->num_rows > 0) {
            $usernameStmt->close();
            carzo_redirect_with_message('../signup.php', 'error', 'Username already exists');
        }
        $usernameStmt->close();
    }

    $accountStatus = carzo_default_account_status_for_role($role);
    $verificationStatus = carzo_default_verification_status_for_role($role);
    $hashedPassword = carzo_hash_password($password);
    $defaultAvatar = 'avatar.png';

    $stmt = $conn->prepare(
        'INSERT INTO users (username, password, role, full_name, email, address, city, phone, dob, profile_pic, account_status, rag_date, license_or_nic, verification_status, bio, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, NOW(), NOW())'
    );

    if (!$stmt) {
        carzo_redirect_with_message('../signup.php', 'error', 'Registration Failed');
    }

    $emptyDob = '';
    $stmt->bind_param(
        'ssssssssssssss',
        $username,
        $hashedPassword,
        $role,
        $fullName,
        $email,
        $address,
        $city,
        $phone,
        $emptyDob,
        $defaultAvatar,
        $accountStatus,
        $licenseOrNic,
        $verificationStatus,
        $bio
    );

    if (!$stmt->execute()) {
        $stmt->close();
        carzo_redirect_with_message('../signup.php', 'error', 'Registration Failed');
    }

    $newUserId = $stmt->insert_id;
    $stmt->close();

    $newUser = carzo_fetch_user_by_id($conn, $newUserId);

    if (!$newUser) {
        carzo_redirect_with_message('../signup.php', 'error', 'Registration Failed');
    }

    if (carzo_table_exists($conn, 'user_roles')) {
        $sessionAdminId = (int) ($_SESSION['admin']['user_id'] ?? 0);
        carzo_upsert_user_role_assignment($conn, $newUserId, $role, $accountStatus, $verificationStatus, true, $sessionAdminId ?: null, 'Created during self-registration');
        carzo_ensure_role_profile_row($conn, $newUserId, $role, $newUser);

        if ($role !== 'customer') {
            carzo_upsert_user_role_assignment($conn, $newUserId, 'customer', 'active', 'verified', false, $sessionAdminId ?: null, 'Default customer role on registration');
            carzo_ensure_role_profile_row($conn, $newUserId, 'customer', $newUser);
        }
    }

    $sessionUser = carzo_set_user_session($newUser, $conn, $role);

    if (!empty($sessionUser['roles']) && count((array) $sessionUser['roles']) > 1) {
        carzo_redirect_with_message('../choose-role.php', 'msg', 'Registration successful. Select your active role to continue.');
    }

    $redirectPath = carzo_public_home_path_for_role($sessionUser['active_role'] ?? $role);
    carzo_redirect_with_message('../' . $redirectPath, 'msg', 'Registration Successful');
}
