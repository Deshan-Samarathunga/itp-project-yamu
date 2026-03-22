<?php
require_once __DIR__ . '/auth.php';
yamu_start_session();
include 'config.php';

if (isset($_POST['forgotPassword'])) {
    $email = trim((string) ($_POST['email'] ?? ''));

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        yamu_redirect_with_message('../forgot-password.php', 'error', 'Please enter a valid email address');
    }

    $user = yamu_fetch_user_by_email($conn, $email);
    $genericMessage = 'If an account exists for that email, a password reset link has been generated.';

    unset($_SESSION['password_reset_preview']);

    if ($user && yamu_table_exists($conn, 'password_resets')) {
        $token = yamu_create_password_reset_token($conn, (int) $user['user_id'], $email, 30);

        if ($token) {
            $_SESSION['password_reset_preview'] = 'reset-password.php?token=' . urlencode($token);
        }
    }

    yamu_redirect_with_message('../forgot-password.php', 'msg', $genericMessage);
}
