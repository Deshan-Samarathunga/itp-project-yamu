<?php
require_once __DIR__ . '/../../includes/auth.php';
yamu_start_session();
yamu_require_admin('../index.php', '../access-denied.php');
include 'config.php';
yamu_ensure_users_password_column($conn);

function yamu_admin_avatar_upload($currentAvatar = 'avatar.png')
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

function yamu_sync_current_admin_session($conn, $userId)
{
    $sessionAdmin = $_SESSION['admin'] ?? [];
    $sessionUserId = (int) ($sessionAdmin['user_id'] ?? 0);
    $sessionEmail = $sessionAdmin['email'] ?? '';
    $updatedUser = yamu_fetch_user_by_id($conn, $userId);

    if (!$updatedUser) {
        return;
    }

    if ($sessionUserId === $userId || ($sessionEmail !== '' && $sessionEmail === $updatedUser['email'])) {
        $assignments = yamu_fetch_user_roles(
            $conn,
            $userId,
            $updatedUser['role'] ?? 'customer',
            $updatedUser['account_status'] ?? 'active',
            $updatedUser['verification_status'] ?? 'verified'
        );
        $adminAssignment = $assignments['admin'] ?? null;

        if (!$adminAssignment || !yamu_role_allows_standard_status($adminAssignment['role_status'] ?? 'active')) {
            unset($_SESSION['admin']);
            return;
        }

        $adminSession = yamu_build_admin_session_from_user(
            $updatedUser,
            'admin',
            $adminAssignment['role_status'] ?? 'active',
            $adminAssignment['verification_status'] ?? 'verified'
        );

        if ($adminSession) {
            $_SESSION['admin'] = $adminSession;
        } else {
            unset($_SESSION['admin']);
        }
    }
}

