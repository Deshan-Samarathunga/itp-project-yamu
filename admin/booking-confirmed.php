<?php
require_once __DIR__ . '/../includes/auth.php';
carzo_start_session();
carzo_require_admin('index.php');
carzo_redirect('bookings.php?status=confirmed');
?>
