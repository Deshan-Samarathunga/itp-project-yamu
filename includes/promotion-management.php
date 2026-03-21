<?php
require_once __DIR__ . '/auth.php';

function carzo_promotion_normalize_status($status)
{
    $status = strtolower(trim((string) $status));
    $allowed = ['active', 'inactive'];
    return in_array($status, $allowed, true) ? $status : 'inactive';
}

function carzo_promotion_normalize_discount_type($type)
{
    $type = strtolower(trim((string) $type));
    $allowed = ['fixed', 'percentage'];
    return in_array($type, $allowed, true) ? $type : 'fixed';
}

function carzo_promotion_fetch_by_id($conn, $promotionId)
{
    $promotionId = (int) $promotionId;
    $result = mysqli_query($conn, "SELECT * FROM promotions WHERE promotion_id = {$promotionId} LIMIT 1");
    return ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;
}

function carzo_promotion_fetch_by_code($conn, $code)
{
    $code = strtoupper(trim((string) $code));
    if ($code === '') {
        return null;
    }

    $codeEscaped = carzo_escape($conn, $code);
    $result = mysqli_query($conn, "SELECT * FROM promotions WHERE UPPER(code) = '{$codeEscaped}' LIMIT 1");
    return ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;
}

function carzo_promotion_is_valid_now(array $promotion)
{
    $now = time();
    $validFrom = !empty($promotion['valid_from']) ? strtotime($promotion['valid_from']) : null;
    $validTo = !empty($promotion['valid_to']) ? strtotime($promotion['valid_to']) : null;

    if ($validFrom && $now < $validFrom) {
        return false;
    }

    if ($validTo && $now > $validTo) {
        return false;
    }

    return true;
}

function carzo_promotion_calculate_discount(array $promotion, $amount)
{
    $amount = (float) $amount;
    $discountType = carzo_promotion_normalize_discount_type($promotion['discount_type'] ?? 'fixed');
    $discountValue = (float) ($promotion['discount_value'] ?? 0);

    if ($discountType === 'percentage') {
        $discountAmount = ($amount * $discountValue) / 100;
    } else {
        $discountAmount = $discountValue;
    }

    $discountAmount = min($amount, max(0, $discountAmount));
    return round($discountAmount, 2);
}

function carzo_promotion_validate_for_booking($conn, $code, $amount, $vehicleId)
{
    $promotion = carzo_promotion_fetch_by_code($conn, $code);

    if (!$promotion) {
        return [false, 'Promo code not found', 0.00, null];
    }

    if (carzo_promotion_normalize_status($promotion['status'] ?? 'inactive') !== 'active') {
        return [false, 'Promo code is inactive', 0.00, null];
    }

    if (!carzo_promotion_is_valid_now($promotion)) {
        return [false, 'Promo code has expired or is not active yet', 0.00, null];
    }

    $usageLimit = isset($promotion['usage_limit']) ? (int) $promotion['usage_limit'] : 0;
    $usageCount = isset($promotion['usage_count']) ? (int) $promotion['usage_count'] : 0;
    if ($usageLimit > 0 && $usageCount >= $usageLimit) {
        return [false, 'Promo code usage limit has been reached', 0.00, null];
    }

    $minimumBookingAmount = (float) ($promotion['minimum_booking_amount'] ?? 0);
    if ((float) $amount < $minimumBookingAmount) {
        return [false, 'Booking total does not meet the promo minimum amount', 0.00, null];
    }

    $applicableVehicleId = (int) ($promotion['applicable_vehicle_id'] ?? 0);
    if ($applicableVehicleId > 0 && $applicableVehicleId !== (int) $vehicleId) {
        return [false, 'Promo code is not valid for this vehicle', 0.00, null];
    }

    $discountAmount = carzo_promotion_calculate_discount($promotion, $amount);
    return [$promotion, null, $discountAmount, round((float) $amount - $discountAmount, 2)];
}
