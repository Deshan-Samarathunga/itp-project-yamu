<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/promotion-management.php';
carzo_start_session();
carzo_require_admin('../index.php', '../access-denied.php');
include 'config.php';

function carzo_admin_promotion_payload($conn)
{
    $code = strtoupper(trim((string) ($_POST['code'] ?? '')));
    $title = trim((string) ($_POST['title'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $discountType = carzo_promotion_normalize_discount_type($_POST['discount_type'] ?? 'fixed');
    $discountValue = (float) ($_POST['discount_value'] ?? 0);
    $validFrom = str_replace('T', ' ', trim((string) ($_POST['valid_from'] ?? '')));
    $validTo = str_replace('T', ' ', trim((string) ($_POST['valid_to'] ?? '')));
    $usageLimit = trim((string) ($_POST['usage_limit'] ?? ''));
    $minimumBookingAmount = trim((string) ($_POST['minimum_booking_amount'] ?? ''));
    $status = carzo_promotion_normalize_status($_POST['status'] ?? 'active');
    $applicableVehicleId = (int) ($_POST['applicable_vehicle_id'] ?? 0);

    if ($code === '' || $title === '' || $discountValue <= 0) {
        return [false, 'Please complete the required promotion fields'];
    }

    return [[
        'code' => carzo_escape($conn, $code),
        'title' => carzo_escape($conn, $title),
        'description' => carzo_escape($conn, $description),
        'discount_type' => carzo_escape($conn, $discountType),
        'discount_value' => $discountValue,
        'valid_from' => $validFrom !== '' ? "'" . carzo_escape($conn, $validFrom) . "'" : 'NULL',
        'valid_to' => $validTo !== '' ? "'" . carzo_escape($conn, $validTo) . "'" : 'NULL',
        'usage_limit' => $usageLimit === '' ? 'NULL' : (int) $usageLimit,
        'minimum_booking_amount' => $minimumBookingAmount === '' ? 0 : (float) $minimumBookingAmount,
        'status' => carzo_escape($conn, $status),
        'applicable_vehicle_id' => $applicableVehicleId > 0 ? $applicableVehicleId : 'NULL',
    ], null];
}

if (isset($_POST['createPromotion'])) {
    [$payload, $error] = carzo_admin_promotion_payload($conn);
    if ($payload === false) {
        carzo_redirect_with_message('../promotion-add.php', 'error', $error);
    }

    $sql = "INSERT INTO promotions
            (`code`, `title`, `description`, `discount_type`, `discount_value`, `valid_from`, `valid_to`, `usage_limit`, `minimum_booking_amount`, `status`, `applicable_vehicle_id`, `created_at`, `updated_at`)
            VALUES
            ('{$payload['code']}', '{$payload['title']}', '{$payload['description']}', '{$payload['discount_type']}', {$payload['discount_value']}, {$payload['valid_from']}, {$payload['valid_to']}, {$payload['usage_limit']}, {$payload['minimum_booking_amount']}, '{$payload['status']}', {$payload['applicable_vehicle_id']}, NOW(), NOW())";

    if (!mysqli_query($conn, $sql)) {
        carzo_redirect_with_message('../promotion-add.php', 'error', 'Unable to create promotion');
    }

    carzo_redirect_with_message('../promotions.php', 'msg', 'Promotion created successfully');
}

if (isset($_POST['updatePromotion'])) {
    $promotionId = (int) ($_POST['promotion_id'] ?? 0);
    [$payload, $error] = carzo_admin_promotion_payload($conn);
    if ($payload === false) {
        carzo_redirect_with_message('../promotion-edit.php?promotion_id=' . $promotionId, 'error', $error);
    }

    $sql = "UPDATE promotions
            SET code = '{$payload['code']}',
                title = '{$payload['title']}',
                description = '{$payload['description']}',
                discount_type = '{$payload['discount_type']}',
                discount_value = {$payload['discount_value']},
                valid_from = {$payload['valid_from']},
                valid_to = {$payload['valid_to']},
                usage_limit = {$payload['usage_limit']},
                minimum_booking_amount = {$payload['minimum_booking_amount']},
                status = '{$payload['status']}',
                applicable_vehicle_id = {$payload['applicable_vehicle_id']},
                updated_at = NOW()
            WHERE promotion_id = {$promotionId}";

    if (!mysqli_query($conn, $sql)) {
        carzo_redirect_with_message('../promotion-edit.php?promotion_id=' . $promotionId, 'error', 'Unable to update promotion');
    }

    carzo_redirect_with_message('../promotions.php', 'msg', 'Promotion updated successfully');
}

if (isset($_GET['deletePromotion'])) {
    $promotionId = (int) $_GET['deletePromotion'];
    if (!mysqli_query($conn, "DELETE FROM promotions WHERE promotion_id = {$promotionId}")) {
        carzo_redirect_with_message('../promotions.php', 'error', 'Unable to delete promotion');
    }

    carzo_redirect_with_message('../promotions.php', 'msg', 'Promotion deleted successfully');
}

