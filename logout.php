<?php
    require_once __DIR__ . '/includes/auth.php';
    yamu_logout_current_session();
    yamu_redirect_with_message('index.php', 'msg', 'Signed out successfully');
?>
