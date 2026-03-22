<?php
require_once __DIR__ . '/auth.php';
yamu_start_session();
yamu_require_authenticated_user('../signin.php');
include 'config.php';

if (isset($_POST['UpdatePassword'])) {
    yamu_ensure_users_password_column($conn);

    $sessionUserId = (int) ($_SESSION['user']['user_ID'] ?? 0);
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    $userId = $sessionUserId;

    $user = yamu_fetch_user_by_id($conn, $userId);

    if (!$user) {
        yamu_redirect_with_message('../update-password.php', 'error', 'User not found');
    }

    if (!yamu_password_matches($currentPassword, $user['password'])) {
        yamu_redirect_with_message('../update-password.php', 'error', 'Invalid current password. Please try again');
    }

    if ($newPassword !== $confirmPassword) {
        yamu_redirect_with_message('../update-password.php', 'error', 'Password confirmation failed. Please try again');
    }

    if (strlen($newPassword) < 8) {
        yamu_redirect_with_message('../update-password.php', 'error', 'Password must contain at least 8 characters');
    }

    $hashedPassword = yamu_hash_password($newPassword);
    $stmt = $conn->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE user_id = ?');

    if (!$stmt) {
        yamu_redirect_with_message('../update-password.php', 'error', 'Password Update failed');
    }

    $stmt->bind_param('si', $hashedPassword, $userId);

    if (!$stmt->execute()) {
        $stmt->close();
        yamu_redirect_with_message('../update-password.php', 'error', 'Password Update failed');
    }

    $stmt->close();

    $updatedUser = yamu_fetch_user_by_id($conn, $userId);

    if ($updatedUser) {
        yamu_set_user_session($updatedUser, $conn, yamu_current_user_role());
    }

    yamu_redirect_with_message('../update-password.php', 'msg', 'Password Updated Successfully');
}
