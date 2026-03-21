<?php
require_once __DIR__ . '/auth.php';
carzo_start_session();
include 'config.php';

if (isset($_POST['forgotPassword'])) {
    $email = trim((string) ($_POST['email'] ?? ''));

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        carzo_redirect_with_message('../forgot-password.php', 'error', 'Please enter a valid email address');
    }

    $user = carzo_fetch_user_by_email($conn, $email);
    $genericMessage = 'If an account exists for that email, a password reset link has been generated.';

    unset($_SESSION['password_reset_preview']);

    if ($user && carzo_table_exists($conn, 'password_resets')) {
        $token = carzo_create_password_reset_token($conn, (int) $user['user_id'], $email, 30);

        if ($token) {
            $_SESSION['password_reset_preview'] = 'reset-password.php?token=' . urlencode($token);
        }
    }

    carzo_redirect_with_message('../forgot-password.php', 'msg', $genericMessage);
}
