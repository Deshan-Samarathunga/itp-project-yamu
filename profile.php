<?php
require_once __DIR__ . '/includes/auth.php';
carzo_start_session();

if (!carzo_is_user_authenticated()) {
    carzo_redirect_with_message('signin.php', 'error', 'Please sign in to continue');
}

carzo_redirect('edit-profile.php');
