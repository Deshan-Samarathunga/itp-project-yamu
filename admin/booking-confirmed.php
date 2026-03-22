<?php
require_once __DIR__ . '/../includes/auth.php';
yamu_start_session();
yamu_require_admin('index.php');
yamu_redirect('bookings.php?status=confirmed');
?>
