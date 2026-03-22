<?php
require_once __DIR__ . '/includes/auth.php';
yamu_start_session();

$target = 'staff-vehicle-edit.php';
if (isset($_GET['vehicle_id'])) {
    $target .= '?vehicle_id=' . urlencode((string) $_GET['vehicle_id']);
}

yamu_redirect($target);
