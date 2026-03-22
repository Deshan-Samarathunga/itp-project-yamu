<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/driver-ad-management.php';
yamu_start_session();
yamu_require_user_roles(['driver'], '../signin.php', ['active', 'verified'], '../access-denied.php');
include 'config.php';

$driverId = (int) ($_SESSION['user']['user_ID'] ?? 0);
$currentUser = yamu_fetch_user_by_id($conn, $driverId);

if (!$currentUser) {
    yamu_redirect_with_message('../signin.php', 'error', 'Driver account not found');
}

if (isset($_POST['createDriverAd'])) {
    [$payload, $error] = yamu_driver_ad_collect_payload($driverId);

    if ($payload === false) {
        yamu_redirect_with_message('../driver-ad-add.php', 'error', $error);
    }

    [$profileImageName, $photoError] = yamu_driver_profile_image_upload($currentUser['profile_pic'] ?? 'avatar.png');

    if ($profileImageName === false) {
        yamu_redirect_with_message('../driver-ad-add.php', 'error', $photoError);
    }

    if ($profileImageName !== ($currentUser['profile_pic'] ?? 'avatar.png') && !yamu_driver_save_profile_image($conn, $driverId, $profileImageName)) {
        yamu_redirect_with_message('../driver-ad-add.php', 'error', 'Failed to save the driver photo');
    }

    if (!yamu_driver_ad_save($conn, $payload)) {
        yamu_redirect_with_message('../driver-ad-add.php', 'error', 'Failed to create your driver advertisement');
    }

    $updatedUser = yamu_fetch_user_by_id($conn, $driverId);

    if ($updatedUser) {
        yamu_set_user_session($updatedUser, $conn, yamu_current_user_role());
    }

    yamu_redirect_with_message('../driver-ads.php', 'msg', 'Driver advertisement created successfully');
}

if (isset($_POST['updateDriverAd'])) {
    $adId = (int) ($_POST['ad_id'] ?? 0);
    $existingAd = yamu_driver_ad_fetch($conn, $adId);

    if (!$existingAd || (int) ($existingAd['driver_user_id'] ?? 0) !== $driverId) {
        yamu_redirect_with_message('../driver-ads.php', 'error', 'Driver advertisement not found');
    }

    [$payload, $error] = yamu_driver_ad_collect_payload($driverId, $existingAd);

    if ($payload === false) {
        yamu_redirect_with_message('../driver-ad-edit.php?ad_id=' . $adId, 'error', $error);
    }

    [$profileImageName, $photoError] = yamu_driver_profile_image_upload($currentUser['profile_pic'] ?? 'avatar.png');

    if ($profileImageName === false) {
        yamu_redirect_with_message('../driver-ad-edit.php?ad_id=' . $adId, 'error', $photoError);
    }

    if ($profileImageName !== ($currentUser['profile_pic'] ?? 'avatar.png') && !yamu_driver_save_profile_image($conn, $driverId, $profileImageName)) {
        yamu_redirect_with_message('../driver-ad-edit.php?ad_id=' . $adId, 'error', 'Failed to save the driver photo');
    }

    if (!yamu_driver_ad_save($conn, $payload, $adId)) {
        yamu_redirect_with_message('../driver-ad-edit.php?ad_id=' . $adId, 'error', 'Failed to update your driver advertisement');
    }

    $updatedUser = yamu_fetch_user_by_id($conn, $driverId);

    if ($updatedUser) {
        yamu_set_user_session($updatedUser, $conn, yamu_current_user_role());
    }

    yamu_redirect_with_message('../driver-ads.php', 'msg', 'Driver advertisement updated successfully');
}

if (isset($_GET['deleteAd'])) {
    $adId = (int) ($_GET['deleteAd'] ?? 0);
    $existingAd = yamu_driver_ad_fetch($conn, $adId);

    if (!$existingAd || (int) ($existingAd['driver_user_id'] ?? 0) !== $driverId) {
        yamu_redirect_with_message('../driver-ads.php', 'error', 'Driver advertisement not found');
    }

    $stmt = $conn->prepare('DELETE FROM driver_ads WHERE driver_ad_id = ? AND driver_user_id = ? LIMIT 1');

    if (!$stmt) {
        yamu_redirect_with_message('../driver-ads.php', 'error', 'Failed to delete your driver advertisement');
    }

    $stmt->bind_param('ii', $adId, $driverId);
    $deleted = $stmt->execute();
    $stmt->close();

    if (!$deleted) {
        yamu_redirect_with_message('../driver-ads.php', 'error', 'Failed to delete your driver advertisement');
    }

    yamu_redirect_with_message('../driver-ads.php', 'msg', 'Driver advertisement deleted successfully');
}

yamu_redirect('../driver-ads.php');
