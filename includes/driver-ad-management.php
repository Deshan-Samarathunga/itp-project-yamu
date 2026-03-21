<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/driver-ad-options.php';

function carzo_driver_ad_normalize_availability_status($status)
{
    $status = strtolower(trim((string) $status));
    $allowedStatuses = ['available', 'busy', 'on_request'];

    return in_array($status, $allowedStatuses, true) ? $status : 'available';
}

function carzo_driver_ad_normalize_advertisement_status($status)
{
    $status = strtolower(trim((string) $status));
    $allowedStatuses = ['active', 'paused', 'draft'];

    return in_array($status, $allowedStatuses, true) ? $status : 'draft';
}

function carzo_driver_ad_normalize_contact_preference($preference)
{
    $preference = strtolower(trim((string) $preference));
    $allowedPreferences = ['phone', 'email', 'both'];

    return in_array($preference, $allowedPreferences, true) ? $preference : 'both';
}

function carzo_driver_ad_fetch($conn, $adId)
{
    $stmt = $conn->prepare('SELECT * FROM driver_ads WHERE driver_ad_id = ? LIMIT 1');

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $adId);
    $stmt->execute();
    $result = $stmt->get_result();
    $ad = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $ad ?: null;
}

function carzo_driver_ad_collect_payload($driverUserId, $existingAd = null)
{
    $title = trim((string) ($_POST['ad_title'] ?? ''));
    $tagline = trim((string) ($_POST['tagline'] ?? ''));
    $serviceLocation = trim((string) ($_POST['service_location'] ?? ''));
    $languages = trim((string) ($_POST['languages'] ?? ''));
    $specialties = trim((string) ($_POST['specialties'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $dailyRateInput = trim((string) ($_POST['daily_rate'] ?? ''));
    $experienceYearsInput = trim((string) ($_POST['experience_years'] ?? ''));
    $groupSizeInput = trim((string) ($_POST['max_group_size'] ?? ''));

    if ($title === '') {
        return [false, 'Please enter an advertisement title'];
    }

    if ($serviceLocation === '') {
        return [false, 'Please enter your main service location'];
    }

    if (!carzo_driver_service_location_exists($serviceLocation)) {
        return [false, 'Please select a valid service location'];
    }

    if ($languages === '') {
        return [false, 'Please enter the languages you speak'];
    }

    if (!carzo_driver_language_exists($languages)) {
        return [false, 'Please select a valid language option'];
    }

    if ($description === '') {
        return [false, 'Please enter a short description for travelers'];
    }

    if ($dailyRateInput === '' || !is_numeric($dailyRateInput) || (float) $dailyRateInput <= 0) {
        return [false, 'Please enter a valid daily rate'];
    }

    if ($experienceYearsInput === '' || !is_numeric($experienceYearsInput) || (int) $experienceYearsInput < 0) {
        return [false, 'Please enter valid experience in years'];
    }

    if ($groupSizeInput === '' || !is_numeric($groupSizeInput) || (int) $groupSizeInput <= 0) {
        return [false, 'Please enter a valid maximum group size'];
    }

    return [[
        'driver_user_id' => (int) $driverUserId,
        'ad_title' => $title,
        'tagline' => $tagline,
        'service_location' => $serviceLocation,
        'languages' => $languages,
        'experience_years' => (int) $experienceYearsInput,
        'daily_rate' => (float) $dailyRateInput,
        'max_group_size' => (int) $groupSizeInput,
        'availability_status' => carzo_driver_ad_normalize_availability_status($_POST['availability_status'] ?? ($existingAd['availability_status'] ?? 'available')),
        'advertisement_status' => carzo_driver_ad_normalize_advertisement_status($_POST['advertisement_status'] ?? ($existingAd['advertisement_status'] ?? 'draft')),
        'specialties' => $specialties,
        'description' => $description,
        'contact_preference' => carzo_driver_ad_normalize_contact_preference($_POST['contact_preference'] ?? ($existingAd['contact_preference'] ?? 'both')),
    ], null];
}

function carzo_driver_ad_save($conn, array $payload, $adId = null)
{
    if ($adId !== null) {
        $stmt = $conn->prepare(
            'UPDATE driver_ads
             SET ad_title = ?, tagline = ?, service_location = ?, languages = ?, experience_years = ?, daily_rate = ?, max_group_size = ?,
                 availability_status = ?, advertisement_status = ?, specialties = ?, description = ?, contact_preference = ?, updated_at = NOW()
             WHERE driver_ad_id = ?
             LIMIT 1'
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            'ssssidisssssi',
            $payload['ad_title'],
            $payload['tagline'],
            $payload['service_location'],
            $payload['languages'],
            $payload['experience_years'],
            $payload['daily_rate'],
            $payload['max_group_size'],
            $payload['availability_status'],
            $payload['advertisement_status'],
            $payload['specialties'],
            $payload['description'],
            $payload['contact_preference'],
            $adId
        );
    } else {
        $stmt = $conn->prepare(
            'INSERT INTO driver_ads (
                driver_user_id,
                ad_title,
                tagline,
                service_location,
                languages,
                experience_years,
                daily_rate,
                max_group_size,
                availability_status,
                advertisement_status,
                specialties,
                description,
                contact_preference,
                created_at,
                updated_at
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            'issssidisssss',
            $payload['driver_user_id'],
            $payload['ad_title'],
            $payload['tagline'],
            $payload['service_location'],
            $payload['languages'],
            $payload['experience_years'],
            $payload['daily_rate'],
            $payload['max_group_size'],
            $payload['availability_status'],
            $payload['advertisement_status'],
            $payload['specialties'],
            $payload['description'],
            $payload['contact_preference']
        );
    }

    $saved = $stmt->execute();
    $stmt->close();

    return $saved;
}
