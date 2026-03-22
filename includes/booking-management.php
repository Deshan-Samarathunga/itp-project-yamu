<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/vehicle-management.php';

function yamu_booking_normalize_status($status)
{
    $status = strtolower(trim((string) $status));
    $allowed = ['pending', 'confirmed', 'rejected', 'cancelled', 'completed'];
    return in_array($status, $allowed, true) ? $status : 'pending';
}

function yamu_booking_normalize_payment_status($status)
{
    $status = strtolower(trim((string) $status));
    $allowed = ['pending', 'paid', 'failed', 'refunded'];
    return in_array($status, $allowed, true) ? $status : 'pending';
}

function yamu_booking_legacy_status_value($bookingStatus)
{
    return in_array($bookingStatus, ['confirmed', 'completed'], true) ? 1 : 0;
}

function yamu_booking_fetch($conn, $bookingId)
{
    $bookingId = (int) $bookingId;
    $stmt = $conn->prepare('SELECT * FROM booking WHERE booking_id = ? LIMIT 1');

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $booking ?: null;
}

function yamu_booking_service_type(array $booking)
{
    return (int) ($booking['vehicle_ID'] ?? 0) > 0 ? 'vehicle' : 'driver';
}

function yamu_booking_service_label($serviceName)
{
    $serviceName = trim((string) $serviceName);
    return $serviceName !== '' ? $serviceName : 'Driver Service';
}

function yamu_booking_role_is_usable($conn, $userId, $role)
{
    $userId = (int) $userId;
    $role = yamu_normalize_role($role);
    $user = yamu_fetch_user_by_id($conn, $userId);

    if (!$user) {
        return false;
    }

    $assignments = yamu_fetch_user_roles(
        $conn,
        $userId,
        $user['role'] ?? 'customer',
        $user['account_status'] ?? 'active',
        $user['verification_status'] ?? 'verified'
    );
    $assignment = $assignments[$role] ?? null;

    if (!$assignment) {
        return false;
    }

    if (!yamu_role_allows_standard_status($assignment['role_status'] ?? 'active')) {
        return false;
    }

    if (in_array($role, ['driver', 'staff'], true)) {
        $verificationStatus = yamu_normalize_verification_status($assignment['verification_status'] ?? 'pending', $role);
        return in_array($verificationStatus, ['approved', 'verified'], true);
    }

    return true;
}

function yamu_booking_fetch_driver_service($conn, $driverAdId)
{
    $driverAdId = (int) $driverAdId;
    $stmt = $conn->prepare(
        "SELECT da.*, u.user_id, u.full_name, u.email, u.phone, u.city, u.profile_pic, u.account_status, u.verification_status
         FROM driver_ads da
         INNER JOIN users u ON u.user_id = da.driver_user_id
         WHERE da.driver_ad_id = ?
           AND da.advertisement_status = 'active'
         LIMIT 1"
    );

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $driverAdId);
    $stmt->execute();
    $result = $stmt->get_result();
    $service = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$service) {
        return null;
    }

    if (!yamu_booking_role_is_usable($conn, (int) ($service['driver_user_id'] ?? 0), 'driver')) {
        return null;
    }

    return $service;
}

function yamu_booking_sync_vehicle_availability($conn, $vehicleId)
{
    $vehicleId = (int) $vehicleId;
    $vehicle = yamu_vehicle_fetch($conn, $vehicleId);

    if (!$vehicle) {
        return false;
    }

    $listingStatus = yamu_vehicle_normalize_listing_status($vehicle['listing_status'] ?? ($vehicle['vehicle_status'] == 1 ? 'approved' : 'inactive'));
    $maintenanceStatus = yamu_vehicle_normalize_maintenance_status($vehicle['maintenance_status'] ?? 'good');
    $availabilityStatus = 'available';

    if ($listingStatus !== 'approved' || in_array($maintenanceStatus, ['under maintenance', 'unavailable'], true)) {
        $availabilityStatus = 'unavailable';
    } else {
        $activeBookings = 0;
        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS active_bookings
             FROM booking
             WHERE vehicle_ID = ?
               AND booking_status IN ('pending', 'confirmed')"
        );

        if ($stmt) {
            $stmt->bind_param('i', $vehicleId);
            $stmt->execute();
            $result = $stmt->get_result();
            $activeBookings = $result ? (int) (($result->fetch_assoc()['active_bookings'] ?? 0)) : 0;
            $stmt->close();
        }

        $availabilityStatus = $activeBookings > 0 ? 'booked' : 'available';
    }

    $vehicleStatus = $listingStatus === 'approved' ? 1 : 0;
    $stmt = $conn->prepare(
        'UPDATE vehicles
         SET availability_status = ?,
             vehicle_status = ?,
             updated_at = NOW()
         WHERE vehicle_id = ?'
    );

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('sii', $availabilityStatus, $vehicleStatus, $vehicleId);
    $updated = $stmt->execute();
    $stmt->close();

    return $updated;
}

