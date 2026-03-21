<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/booking-management.php';
require_once __DIR__ . '/promotion-management.php';

function carzo_payment_normalize_status($status)
{
    $status = strtolower(trim((string) $status));
    $allowed = ['pending', 'paid', 'failed', 'refunded'];
    return in_array($status, $allowed, true) ? $status : 'pending';
}

function carzo_payment_normalize_method($method)
{
    $method = strtolower(trim((string) $method));
    $allowed = ['mock_card', 'bank_transfer', 'cash', 'wallet'];
    return in_array($method, $allowed, true) ? $method : 'mock_card';
}

function carzo_payment_fetch($conn, $paymentId)
{
    $paymentId = (int) $paymentId;
    $result = mysqli_query($conn, "SELECT * FROM payments WHERE payment_id = {$paymentId} LIMIT 1");
    return ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;
}

function carzo_payment_fetch_by_booking($conn, $bookingId)
{
    $bookingId = (int) $bookingId;
    $result = mysqli_query($conn, "SELECT * FROM payments WHERE booking_id = {$bookingId} ORDER BY created_at DESC, payment_id DESC LIMIT 1");
    return ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;
}

function carzo_payment_reference()
{
    return 'TXN' . date('YmdHis') . rand(100, 999);
}

function carzo_payment_create($conn, array $booking, $customerId, $paymentMethod, $promoCode = '')
{
    $bookingStatus = carzo_booking_normalize_status($booking['booking_status'] ?? 'pending');
    if (!in_array($bookingStatus, ['pending', 'confirmed', 'completed'], true)) {
        return [false, 'This booking cannot be paid in its current status', null];
    }

    if ((int) ($booking['user_ID'] ?? 0) !== (int) $customerId) {
        return [false, 'This booking does not belong to you', null];
    }

    if (carzo_booking_normalize_payment_status($booking['payment_status'] ?? 'pending') === 'paid') {
        return [false, 'This booking has already been paid', null];
    }

    $amount = (float) ($booking['total'] ?? 0);
    $discountAmount = 0.00;
    $finalAmount = $amount;
    $promotionId = 'NULL';
    $promoCodeValue = 'NULL';

    $promoCode = strtoupper(trim((string) $promoCode));
    if ($promoCode !== '') {
        [$promotion, $promoError, $promoDiscount, $promoFinal] = carzo_promotion_validate_for_booking($conn, $promoCode, $amount, (int) ($booking['vehicle_ID'] ?? 0));

        if ($promotion === false) {
            return [false, $promoError, null];
        }

        $discountAmount = (float) $promoDiscount;
        $finalAmount = (float) $promoFinal;
        $promotionId = (int) $promotion['promotion_id'];
        $promoCodeValue = "'" . carzo_escape($conn, $promotion['code']) . "'";
    }

    $paymentMethod = carzo_payment_normalize_method($paymentMethod);
    $paymentMethodEscaped = carzo_escape($conn, $paymentMethod);
    $transactionReference = carzo_payment_reference();
    $referenceEscaped = carzo_escape($conn, $transactionReference);

    $sql = "INSERT INTO payments
            (`booking_id`, `customer_id`, `driver_id`, `promotion_id`, `promo_code`, `amount`, `discount_amount`, `final_amount`, `payment_method`, `transaction_reference`, `payment_status`, `paid_at`, `created_at`, `updated_at`)
            VALUES
            (" . (int) $booking['booking_id'] . ",
             " . (int) $customerId . ",
             " . ((int) ($booking['driver_id'] ?? 0) > 0 ? (int) $booking['driver_id'] : 'NULL') . ",
             {$promotionId},
             {$promoCodeValue},
             {$amount},
             {$discountAmount},
             {$finalAmount},
             '{$paymentMethodEscaped}',
             '{$referenceEscaped}',
             'paid',
             NOW(),
             NOW(),
             NOW())";

    if (!mysqli_query($conn, $sql)) {
        return [false, 'Unable to process payment', null];
    }

    $paymentId = (int) mysqli_insert_id($conn);

    mysqli_query(
        $conn,
        "UPDATE booking
         SET payment_status = 'paid',
             promotion_id = " . ($promotionId === 'NULL' ? 'NULL' : (int) $promotionId) . ",
             promo_code = {$promoCodeValue},
             discount_amount = {$discountAmount},
             final_amount = {$finalAmount},
             updated_at = NOW()
         WHERE booking_id = " . (int) $booking['booking_id']
    );

    if ($promotionId !== 'NULL') {
        mysqli_query($conn, "UPDATE promotions SET usage_count = usage_count + 1, updated_at = NOW() WHERE promotion_id = " . (int) $promotionId);
    }

    return [true, 'Payment completed successfully', $paymentId];
}

function carzo_payment_update_status($conn, $paymentId, $newStatus)
{
    $payment = carzo_payment_fetch($conn, $paymentId);
    if (!$payment) {
        return false;
    }

    $newStatus = carzo_payment_normalize_status($newStatus);
    $statusEscaped = carzo_escape($conn, $newStatus);
    $paidAtSql = $newStatus === 'paid' ? ', paid_at = NOW()' : '';

    $updated = mysqli_query(
        $conn,
        "UPDATE payments
         SET payment_status = '{$statusEscaped}',
             updated_at = NOW()
             {$paidAtSql}
         WHERE payment_id = " . (int) $paymentId
    );

    if ($updated) {
        mysqli_query(
            $conn,
            "UPDATE booking
             SET payment_status = '{$statusEscaped}',
                 updated_at = NOW()
             WHERE booking_id = " . (int) $payment['booking_id']
        );
    }

    return $updated;
}
