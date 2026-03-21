<?php
require_once __DIR__ . '/auth.php';
carzo_start_session();
carzo_require_user_roles(['driver'], '../signin.php', ['active', 'pending'], '../index.php');
carzo_redirect('../driver-ads.php');
