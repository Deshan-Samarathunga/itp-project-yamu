<?php
require_once __DIR__ . '/../includes/auth.php';
yamu_start_session();
yamu_require_admin('index.php');

yamu_redirect_with_message(
    'vehicle.php',
    'error',
    'Admins cannot post vehicle listings. Create or assign them from a staff account instead.'
);