function yamu_booking_total_days($startDate, $endDate)
{
    $start = date_create($startDate);
    $end = date_create($endDate);

    if (!$start || !$end || $end < $start) {
        return 0;
    }

    $dayDiff = (int) date_diff($start, $end)->format('%a');
    return max(1, $dayDiff);
}

function yamu_booking_has_overlap($conn, $vehicleId, $startDate, $endDate, $excludeBookingId = 0)
{
    $vehicleId = (int) $vehicleId;

    if ($excludeBookingId > 0) {
        $stmt = $conn->prepare(
            "SELECT booking_id
             FROM booking
             WHERE vehicle_ID = ?
               AND booking_status IN ('pending', 'confirmed')
               AND booking_id != ?
               AND NOT (`end_Date` < ? OR `start_Data` > ?)
             LIMIT 1"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('iiss', $vehicleId, $excludeBookingId, $startDate, $endDate);
    } else {
        $stmt = $conn->prepare(
            "SELECT booking_id
             FROM booking
             WHERE vehicle_ID = ?
               AND booking_status IN ('pending', 'confirmed')
               AND NOT (`end_Date` < ? OR `start_Data` > ?)
             LIMIT 1"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('iss', $vehicleId, $startDate, $endDate);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $hasOverlap = $result && $result->num_rows > 0;
    $stmt->close();

    return $hasOverlap;
}

function yamu_driver_booking_has_overlap($conn, $driverId, $startDate, $endDate, $excludeBookingId = 0)
{
    $driverId = (int) $driverId;

    if ($excludeBookingId > 0) {
        $stmt = $conn->prepare(
            "SELECT booking_id
             FROM booking
             WHERE driver_id = ?
               AND (vehicle_ID IS NULL OR vehicle_ID = 0)
               AND booking_status IN ('pending', 'confirmed')
               AND booking_id != ?
               AND NOT (`end_Date` < ? OR `start_Data` > ?)
             LIMIT 1"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('iiss', $driverId, $excludeBookingId, $startDate, $endDate);
    } else {
        $stmt = $conn->prepare(
            "SELECT booking_id
             FROM booking
             WHERE driver_id = ?
               AND (vehicle_ID IS NULL OR vehicle_ID = 0)
               AND booking_status IN ('pending', 'confirmed')
               AND NOT (`end_Date` < ? OR `start_Data` > ?)
             LIMIT 1"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('iss', $driverId, $startDate, $endDate);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $hasOverlap = $result && $result->num_rows > 0;
    $stmt->close();

    return $hasOverlap;
}

function yamu_booking_create($conn, $customerId, $vehicleId, $startDate, $endDate)
{
    $customerId = (int) $customerId;
    $vehicleId = (int) $vehicleId;
    $vehicle = yamu_vehicle_fetch($conn, $vehicleId);

    if (!$vehicle) {
        return [false, 'Vehicle not found'];
    }

    if (($vehicle['listing_status'] ?? 'pending') !== 'approved') {
        return [false, 'This vehicle listing is not approved yet'];
    }

    if (!yamu_booking_role_is_usable($conn, (int) ($vehicle['owner_user_id'] ?? 0), 'staff')) {
        return [false, 'This rental vehicle is not currently available from an approved rental center'];
    }

    if (($vehicle['availability_status'] ?? 'available') !== 'available' || in_array(($vehicle['maintenance_status'] ?? 'good'), ['under maintenance', 'unavailable'], true)) {
        return [false, 'This vehicle is currently unavailable'];
    }

    if (yamu_booking_has_overlap($conn, $vehicleId, $startDate, $endDate)) {
        return [false, 'This vehicle is already booked for the selected dates'];
    }

    $totalDays = yamu_booking_total_days($startDate, $endDate);

    if ($totalDays <= 0) {
        return [false, 'Please choose a valid booking date range'];
    }

    $bookingNumber = 'BOOK' . rand(10000, 99999);
    $totalPrice = (float) $vehicle['price'] * $totalDays;
    $driverId = (int) ($vehicle['owner_user_id'] ?? 0);
    $bookingStatus = 'pending';
    $paymentStatus = 'pending';
    $legacyStatus = 0;
    $driverValue = $driverId > 0 ? $driverId : null;
    $stmt = $conn->prepare(
        'INSERT INTO booking
         (`booking_No`, `user_ID`, `driver_id`, `vehicle_ID`, `start_Data`, `end_Date`, `total`, `status`, `booking_status`, `payment_status`, `promotion_id`, `promo_code`, `discount_amount`, `final_amount`, `booking_Date`, `created_at`, `updated_at`)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, 0.00, ?, NOW(), NOW(), NOW())'
    );

    if (!$stmt) {
        return [false, 'Booking failed'];
    }

    $stmt->bind_param(
        'siiissdissd',
        $bookingNumber,
        $customerId,
        $driverValue,
        $vehicleId,
        $startDate,
        $endDate,
        $totalPrice,
        $legacyStatus,
        $bookingStatus,
        $paymentStatus,
        $totalPrice
    );
    $created = $stmt->execute();
    $stmt->close();

    if (!$created) {
        return [false, 'Booking failed'];
    }

    yamu_booking_sync_vehicle_availability($conn, $vehicleId);

    return [true, 'Booking successful'];
}

function yamu_booking_create_driver_service($conn, $customerId, $driverAdId, $startDate, $endDate)
{
    $customerId = (int) $customerId;
    $driverService = yamu_booking_fetch_driver_service($conn, $driverAdId);

    if (!$driverService) {
        return [false, 'Driver service not found'];
    }

    $driverId = (int) ($driverService['driver_user_id'] ?? 0);

    if ($driverId <= 0) {
        return [false, 'Driver service not found'];
    }

    if ($driverId === (int) $customerId) {
        return [false, 'You cannot book your own driver service'];
    }

    if (yamu_driver_booking_has_overlap($conn, $driverId, $startDate, $endDate)) {
        return [false, 'This driver is already booked for the selected dates'];
    }

    $totalDays = yamu_booking_total_days($startDate, $endDate);

    if ($totalDays <= 0) {
        return [false, 'Please choose a valid booking date range'];
    }

    $bookingNumber = 'BOOK' . rand(10000, 99999);
    $totalPrice = (float) ($driverService['daily_rate'] ?? 0) * $totalDays;
    $bookingStatus = 'pending';
    $paymentStatus = 'pending';
    $legacyStatus = 0;
    $stmt = $conn->prepare(
        'INSERT INTO booking
         (`booking_No`, `user_ID`, `driver_id`, `vehicle_ID`, `start_Data`, `end_Date`, `total`, `status`, `booking_status`, `payment_status`, `promotion_id`, `promo_code`, `discount_amount`, `final_amount`, `booking_Date`, `created_at`, `updated_at`)
         VALUES (?, ?, ?, NULL, ?, ?, ?, ?, ?, ?, NULL, NULL, 0.00, ?, NOW(), NOW(), NOW())'
    );

    if (!$stmt) {
        return [false, 'Booking failed'];
    }

    $stmt->bind_param(
        'siissdissd',
        $bookingNumber,
        $customerId,
        $driverId,
        $startDate,
        $endDate,
        $totalPrice,
        $legacyStatus,
        $bookingStatus,
        $paymentStatus,
        $totalPrice
    );
    $created = $stmt->execute();
    $stmt->close();

    if (!$created) {
        return [false, 'Booking failed'];
    }

    return [true, 'Driver service booked successfully'];
}

function yamu_booking_update_status($conn, $bookingId, $newStatus)
{
    $bookingId = (int) $bookingId;
    $booking = yamu_booking_fetch($conn, $bookingId);

    if (!$booking) {
        return false;
    }

    $newStatus = yamu_booking_normalize_status($newStatus);
    $legacyStatus = yamu_booking_legacy_status_value($newStatus);
    $sql = 'UPDATE booking SET `status` = ?, `booking_status` = ?, `updated_at` = NOW()';

    if ($newStatus === 'cancelled' || $newStatus === 'rejected') {
        $sql .= ', `cancelled_at` = NOW()';
    }

    if ($newStatus === 'completed') {
        $sql .= ', `completed_at` = NOW()';
    }

    $sql .= ' WHERE booking_id = ?';
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('isi', $legacyStatus, $newStatus, $bookingId);
    $updated = $stmt->execute();
    $stmt->close();

    if ($updated && !empty($booking['vehicle_ID'])) {
        yamu_booking_sync_vehicle_availability($conn, (int) $booking['vehicle_ID']);
    }

    return $updated;
}
