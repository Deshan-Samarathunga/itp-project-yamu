<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/payment-management.php';
carzo_start_session();
carzo_require_admin('../index.php', '../access-denied.php');
include 'config.php';

if (isset($_GET['payment_id'], $_GET['status'])) {
    $paymentId = (int) $_GET['payment_id'];
    $status = carzo_payment_normalize_status($_GET['status']);

    if (!carzo_payment_update_status($conn, $paymentId, $status)) {
        carzo_redirect_with_message('../payments.php', 'error', 'Unable to update payment status');
    }

    carzo_redirect_with_message('../payments.php', 'msg', 'Payment status updated successfully');
}

