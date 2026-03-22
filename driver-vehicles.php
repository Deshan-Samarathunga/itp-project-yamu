<?php
require_once __DIR__ . '/includes/auth.php';
yamu_start_session();

$target = 'staff-vehicles.php';
if (isset($_GET['status']) && $_GET['status'] !== '') {
    $target .= '?status=' . urlencode((string) $_GET['status']);
}

yamu_redirect($target);
