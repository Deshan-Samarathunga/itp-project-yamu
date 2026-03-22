<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/driver-ad-management.php';
yamu_start_session();
yamu_require_user_roles(['driver'], 'signin.php', ['active', 'verified'], 'access-denied.php');
include 'includes/config.php';
$page_title = 'Driver Ads';

$driverId = (int) ($_SESSION['user']['user_ID'] ?? 0);
$search = trim((string) ($_GET['search'] ?? ''));
$statusFilter = strtolower(trim((string) ($_GET['status'] ?? '')));
$availabilityFilter = strtolower(trim((string) ($_GET['availability'] ?? '')));
$allowedStatuses = ['active', 'paused', 'draft'];
$allowedAvailabilityStatuses = ['available', 'busy', 'on_request'];

$sql = "SELECT * FROM driver_ads WHERE driver_user_id = {$driverId}";

if ($search !== '') {
    $safeSearch = yamu_escape($conn, $search);
    $sql .= " AND (
        ad_title LIKE '%{$safeSearch}%'
        OR tagline LIKE '%{$safeSearch}%'
        OR service_location LIKE '%{$safeSearch}%'
        OR languages LIKE '%{$safeSearch}%'
        OR specialties LIKE '%{$safeSearch}%'
    )";
}

if (in_array($statusFilter, $allowedStatuses, true)) {
    $sql .= " AND advertisement_status = '" . yamu_escape($conn, $statusFilter) . "'";
}

if (in_array($availabilityFilter, $allowedAvailabilityStatuses, true)) {
    $sql .= " AND availability_status = '" . yamu_escape($conn, $availabilityFilter) . "'";
}

$sql .= ' ORDER BY updated_at DESC, driver_ad_id DESC';
$result = mysqli_query($conn, $sql);
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
                    <h3>My Tour Driver Ads</h3>
                    <p class="profile-intro">Create public advertisements so travelers can discover you for day tours, airport pickups, chauffeur service, and private trips.</p>

                    <form action="" method="GET" class="driver-filter-form manage-filter-form">
                        <input type="text" name="search" value="<?php echo yamu_e($search); ?>" placeholder="Search title, location, language..." />
                        <select name="status">
                            <option value="">All Visibility</option>
                            <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Published</option>
                            <option value="paused" <?php echo $statusFilter === 'paused' ? 'selected' : ''; ?>>Paused</option>
                            <option value="draft" <?php echo $statusFilter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        </select>
                        <select name="availability">
                            <option value="">All Availability</option>
                            <option value="available" <?php echo $availabilityFilter === 'available' ? 'selected' : ''; ?>>Available</option>
                            <option value="on_request" <?php echo $availabilityFilter === 'on_request' ? 'selected' : ''; ?>>On Request</option>
                            <option value="busy" <?php echo $availabilityFilter === 'busy' ? 'selected' : ''; ?>>Busy</option>
                        </select>
                        <button type="submit" class="btn second-btn">Filter</button>
                        <a href="driver-ads.php" class="btn second-btn">Reset</a>
                        <a href="driver-ad-add.php" class="btn main-btn">Create Ad +</a>
                    </form>

                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Advertisement</th>
                                    <th>Location</th>
                                    <th>Daily Rate</th>
                                    <th>Availability</th>
                                    <th>Visibility</th>
                                    <th>Updated</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="table-body">
                                <?php if ($result && mysqli_num_rows($result) > 0) {
                                    while ($ad = mysqli_fetch_assoc($result)) { ?>
                                        <tr>
                                            <td><?php echo (int) $ad['driver_ad_id']; ?></td>
                                            <td>
                                                <strong><?php echo yamu_e($ad['ad_title']); ?></strong>
                                                <?php if (!empty($ad['tagline'])) { ?>
                                                    <br><small><?php echo yamu_e($ad['tagline']); ?></small>
                                                <?php } ?>
                                            </td>
                                            <td><?php echo yamu_e($ad['service_location']); ?></td>
                                            <td>Rs. <?php echo yamu_money($ad['daily_rate']); ?> / day</td>
                                            <td><span class="<?php echo yamu_e(yamu_badge_class($ad['availability_status'])); ?>"><?php echo yamu_e(ucwords(str_replace('_', ' ', $ad['availability_status']))); ?></span></td>
                                            <td><span class="<?php echo yamu_e(yamu_badge_class($ad['advertisement_status'])); ?>"><?php echo yamu_e(ucfirst($ad['advertisement_status'])); ?></span></td>
                                            <td><?php echo yamu_e($ad['updated_at'] ?: $ad['created_at']); ?></td>
                                            <td class="action-cell">
                                                <div class="table-actions">
                                                    <a href="driver-ad-edit.php?ad_id=<?php echo (int) $ad['driver_ad_id']; ?>" class="edit-badge" title="Edit"><i class="ri-pencil-fill"></i></a>
                                                    <?php if (($ad['advertisement_status'] ?? '') === 'active') { ?>
                                                        <a href="driver-details.php?ad_id=<?php echo (int) $ad['driver_ad_id']; ?>" class="edit-badge" title="View"><i class="ri-eye-line"></i></a>
                                                    <?php } ?>
                                                    <a href="includes/driver-ad-process.php?deleteAd=<?php echo (int) $ad['driver_ad_id']; ?>" class="del-badge" title="Delete"><i class="ri-delete-bin-7-fill"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php }
                                } else { ?>
                                    <tr>
                                        <td colspan="8">No driver advertisements found yet.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
