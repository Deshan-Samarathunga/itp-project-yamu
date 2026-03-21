<?php
require_once __DIR__ . '/auth.php';
carzo_start_session();
carzo_require_user_roles(['customer', 'driver', 'staff', 'admin'], '../signin.php', ['active', 'pending', 'verified'], '../access-denied.php');
include 'config.php';

if (isset($_POST['UpdatePassword'])) {
    $sessionUserId = (int) ($_SESSION['user']['user_ID'] ?? 0);
    $userId = (int) ($_POST['userID'] ?? 0);
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($sessionUserId !== $userId) {
        carzo_redirect_with_message('../update-password.php', 'error', 'You can only update your own password');
    }

    $user = carzo_fetch_user_by_id($conn, $userId);

    if (!$user) {
        carzo_redirect_with_message('../update-password.php', 'error', 'User not found');
    }

    if (!carzo_password_matches($currentPassword, $user['password'])) {
        carzo_redirect_with_message('../update-password.php', 'error', 'Invalid current password. Please try again');
    }

    if ($newPassword !== $confirmPassword) {
        carzo_redirect_with_message('../update-password.php', 'error', 'Password confirmation failed. Please try again');
    }

    if (strlen($newPassword) < 8) {
        carzo_redirect_with_message('../update-password.php', 'error', 'Password must contain at least 8 characters');
    }

    $hashedPassword = carzo_hash_password($newPassword);
    $stmt = $conn->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE user_id = ?');

    if (!$stmt) {
        carzo_redirect_with_message('../update-password.php', 'error', 'Password Update failed');
    }

    $stmt->bind_param('si', $hashedPassword, $userId);

    if (!$stmt->execute()) {
        $stmt->close();
        carzo_redirect_with_message('../update-password.php', 'error', 'Password Update failed');
    }

    $stmt->close();

    $updatedUser = carzo_fetch_user_by_id($conn, $userId);

    if ($updatedUser) {
        carzo_set_user_session($updatedUser, $conn, carzo_current_user_role());
    }

    carzo_redirect_with_message('../update-password.php', 'msg', 'Password Updated Successfully');
}
