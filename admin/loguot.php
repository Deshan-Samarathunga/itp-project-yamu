<?php
    require_once __DIR__ . '/../includes/auth.php';
    carzo_logout_current_session();
    carzo_redirect_with_message('../index.php', 'msg', 'Signed out successfully');
?>