if (isset($_POST['createUser']) || isset($_POST['updateUser'])) {
    $isUpdate = isset($_POST['updateUser']);
    $userId = (int) ($_POST['user_id'] ?? 0);
    $fullName = trim($_POST['fullName']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'] ?? '';
    $role = yamu_normalize_role($_POST['role'] ?? 'customer');
    $accountStatus = yamu_normalize_account_status($_POST['account_status'] ?? yamu_default_account_status_for_role($role), $role);
    $phone = trim($_POST['phone'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $licenseOrNic = trim($_POST['license_or_nic'] ?? '');
    $verificationStatus = yamu_normalize_verification_status($_POST['verification_status'] ?? yamu_default_verification_status_for_role($role), $role);
    $bio = trim($_POST['bio'] ?? '');
    $existingUser = null;
    $existingAssignments = [];

    if ($role === 'admin' && !$isUpdate) {
        yamu_redirect_with_message('../user-add.php', 'error', 'Admin accounts must be seeded directly in the database');
    }

    if ($isUpdate) {
        $existingUser = yamu_fetch_user_by_id($conn, $userId);

        if (!$existingUser) {
            yamu_redirect_with_message('../users.php', 'error', 'User not found');
        }

        $existingAssignments = yamu_fetch_user_roles(
            $conn,
            $userId,
            $existingUser['role'] ?? 'customer',
            $existingUser['account_status'] ?? 'active',
            $existingUser['verification_status'] ?? 'verified'
        );

        if ($role === 'admin' && !isset($existingAssignments['admin'])) {
            yamu_redirect_with_message('../user-edit.php?user_id=' . $userId, 'error', 'Admin accounts must be seeded directly in the database');
        }

        if (isset($existingAssignments['admin'])) {
            $role = 'admin';
        }

        $currentAdminUserId = (int) ($_SESSION['admin']['user_id'] ?? 0);
        if (
            isset($existingAssignments['admin'])
            && $currentAdminUserId === $userId
            && !yamu_role_allows_standard_status($accountStatus)
        ) {
            yamu_redirect_with_message('../user-edit.php?user_id=' . $userId, 'error', 'You cannot make your own admin session unavailable from this form');
        }
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        yamu_redirect_with_message($isUpdate ? '../user-edit.php?user_id=' . $userId : '../user-add.php', 'error', 'Please enter a valid email address');
    }

    if (!$isUpdate && strlen($password) < 8) {
        yamu_redirect_with_message('../user-add.php', 'error', 'Password must contain at least 8 characters');
    }

    if ($isUpdate && $password !== '' && strlen($password) < 8) {
        yamu_redirect_with_message('../user-edit.php?user_id=' . $userId, 'error', 'Password must contain at least 8 characters');
    }

    if (!in_array($role, ['driver', 'staff'], true)) {
        $licenseOrNic = null;
        $verificationStatus = 'verified';
        $bio = $bio !== '' ? $bio : null;
    }

    if ($role === 'driver' && $licenseOrNic === '') {
        yamu_redirect_with_message($isUpdate ? '../user-edit.php?user_id=' . $userId : '../user-add.php', 'error', 'Driver accounts require a license number or NIC');
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
            yamu_redirect_with_message($isUpdate ? '../user-edit.php?user_id=' . $userId : '../user-add.php', 'error', 'Email Id Already Exists');
        }

        $duplicateStmt->close();
    }

    if (!$isUpdate && $password === '') {
        yamu_redirect_with_message('../user-add.php', 'error', 'Password is required for new users');
    }

    $currentAvatar = 'avatar.png';

    if ($isUpdate) {
        $currentAvatar = $existingUser['profile_pic'] ?? 'avatar.png';
    }

    $avatarName = yamu_admin_avatar_upload($currentAvatar);

    if ($avatarName === false) {
        yamu_redirect_with_message($isUpdate ? '../user-edit.php?user_id=' . $userId : '../user-add.php', 'error', 'Failed to upload profile image');
    }

    if ($isUpdate) {
        if ($password !== '') {
            $hashedPassword = yamu_hash_password($password);
            $stmt = $conn->prepare(
                'UPDATE users
                 SET username = ?, password = ?, role = ?, full_name = ?, email = ?, address = ?, city = ?, phone = ?, dob = ?, profile_pic = ?, account_status = ?, license_or_nic = ?, verification_status = ?, bio = ?, updated_at = NOW()
                 WHERE user_id = ?'
            );

            if (!$stmt) {
                yamu_redirect_with_message('../user-edit.php?user_id=' . $userId, 'error', 'User update failed');
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
                yamu_redirect_with_message('../user-edit.php?user_id=' . $userId, 'error', 'User update failed');
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
            yamu_redirect_with_message('../user-edit.php?user_id=' . $userId, 'error', 'User update failed');
        }

        $stmt->close();
        $currentAdminUserId = (int) ($_SESSION['admin']['user_id'] ?? 0);
        if (yamu_table_exists($conn, 'user_roles')) {
            yamu_upsert_user_role_assignment($conn, $userId, $role, $accountStatus, $verificationStatus, true, $currentAdminUserId ?: null, 'Updated from admin user form');
            yamu_ensure_role_profile_row($conn, $userId, $role, yamu_fetch_user_by_id($conn, $userId));
            if ($role !== 'customer' && $role !== 'admin') {
                yamu_upsert_user_role_assignment($conn, $userId, 'customer', 'active', 'verified', false, $currentAdminUserId ?: null, 'Default customer role');
                yamu_ensure_role_profile_row($conn, $userId, 'customer', yamu_fetch_user_by_id($conn, $userId));
            }
            yamu_sync_user_primary_role_snapshot($conn, $userId);
        }
        yamu_sync_current_admin_session($conn, $userId);
        yamu_redirect_with_message('../users.php', 'msg', 'User updated successfully');
    }

    $hashedPassword = yamu_hash_password($password);
    $stmt = $conn->prepare(
        'INSERT INTO users (username, password, role, full_name, email, address, city, phone, dob, profile_pic, account_status, rag_date, license_or_nic, verification_status, bio, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, NOW(), NOW())'
    );

    if (!$stmt) {
        yamu_redirect_with_message('../user-add.php', 'error', 'User creation failed');
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
        yamu_redirect_with_message('../user-add.php', 'error', 'User creation failed');
    }

    $newUserId = (int) $stmt->insert_id;
    $stmt->close();

    $currentAdminUserId = (int) ($_SESSION['admin']['user_id'] ?? 0);
    if (yamu_table_exists($conn, 'user_roles')) {
        yamu_upsert_user_role_assignment($conn, $newUserId, $role, $accountStatus, $verificationStatus, true, $currentAdminUserId ?: null, 'Created from admin user form');
        $createdUser = yamu_fetch_user_by_id($conn, $newUserId);
        yamu_ensure_role_profile_row($conn, $newUserId, $role, $createdUser);

        if ($role !== 'customer' && $role !== 'admin') {
            yamu_upsert_user_role_assignment($conn, $newUserId, 'customer', 'active', 'verified', false, $currentAdminUserId ?: null, 'Default customer role');
            yamu_ensure_role_profile_row($conn, $newUserId, 'customer', $createdUser);
        }

        yamu_sync_user_primary_role_snapshot($conn, $newUserId);
    }
    yamu_redirect_with_message('../users.php', 'msg', 'User created successfully');
}

if (isset($_GET['action'], $_GET['user_id'])) {
    $action = $_GET['action'];
    $userId = (int) $_GET['user_id'];
    $user = yamu_fetch_user_by_id($conn, $userId);

    if (!$user) {
        yamu_redirect_with_message('../users.php', 'error', 'User not found');
    }

    $currentAdminUserId = (int) ($_SESSION['admin']['user_id'] ?? 0);
    $currentAdminEmail = $_SESSION['admin']['email'] ?? '';

    if ($action === 'delete') {
        if ($currentAdminUserId === $userId || ($currentAdminEmail !== '' && $currentAdminEmail === $user['email'])) {
            yamu_redirect_with_message('../users.php', 'error', 'You cannot delete the account you are currently using');
        }

        if (yamu_table_exists($conn, 'user_roles')) {
            $conn->query('DELETE FROM user_roles WHERE user_id = ' . $userId);
        }
        if (yamu_table_exists($conn, 'customer_profiles')) {
            $conn->query('DELETE FROM customer_profiles WHERE user_id = ' . $userId);
        }
        if (yamu_table_exists($conn, 'driver_profiles')) {
            $conn->query('DELETE FROM driver_profiles WHERE user_id = ' . $userId);
        }
        if (yamu_table_exists($conn, 'staff_profiles')) {
            $conn->query('DELETE FROM staff_profiles WHERE user_id = ' . $userId);
        }
        if (yamu_table_exists($conn, 'admin_profiles')) {
            $conn->query('DELETE FROM admin_profiles WHERE user_id = ' . $userId);
        }
        if (yamu_table_exists($conn, 'password_resets')) {
            $conn->query('DELETE FROM password_resets WHERE user_id = ' . $userId);
        }

        $stmt = $conn->prepare('DELETE FROM users WHERE user_id = ?');
        $stmt->bind_param('i', $userId);

        if (!$stmt->execute()) {
            $stmt->close();
            yamu_redirect_with_message('../users.php', 'error', 'Unable to delete user');
        }

        $stmt->close();
        yamu_redirect_with_message('../users.php', 'msg', 'User deleted successfully');
    }

    if ($action === 'activate' || $action === 'suspend') {
        $newStatus = $action === 'activate' ? 'active' : 'suspended';
        $stmt = $conn->prepare('UPDATE users SET account_status = ?, updated_at = NOW() WHERE user_id = ?');
        $stmt->bind_param('si', $newStatus, $userId);

        if (!$stmt->execute()) {
            $stmt->close();
            yamu_redirect_with_message('../users.php', 'error', 'Unable to update account status');
        }

        $stmt->close();
        if (yamu_table_exists($conn, 'user_roles')) {
            $roleStmt = $conn->prepare('UPDATE user_roles SET role_status = ?, updated_at = NOW() WHERE user_id = ?');
            if ($roleStmt) {
                $roleStmt->bind_param('si', $newStatus, $userId);
                $roleStmt->execute();
                $roleStmt->close();
            }
            yamu_sync_user_primary_role_snapshot($conn, $userId);
        }
        yamu_sync_current_admin_session($conn, $userId);
        yamu_redirect_with_message('../users.php', 'msg', 'Account status updated successfully');
    }

    if ($action === 'approve-driver' || $action === 'reject-driver') {
        if (yamu_normalize_role($user['role'] ?? 'customer') !== 'driver') {
            yamu_redirect_with_message('../users.php', 'error', 'Only driver accounts can be reviewed from this action');
        }

        $newVerificationStatus = $action === 'approve-driver' ? 'approved' : 'rejected';
        $newAccountStatus = $action === 'approve-driver' ? 'active' : 'suspended';

        $stmt = $conn->prepare('UPDATE users SET verification_status = ?, account_status = ?, updated_at = NOW() WHERE user_id = ?');
        $stmt->bind_param('ssi', $newVerificationStatus, $newAccountStatus, $userId);

        if (!$stmt->execute()) {
            $stmt->close();
            yamu_redirect_with_message('../users.php', 'error', 'Unable to update driver verification');
        }

        $stmt->close();
        if (yamu_table_exists($conn, 'user_roles')) {
            yamu_upsert_user_role_assignment($conn, $userId, 'driver', $newAccountStatus, $newVerificationStatus, false, (int) ($_SESSION['admin']['user_id'] ?? 0), 'Updated from quick driver verification action');
            if (yamu_table_exists($conn, 'driver_profiles')) {
                $driverStmt = $conn->prepare('UPDATE driver_profiles SET verification_status = ?, verified_at = CASE WHEN ? IN (\'approved\', \'verified\') THEN NOW() ELSE NULL END, updated_at = NOW() WHERE user_id = ?');
                if ($driverStmt) {
                    $driverStmt->bind_param('ssi', $newVerificationStatus, $newVerificationStatus, $userId);
                    $driverStmt->execute();
                    $driverStmt->close();
                }
            }
            yamu_sync_user_primary_role_snapshot($conn, $userId);
        }
        yamu_sync_current_admin_session($conn, $userId);
        yamu_redirect_with_message('../users.php', 'msg', 'Driver account updated successfully');
    }
}
