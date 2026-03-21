<?php
require_once __DIR__ . '/../../includes/auth.php';
carzo_start_session();
carzo_require_admin('../index.php');
include 'config.php';

function carzo_admin_avatar_upload($currentAvatar = 'avatar.png')
{
    if (!isset($_FILES['profileImage']) || empty($_FILES['profileImage']['name']) || $_FILES['profileImage']['error'] !== UPLOAD_ERR_OK) {
        return $currentAvatar ?: 'avatar.png';
    }

    $uploadedName = basename($_FILES['profileImage']['name']);
    $safeName = preg_replace('/[^A-Za-z0-9._-]/', '', $uploadedName);
    $newName = uniqid() . '_' . $safeName;
    $destination = '../../assets/images/uploads/avatar/' . $newName;

    if (!move_uploaded_file($_FILES['profileImage']['tmp_name'], $destination)) {
        return false;
    }

    return $newName;
}

function carzo_sync_current_admin_session($conn, $userId)
{
    $sessionAdmin = $_SESSION['admin'] ?? [];
    $sessionUserId = (int) ($sessionAdmin['user_id'] ?? 0);
    $sessionEmail = $sessionAdmin['email'] ?? '';
    $updatedUser = carzo_fetch_user_by_id($conn, $userId);

    if (!$updatedUser) {
        return;
    }

    if ($sessionUserId === $userId || ($sessionEmail !== '' && $sessionEmail === $updatedUser['email'])) {
        if (carzo_normalize_role($updatedUser['role'] ?? 'customer') !== 'admin' || carzo_normalize_account_status($updatedUser['account_status'] ?? 'active', 'admin') === 'suspended') {
            unset($_SESSION['admin']);
            return;
        }

        carzo_set_admin_session_from_user($updatedUser);
    }
}

