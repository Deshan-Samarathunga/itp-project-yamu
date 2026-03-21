<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/dispute-management.php';
carzo_start_session();
carzo_require_user_roles(['customer'], '../signin.php', ['active'], '../index.php');
include 'config.php';

function carzo_store_dispute_attachment($fieldName)
{
    if (!isset($_FILES[$fieldName]) || empty($_FILES[$fieldName]['name'])) {
        return '';
    }

    if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $originalName = basename($_FILES[$fieldName]['name']);
    $safeName = preg_replace('/[^A-Za-z0-9._-]/', '', $originalName);
    $newName = uniqid() . '_' . $safeName;
    $targetDirectory = __DIR__ . '/../assets/images/uploads/disputes/';

    if (!is_dir($targetDirectory)) {
        mkdir($targetDirectory, 0777, true);
    }

    if (!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetDirectory . $newName)) {
        return false;
    }

    return $newName;
}

$customerId = (int) ($_SESSION['user']['user_ID'] ?? 0);

if (isset($_POST['submitDispute'])) {
    $bookingId = (int) ($_POST['booking_id'] ?? 0);
    $subject = trim((string) ($_POST['subject'] ?? ''));
    $category = trim((string) ($_POST['category'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $booking = carzo_booking_fetch($conn, $bookingId);

    if (!$booking || (int) ($booking['user_ID'] ?? 0) !== $customerId) {
        carzo_redirect_with_message('../my-disputes.php', 'error', 'Booking not found');
    }

    $attachment = carzo_store_dispute_attachment('attachment');
    if ($attachment === false) {
        carzo_redirect_with_message('../booking-dispute.php?booking_id=' . $bookingId, 'error', 'Unable to upload attachment');
    }

    [$success, $message] = carzo_complaint_create($conn, $booking, $customerId, $subject, $category, $description, $attachment);
    if (!$success) {
        carzo_redirect_with_message('../booking-dispute.php?booking_id=' . $bookingId, 'error', $message);
    }

    carzo_redirect_with_message('../my-disputes.php', 'msg', $message);
}
