<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/driver-ad-management.php';
carzo_start_session();
carzo_require_user_roles(['driver'], '../signin.php', ['active', 'pending'], '../index.php');
include 'config.php';

$driverId = (int) ($_SESSION['user']['user_ID'] ?? 0);

if (isset($_POST['createDriverAd'])) {
    [$payload, $error] = carzo_driver_ad_collect_payload($driverId);

    if ($payload === false) {
        carzo_redirect_with_message('../driver-ad-add.php', 'error', $error);
    }

    if (!carzo_driver_ad_save($conn, $payload)) {
        carzo_redirect_with_message('../driver-ad-add.php', 'error', 'Failed to create your driver advertisement');
    }

    carzo_redirect_with_message('../driver-ads.php', 'msg', 'Driver advertisement created successfully');
}

if (isset($_POST['updateDriverAd'])) {
    $adId = (int) ($_POST['ad_id'] ?? 0);
    $existingAd = carzo_driver_ad_fetch($conn, $adId);

    if (!$existingAd || (int) ($existingAd['driver_user_id'] ?? 0) !== $driverId) {
        carzo_redirect_with_message('../driver-ads.php', 'error', 'Driver advertisement not found');
    }

    [$payload, $error] = carzo_driver_ad_collect_payload($driverId, $existingAd);

    if ($payload === false) {
        carzo_redirect_with_message('../driver-ad-edit.php?ad_id=' . $adId, 'error', $error);
    }

    if (!carzo_driver_ad_save($conn, $payload, $adId)) {
        carzo_redirect_with_message('../driver-ad-edit.php?ad_id=' . $adId, 'error', 'Failed to update your driver advertisement');
    }

    carzo_redirect_with_message('../driver-ads.php', 'msg', 'Driver advertisement updated successfully');
}

if (isset($_GET['deleteAd'])) {
    $adId = (int) ($_GET['deleteAd'] ?? 0);
    $existingAd = carzo_driver_ad_fetch($conn, $adId);

    if (!$existingAd || (int) ($existingAd['driver_user_id'] ?? 0) !== $driverId) {
        carzo_redirect_with_message('../driver-ads.php', 'error', 'Driver advertisement not found');
    }

    $stmt = $conn->prepare('DELETE FROM driver_ads WHERE driver_ad_id = ? AND driver_user_id = ? LIMIT 1');

    if (!$stmt) {
        carzo_redirect_with_message('../driver-ads.php', 'error', 'Failed to delete your driver advertisement');
    }

    $stmt->bind_param('ii', $adId, $driverId);
    $deleted = $stmt->execute();
    $stmt->close();

    if (!$deleted) {
        carzo_redirect_with_message('../driver-ads.php', 'error', 'Failed to delete your driver advertisement');
    }

    carzo_redirect_with_message('../driver-ads.php', 'msg', 'Driver advertisement deleted successfully');
}

carzo_redirect('../driver-ads.php');
