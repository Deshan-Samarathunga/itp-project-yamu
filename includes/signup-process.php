<?php
require_once __DIR__ . '/auth.php';
carzo_start_session();
include 'config.php';

if (isset($_POST['signup'])) {
    $fullName = trim($_POST['fullName']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['conPassword'];
    $role = carzo_normalize_role($_POST['role'] ?? 'customer');
    $phone = trim($_POST['phone'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $licenseOrNic = trim($_POST['license_or_nic'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

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

    $accountStatus = $role === 'driver' ? 'pending' : 'active';
    $verificationStatus = $role === 'driver' ? 'pending' : 'verified';
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
        'sssssssssssss',
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

    if ($role === 'driver') {
        carzo_redirect_with_message('../signin.php', 'msg', 'Driver account created. Please wait for admin approval before posting vehicles.');
    }

    carzo_set_user_session($newUser);
    carzo_redirect_with_message('../index.php', 'msg', 'Registration Successful');
}
