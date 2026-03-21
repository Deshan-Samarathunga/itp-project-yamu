<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/driver-ad-management.php';
require_once __DIR__ . '/includes/driver-ad-options.php';
carzo_start_session();
carzo_require_user_roles(['driver'], 'signin.php', ['active', 'pending'], 'index.php');
include 'includes/config.php';
$page_title = 'Driver Ads';
$serviceLocations = carzo_driver_service_locations();
$languageOptions = carzo_driver_language_options();

$adId = isset($_GET['ad_id']) ? (int) $_GET['ad_id'] : 0;
$ad = carzo_driver_ad_fetch($conn, $adId);

if (!$ad || (int) ($ad['driver_user_id'] ?? 0) !== (int) ($_SESSION['user']['user_ID'] ?? 0)) {
    carzo_redirect_with_message('driver-ads.php', 'error', 'Driver advertisement not found');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
</head>
<body>
    <?php include 'includes/menu.php'; ?>

    <section class="profile">
        <?php include 'includes/alert.php'; ?>
        <div class="container">
            <div class="row">
                <?php
                    $currentAccountPage = 'driver-ads';
                    include 'includes/account-sidebar.php';
                ?>
                <div class="profile-details card">
                    <form action="includes/driver-ad-process.php" method="POST" class="signup-form">
                        <h3>Edit Driver Advertisement</h3>
                        <p>Keep your public driver profile up to date so travelers know where you work and how to contact you.</p>

                        <input type="hidden" name="ad_id" value="<?php echo (int) $ad['driver_ad_id']; ?>" />

                        <div class="form-group">
                            <label for="ad_title">Advertisement Title:</label>
                            <input type="text" name="ad_title" id="ad_title" value="<?php echo carzo_e($ad['ad_title']); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="tagline">Short Tagline:</label>
                            <input type="text" name="tagline" id="tagline" value="<?php echo carzo_e($ad['tagline']); ?>" />
                        </div>
                        <div class="form-group">
                            <label for="service_location">Service Location:</label>
                            <select name="service_location" id="service_location" required>
                                <?php if (!empty($ad['service_location']) && !carzo_driver_service_location_exists($ad['service_location'])) { ?>
                                    <option value="<?php echo carzo_e($ad['service_location']); ?>" selected><?php echo carzo_e($ad['service_location']); ?></option>
                                <?php } else { ?>
                                    <option value="">--Select Service Location--</option>
                                <?php } ?>
                                <?php foreach ($serviceLocations as $serviceLocation) { ?>
                                    <option value="<?php echo carzo_e($serviceLocation); ?>" <?php echo $ad['service_location'] === $serviceLocation ? 'selected' : ''; ?>>
                                        <?php echo carzo_e($serviceLocation); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="languages">Languages:</label>
                            <select name="languages" id="languages" required>
                                <?php if (!empty($ad['languages']) && !carzo_driver_language_exists($ad['languages'])) { ?>
                                    <option value="<?php echo carzo_e($ad['languages']); ?>" selected><?php echo carzo_e($ad['languages']); ?></option>
                                <?php } else { ?>
                                    <option value="">--Select Languages--</option>
                                <?php } ?>
                                <?php foreach ($languageOptions as $languageOption) { ?>
                                    <option value="<?php echo carzo_e($languageOption); ?>" <?php echo $ad['languages'] === $languageOption ? 'selected' : ''; ?>>
                                        <?php echo carzo_e($languageOption); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="experience_years">Experience (Years):</label>
                            <input type="number" min="0" name="experience_years" id="experience_years" value="<?php echo (int) $ad['experience_years']; ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="daily_rate">Daily Rate:</label>
                            <input type="number" min="1" step="0.01" name="daily_rate" id="daily_rate" value="<?php echo carzo_e($ad['daily_rate']); ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="max_group_size">Max Group Size:</label>
                            <input type="number" min="1" name="max_group_size" id="max_group_size" value="<?php echo (int) $ad['max_group_size']; ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="availability_status">Availability:</label>
                            <select name="availability_status" id="availability_status">
                                <option value="available" <?php echo $ad['availability_status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                                <option value="on_request" <?php echo $ad['availability_status'] === 'on_request' ? 'selected' : ''; ?>>On Request</option>
                                <option value="busy" <?php echo $ad['availability_status'] === 'busy' ? 'selected' : ''; ?>>Busy</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="advertisement_status">Ad Visibility:</label>
                            <select name="advertisement_status" id="advertisement_status">
                                <option value="active" <?php echo $ad['advertisement_status'] === 'active' ? 'selected' : ''; ?>>Published</option>
                                <option value="paused" <?php echo $ad['advertisement_status'] === 'paused' ? 'selected' : ''; ?>>Paused</option>
                                <option value="draft" <?php echo $ad['advertisement_status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="contact_preference">Preferred Contact:</label>
                            <select name="contact_preference" id="contact_preference">
                                <option value="both" <?php echo $ad['contact_preference'] === 'both' ? 'selected' : ''; ?>>Phone & Email</option>
                                <option value="phone" <?php echo $ad['contact_preference'] === 'phone' ? 'selected' : ''; ?>>Phone Only</option>
                                <option value="email" <?php echo $ad['contact_preference'] === 'email' ? 'selected' : ''; ?>>Email Only</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="specialties">Tour Specialties:</label>
                            <textarea name="specialties" id="specialties" rows="4"><?php echo carzo_e($ad['specialties']); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea name="description" id="description" rows="7" required><?php echo carzo_e($ad['description']); ?></textarea>
                        </div>
                        <a href="driver-ads.php" class="btn second-btn">Cancel</a>
                        <input type="submit" value="Update Advertisement" class="btn main-btn" name="updateDriverAd" />
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
