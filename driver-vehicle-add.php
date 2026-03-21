<?php
require_once __DIR__ . '/includes/auth.php';
carzo_start_session();
carzo_require_user_roles(['driver'], 'signin.php', ['active', 'pending'], 'index.php');
carzo_redirect('driver-ad-add.php');
