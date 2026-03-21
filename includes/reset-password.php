<?php
require_once __DIR__ . '/auth.php';
carzo_start_session();
include 'config.php';

if (isset($_POST['resetPassword'])) {
    $token = trim((string) ($_POST['token'] ?? ''));
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($token === '') {
        carzo_redirect_with_message('../forgot-password.php', 'error', 'Invalid password reset request');
    }

    if ($newPassword === '' || strlen($newPassword) < 8) {
        carzo_redirect_with_message('../reset-password.php?token=' . urlencode($token), 'error', 'Password must contain at least 8 characters');
    }

    if ($newPassword !== $confirmPassword) {
        carzo_redirect_with_message('../reset-password.php?token=' . urlencode($token), 'error', 'Password confirmation failed');
    }

    $resetRow = carzo_fetch_password_reset_by_token($conn, $token);

    if (!$resetRow) {
        carzo_redirect_with_message('../forgot-password.php', 'error', 'Reset link is invalid or has expired');
    }

    $userId = (int) ($resetRow['user_id'] ?? 0);
    $email = trim((string) ($resetRow['email'] ?? ''));

    $user = $userId > 0 ? carzo_fetch_user_by_id($conn, $userId) : carzo_fetch_user_by_email($conn, $email);

    if (!$user) {
        carzo_mark_password_reset_used($conn, (int) $resetRow['password_reset_id']);
        carzo_redirect_with_message('../forgot-password.php', 'error', 'User account not found');
    }

    $hashedPassword = carzo_hash_password($newPassword);
    $stmt = $conn->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE user_id = ?');

    if (!$stmt) {
        carzo_redirect_with_message('../forgot-password.php', 'error', 'Unable to reset password at the moment');
    }

    $userId = (int) $user['user_id'];
    $stmt->bind_param('si', $hashedPassword, $userId);
    $ok = $stmt->execute();
    $stmt->close();

    if (!$ok) {
        carzo_redirect_with_message('../reset-password.php?token=' . urlencode($token), 'error', 'Unable to reset password');
    }

    carzo_mark_password_reset_used($conn, (int) $resetRow['password_reset_id']);

    if (carzo_is_user_authenticated() && (int) ($_SESSION['user']['user_ID'] ?? 0) === $userId) {
        carzo_refresh_user_session($conn, $userId, $_SESSION['user']['active_role'] ?? null);
    }

    carzo_redirect_with_message('../signin.php', 'msg', 'Password reset successful. Please sign in.');
}
