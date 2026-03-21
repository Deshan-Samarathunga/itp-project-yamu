<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/vehicle-management.php';

function carzo_booking_normalize_status($status)
{
    $status = strtolower(trim((string) $status));
    $allowed = ['pending', 'confirmed', 'rejected', 'cancelled', 'completed'];
    return in_array($status, $allowed, true) ? $status : 'pending';
}

function carzo_booking_normalize_payment_status($status)
{
    $status = strtolower(trim((string) $status));
    $allowed = ['pending', 'paid', 'failed', 'refunded'];
    return in_array($status, $allowed, true) ? $status : 'pending';
}

function carzo_booking_legacy_status_value($bookingStatus)
{
    return in_array($bookingStatus, ['confirmed', 'completed'], true) ? 1 : 0;
}

function carzo_booking_fetch($conn, $bookingId)
{
    $bookingId = (int) $bookingId;
    $sql = "SELECT * FROM booking WHERE booking_id = {$bookingId} LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }

    return null;
}

function carzo_booking_sync_vehicle_availability($conn, $vehicleId)
{
    $vehicleId = (int) $vehicleId;
    $vehicle = carzo_vehicle_fetch($conn, $vehicleId);

    if (!$vehicle) {
        return false;
    }

    $listingStatus = carzo_vehicle_normalize_listing_status($vehicle['listing_status'] ?? ($vehicle['vehicle_status'] == 1 ? 'approved' : 'inactive'));
    $maintenanceStatus = carzo_vehicle_normalize_maintenance_status($vehicle['maintenance_status'] ?? 'good');
    $availabilityStatus = 'available';

    if ($listingStatus !== 'approved' || in_array($maintenanceStatus, ['under maintenance', 'unavailable'], true)) {
        $availabilityStatus = 'unavailable';
    } else {
        $sql = "SELECT COUNT(*) AS active_bookings
                FROM booking
                WHERE vehicle_ID = {$vehicleId}
                  AND booking_status IN ('pending', 'confirmed')";
        $result = mysqli_query($conn, $sql);
        $activeBookings = $result ? (int) (mysqli_fetch_assoc($result)['active_bookings'] ?? 0) : 0;
        $availabilityStatus = $activeBookings > 0 ? 'booked' : 'available';
    }

    $availabilityStatus = carzo_escape($conn, $availabilityStatus);
    $vehicleStatus = $listingStatus === 'approved' ? 1 : 0;

    return mysqli_query(
        $conn,
        "UPDATE vehicles
         SET availability_status = '{$availabilityStatus}',
             vehicle_status = {$vehicleStatus},
             updated_at = NOW()
         WHERE vehicle_id = {$vehicleId}"
    );
}

function carzo_booking_total_days($startDate, $endDate)
{
    $start = date_create($startDate);
    $end = date_create($endDate);

    if (!$start || !$end || $end < $start) {
        return 0;
    }

    $dayDiff = (int) date_diff($start, $end)->format('%a');
    return max(1, $dayDiff);
}

function carzo_booking_has_overlap($conn, $vehicleId, $startDate, $endDate, $excludeBookingId = 0)
{
    $vehicleId = (int) $vehicleId;
    $excludeSql = $excludeBookingId > 0 ? " AND booking_id != " . (int) $excludeBookingId : '';
    $startDate = carzo_escape($conn, $startDate);
    $endDate = carzo_escape($conn, $endDate);

    $sql = "SELECT booking_id
            FROM booking
            WHERE vehicle_ID = {$vehicleId}
              AND booking_status IN ('pending', 'confirmed')
              {$excludeSql}
              AND NOT (`end_Date` < '{$startDate}' OR `start_Data` > '{$endDate}')
            LIMIT 1";

    $result = mysqli_query($conn, $sql);
    return $result && mysqli_num_rows($result) > 0;
}

function carzo_booking_create($conn, $customerId, $vehicleId, $startDate, $endDate)
{
    $vehicle = carzo_vehicle_fetch($conn, $vehicleId);

    if (!$vehicle) {
        return [false, 'Vehicle not found'];
    }

    if (($vehicle['listing_status'] ?? 'pending') !== 'approved') {
        return [false, 'This vehicle listing is not approved yet'];
    }

    if (($vehicle['availability_status'] ?? 'available') !== 'available' || in_array(($vehicle['maintenance_status'] ?? 'good'), ['under maintenance', 'unavailable'], true)) {
        return [false, 'This vehicle is currently unavailable'];
    }

    if (carzo_booking_has_overlap($conn, $vehicleId, $startDate, $endDate)) {
        return [false, 'This vehicle is already booked for the selected dates'];
    }

    $totalDays = carzo_booking_total_days($startDate, $endDate);

    if ($totalDays <= 0) {
        return [false, 'Please choose a valid booking date range'];
    }

    $bookingNumber = 'BOOK' . rand(10000, 99999);
    $totalPrice = (float) $vehicle['price'] * $totalDays;
    $driverId = (int) ($vehicle['owner_user_id'] ?? 0);
    $startDate = carzo_escape($conn, $startDate);
    $endDate = carzo_escape($conn, $endDate);

    $sql = "INSERT INTO booking
            (`booking_No`, `user_ID`, `driver_id`, `vehicle_ID`, `start_Data`, `end_Date`, `total`, `status`, `booking_status`, `payment_status`, `promotion_id`, `promo_code`, `discount_amount`, `final_amount`, `booking_Date`, `created_at`, `updated_at`)
            VALUES
            ('{$bookingNumber}', " . (int) $customerId . ", " . ($driverId > 0 ? $driverId : 'NULL') . ", " . (int) $vehicleId . ", '{$startDate}', '{$endDate}', {$totalPrice}, 0, 'pending', 'pending', NULL, NULL, 0.00, {$totalPrice}, NOW(), NOW(), NOW())";

    if (!mysqli_query($conn, $sql)) {
        return [false, 'Booking failed'];
    }

    carzo_booking_sync_vehicle_availability($conn, $vehicleId);

    return [true, 'Booking successful'];
}

function carzo_booking_update_status($conn, $bookingId, $newStatus)
{
    $bookingId = (int) $bookingId;
    $booking = carzo_booking_fetch($conn, $bookingId);

    if (!$booking) {
        return false;
    }

    $newStatus = carzo_booking_normalize_status($newStatus);
    $legacyStatus = carzo_booking_legacy_status_value($newStatus);
    $statusEscaped = carzo_escape($conn, $newStatus);
    $dateSql = '';

    if ($newStatus === 'cancelled' || $newStatus === 'rejected') {
        $dateSql .= ", `cancelled_at` = NOW()";
    }

    if ($newStatus === 'completed') {
        $dateSql .= ", `completed_at` = NOW()";
    }

    $sql = "UPDATE booking
            SET `status` = {$legacyStatus},
                `booking_status` = '{$statusEscaped}',
                `updated_at` = NOW()
                {$dateSql}
            WHERE booking_id = {$bookingId}";

    $updated = mysqli_query($conn, $sql);

    if ($updated && !empty($booking['vehicle_ID'])) {
        carzo_booking_sync_vehicle_availability($conn, (int) $booking['vehicle_ID']);
    }

    return $updated;
}
