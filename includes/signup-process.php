<?php
require_once __DIR__ . '/auth.php';
yamu_start_session();
include 'config.php';

if (isset($_POST['signup'])) {
    yamu_ensure_users_password_column($conn);

    $fullName = trim((string) ($_POST['fullName'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['conPassword'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $role = 'customer';
    $licenseOrNic = '';
    $bio = '';

    if ($fullName === '' || $email === '' || $username === '' || $password === '') {
        yamu_redirect_with_message('../signup.php', 'error', 'Please fill in all required fields');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        yamu_redirect_with_message('../signup.php', 'error', 'Please enter a valid email address');
    }

    if (strlen($password) < 8) {
        yamu_redirect_with_message('../signup.php', 'error', 'Password must contain at least 8 characters');
    }

    if ($password !== $confirmPassword) {
        yamu_redirect_with_message('../signup.php', 'error', 'Password confirmation failed');
    }

    if (yamu_fetch_user_by_email($conn, $email)) {
        yamu_redirect_with_message('../signup.php', 'error', 'Email Id Already Exists');
    }

    $usernameStmt = $conn->prepare('SELECT user_id FROM users WHERE username = ? LIMIT 1');
    if ($usernameStmt) {
        $usernameStmt->bind_param('s', $username);
        $usernameStmt->execute();
        $usernameResult = $usernameStmt->get_result();
        if ($usernameResult && $usernameResult->num_rows > 0) {
            $usernameStmt->close();
            yamu_redirect_with_message('../signup.php', 'error', 'Username already exists');
        }
        $usernameStmt->close();
    }

    $accountStatus = yamu_default_account_status_for_role($role);
    $verificationStatus = yamu_default_verification_status_for_role($role);
    $hashedPassword = yamu_hash_password($password);
    $defaultAvatar = 'avatar.png';

    $stmt = $conn->prepare(
        'INSERT INTO users (username, password, role, full_name, email, address, city, phone, dob, profile_pic, account_status, rag_date, license_or_nic, verification_status, bio, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, NOW(), NOW())'
    );

    if (!$stmt) {
        yamu_redirect_with_message('../signup.php', 'error', 'Registration Failed');
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
        yamu_redirect_with_message('../signup.php', 'error', 'Registration Failed');
    }

    $newUserId = $stmt->insert_id;
    $stmt->close();

    $newUser = yamu_fetch_user_by_id($conn, $newUserId);

    if (!$newUser) {
        yamu_redirect_with_message('../signup.php', 'error', 'Registration Failed');
    }

    if (yamu_table_exists($conn, 'user_roles')) {
        $sessionAdminId = (int) ($_SESSION['admin']['user_id'] ?? 0);
        yamu_upsert_user_role_assignment($conn, $newUserId, $role, $accountStatus, $verificationStatus, true, $sessionAdminId ?: null, 'Created during self-registration');
        yamu_ensure_role_profile_row($conn, $newUserId, $role, $newUser);

        yamu_sync_user_primary_role_snapshot($conn, $newUserId);
    }

    $sessionUser = yamu_set_user_session($newUser, $conn, 'customer');
    $redirectPath = yamu_public_home_path_for_role($sessionUser['active_role'] ?? 'customer');
    yamu_redirect_with_message('../' . $redirectPath, 'msg', 'Registration Successful');
}