if (isset($_POST['createUser']) || isset($_POST['updateUser'])) {
    $isUpdate = isset($_POST['updateUser']);
    $userId = (int) ($_POST['user_id'] ?? 0);
    $fullName = trim($_POST['fullName']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'] ?? '';
    $role = carzo_normalize_role($_POST['role'] ?? 'customer');
    $accountStatus = carzo_normalize_account_status($_POST['account_status'] ?? ($role === 'driver' ? 'pending' : 'active'), $role);
    $phone = trim($_POST['phone'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $licenseOrNic = trim($_POST['license_or_nic'] ?? '');
    $verificationStatus = carzo_normalize_verification_status($_POST['verification_status'] ?? ($role === 'driver' ? 'pending' : 'verified'), $role);
    $bio = trim($_POST['bio'] ?? '');

    if ($role !== 'driver') {
        $licenseOrNic = null;
        $verificationStatus = 'verified';
        $bio = $bio !== '' ? $bio : null;
    }

    if ($role === 'driver' && $licenseOrNic === '') {
        carzo_redirect_with_message($isUpdate ? '../user-edit.php?user_id=' . $userId : '../user-add.php', 'error', 'Driver accounts require a license number or NIC');
    }

    if ($verificationStatus === 'approved' && $accountStatus === 'pending') {
        $accountStatus = 'active';
    }

    if ($verificationStatus === 'rejected' && $accountStatus !== 'suspended') {
        $accountStatus = 'suspended';
    }

    $duplicateStmt = $conn->prepare('SELECT user_id FROM users WHERE email = ? AND user_id != ? LIMIT 1');

    if ($duplicateStmt) {
        $duplicateStmt->bind_param('si', $email, $userId);
        $duplicateStmt->execute();
        $duplicateResult = $duplicateStmt->get_result();

        if ($duplicateResult && $duplicateResult->num_rows > 0) {
            $duplicateStmt->close();
            carzo_redirect_with_message($isUpdate ? '../user-edit.php?user_id=' . $userId : '../user-add.php', 'error', 'Email Id Already Exists');
        }

        $duplicateStmt->close();
    }

    if (!$isUpdate && $password === '') {
        carzo_redirect_with_message('../user-add.php', 'error', 'Password is required for new users');
    }

    $currentAvatar = 'avatar.png';

    if ($isUpdate) {
        $existingUser = carzo_fetch_user_by_id($conn, $userId);

        if (!$existingUser) {
            carzo_redirect_with_message('../users.php', 'error', 'User not found');
        }

        $currentAvatar = $existingUser['profile_pic'] ?? 'avatar.png';
    }

    $avatarName = carzo_admin_avatar_upload($currentAvatar);

    if ($avatarName === false) {
        carzo_redirect_with_message($isUpdate ? '../user-edit.php?user_id=' . $userId : '../user-add.php', 'error', 'Failed to upload profile image');
    }

    if ($isUpdate) {
        if ($password !== '') {
            $hashedPassword = carzo_hash_password($password);
            $stmt = $conn->prepare(
                'UPDATE users
                 SET username = ?, password = ?, role = ?, full_name = ?, email = ?, address = ?, city = ?, phone = ?, dob = ?, profile_pic = ?, account_status = ?, license_or_nic = ?, verification_status = ?, bio = ?, updated_at = NOW()
                 WHERE user_id = ?'
            );

            if (!$stmt) {
                carzo_redirect_with_message('../user-edit.php?user_id=' . $userId, 'error', 'User update failed');
            }

            $stmt->bind_param(
                'ssssssssssssssi',
                $username,
                $hashedPassword,
                $role,
                $fullName,
                $email,
                $address,
                $city,
                $phone,
                $dob,
                $avatarName,
                $accountStatus,
                $licenseOrNic,
                $verificationStatus,
                $bio,
                $userId
            );
        } else {
            $stmt = $conn->prepare(
                'UPDATE users
                 SET username = ?, role = ?, full_name = ?, email = ?, address = ?, city = ?, phone = ?, dob = ?, profile_pic = ?, account_status = ?, license_or_nic = ?, verification_status = ?, bio = ?, updated_at = NOW()
                 WHERE user_id = ?'
            );

            if (!$stmt) {
                carzo_redirect_with_message('../user-edit.php?user_id=' . $userId, 'error', 'User update failed');
            }

            $stmt->bind_param(
                'sssssssssssssi',
                $username,
                $role,
                $fullName,
                $email,
                $address,
                $city,
                $phone,
                $dob,
                $avatarName,
                $accountStatus,
                $licenseOrNic,
                $verificationStatus,
                $bio,
                $userId
            );
        }

        if (!$stmt->execute()) {
            $stmt->close();
            carzo_redirect_with_message('../user-edit.php?user_id=' . $userId, 'error', 'User update failed');
        }

        $stmt->close();
        carzo_sync_current_admin_session($conn, $userId);
        carzo_redirect_with_message('../users.php', 'msg', 'User updated successfully');
    }

    $hashedPassword = carzo_hash_password($password);
    $stmt = $conn->prepare(
        'INSERT INTO users (username, password, role, full_name, email, address, city, phone, dob, profile_pic, account_status, rag_date, license_or_nic, verification_status, bio, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, NOW(), NOW())'
    );

    if (!$stmt) {
        carzo_redirect_with_message('../user-add.php', 'error', 'User creation failed');
    }

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
        $dob,
        $avatarName,
        $accountStatus,
        $licenseOrNic,
        $verificationStatus,
        $bio
    );

    if (!$stmt->execute()) {
        $stmt->close();
        carzo_redirect_with_message('../user-add.php', 'error', 'User creation failed');
    }

    $stmt->close();
    carzo_redirect_with_message('../users.php', 'msg', 'User created successfully');
}

if (isset($_GET['action'], $_GET['user_id'])) {
    $action = $_GET['action'];
    $userId = (int) $_GET['user_id'];
    $user = carzo_fetch_user_by_id($conn, $userId);

    if (!$user) {
        carzo_redirect_with_message('../users.php', 'error', 'User not found');
    }

    $currentAdminUserId = (int) ($_SESSION['admin']['user_id'] ?? 0);
    $currentAdminEmail = $_SESSION['admin']['email'] ?? '';

    if ($action === 'delete') {
        if ($currentAdminUserId === $userId || ($currentAdminEmail !== '' && $currentAdminEmail === $user['email'])) {
            carzo_redirect_with_message('../users.php', 'error', 'You cannot delete the account you are currently using');
        }

        $stmt = $conn->prepare('DELETE FROM users WHERE user_id = ?');
        $stmt->bind_param('i', $userId);

        if (!$stmt->execute()) {
            $stmt->close();
            carzo_redirect_with_message('../users.php', 'error', 'Unable to delete user');
        }

        $stmt->close();
        carzo_redirect_with_message('../users.php', 'msg', 'User deleted successfully');
    }

    if ($action === 'activate' || $action === 'suspend') {
        $newStatus = $action === 'activate' ? 'active' : 'suspended';
        $stmt = $conn->prepare('UPDATE users SET account_status = ?, updated_at = NOW() WHERE user_id = ?');
        $stmt->bind_param('si', $newStatus, $userId);

        if (!$stmt->execute()) {
            $stmt->close();
            carzo_redirect_with_message('../users.php', 'error', 'Unable to update account status');
        }

        $stmt->close();
        carzo_sync_current_admin_session($conn, $userId);
        carzo_redirect_with_message('../users.php', 'msg', 'Account status updated successfully');
    }

    if ($action === 'approve-driver' || $action === 'reject-driver') {
        if (carzo_normalize_role($user['role'] ?? 'customer') !== 'driver') {
            carzo_redirect_with_message('../users.php', 'error', 'Only driver accounts can be reviewed from this action');
        }

        $newVerificationStatus = $action === 'approve-driver' ? 'approved' : 'rejected';
        $newAccountStatus = $action === 'approve-driver' ? 'active' : 'suspended';

        $stmt = $conn->prepare('UPDATE users SET verification_status = ?, account_status = ?, updated_at = NOW() WHERE user_id = ?');
        $stmt->bind_param('ssi', $newVerificationStatus, $newAccountStatus, $userId);

        if (!$stmt->execute()) {
            $stmt->close();
            carzo_redirect_with_message('../users.php', 'error', 'Unable to update driver verification');
        }

        $stmt->close();
        carzo_sync_current_admin_session($conn, $userId);
        carzo_redirect_with_message('../users.php', 'msg', 'Driver account updated successfully');
    }
}
