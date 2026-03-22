<?php
require_once __DIR__ . '/includes/auth.php';
yamu_start_session();

if (!yamu_is_user_authenticated()) {
    yamu_redirect_with_message('signin.php', 'error', 'Please sign in to continue');
}

yamu_redirect('my-profile.php');
