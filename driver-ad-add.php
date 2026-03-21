<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/driver-ad-options.php';
carzo_start_session();
carzo_require_user_roles(['driver'], 'signin.php', ['active', 'pending'], 'index.php');
include 'includes/config.php';
$page_title = 'Driver Ads';
$serviceLocations = carzo_driver_service_locations();
$languageOptions = carzo_driver_language_options();
$driverPhoto = carzo_profile_avatar_path($_SESSION['user']['avatar'] ?? 'avatar.png');
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
                    <form action="includes/driver-ad-process.php" method="POST" enctype="multipart/form-data" class="signup-form">
                        <h3>Create Driver Advertisement</h3>
                        <p>Publish yourself as a tour driver so travelers can discover your route, rates, and specialties.</p>

                        <div class="driver-photo-upload">
                            <div class="driver-photo-preview">
                                <img src="<?php echo carzo_e($driverPhoto); ?>" alt="Driver photo" id="driverProfilePreview">
                            </div>
                            <div class="driver-photo-copy">
                                <h4>Driver Photo</h4>
                                <p>Upload a clear photo of yourself. This image appears on your public driver card and detail page.</p>
                                <input type="file" name="profileImage" id="profileImage" class="avatar-input" accept="image/*">
                                <label for="profileImage" class="btn second-btn">Upload Driver Photo</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="ad_title">Advertisement Title:</label>
                            <input type="text" name="ad_title" id="ad_title" placeholder="Example: Friendly Kandy Day Tour Driver" required />
                        </div>
                        <div class="form-group">
                            <label for="tagline">Short Tagline:</label>
                            <input type="text" name="tagline" id="tagline" placeholder="Example: Airport pickup, hill country tours, family trips" />
                        </div>
                        <div class="form-group">
                            <label for="service_location">Service Location:</label>
                            <select name="service_location" id="service_location" required>
                                <option value="">--Select Service Location--</option>
                                <?php foreach ($serviceLocations as $serviceLocation) { ?>
                                    <option value="<?php echo carzo_e($serviceLocation); ?>"><?php echo carzo_e($serviceLocation); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="languages">Languages:</label>
                            <select name="languages" id="languages" required>
                                <option value="">--Select Languages--</option>
                                <?php foreach ($languageOptions as $languageOption) { ?>
                                    <option value="<?php echo carzo_e($languageOption); ?>"><?php echo carzo_e($languageOption); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="experience_years">Experience (Years):</label>
                            <input type="number" min="0" name="experience_years" id="experience_years" placeholder="Enter years of tour driving experience" required />
                        </div>
                        <div class="form-group">
                            <label for="daily_rate">Daily Rate:</label>
                            <input type="number" min="1" step="0.01" name="daily_rate" id="daily_rate" placeholder="Enter your daily driver fee" required />
                        </div>
                        <div class="form-group">
                            <label for="max_group_size">Max Group Size:</label>
                            <input type="number" min="1" name="max_group_size" id="max_group_size" placeholder="How many travelers can you handle?" required />
                        </div>
                        <div class="form-group">
                            <label for="availability_status">Availability:</label>
                            <select name="availability_status" id="availability_status">
                                <option value="available">Available</option>
                                <option value="on_request">On Request</option>
                                <option value="busy">Busy</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="advertisement_status">Ad Visibility:</label>
                            <select name="advertisement_status" id="advertisement_status">
                                <option value="active">Publish Now</option>
                                <option value="paused">Pause</option>
                                <option value="draft">Save as Draft</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="contact_preference">Preferred Contact:</label>
                            <select name="contact_preference" id="contact_preference">
                                <option value="both">Phone & Email</option>
                                <option value="phone">Phone Only</option>
                                <option value="email">Email Only</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="specialties">Tour Specialties:</label>
                            <textarea name="specialties" id="specialties" rows="4" placeholder="Example: Cultural tours, tea estate trips, airport transfers, wedding transport"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea name="description" id="description" rows="7" placeholder="Describe your driving style, tour knowledge, what travelers can expect, and why they should book you." required></textarea>
                        </div>
                        <input type="reset" value="Clear" class="btn second-btn" />
                        <input type="submit" value="Create Advertisement" class="btn main-btn" name="createDriverAd" />
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
    <script>
        const driverProfileInput = document.getElementById('profileImage');
        const driverProfilePreview = document.getElementById('driverProfilePreview');

        if (driverProfileInput && driverProfilePreview) {
            driverProfileInput.addEventListener('change', function(event) {
                const file = event.target.files[0];

                if (!file) {
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    driverProfilePreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            });
        }
    </script>
</body>
</html>
