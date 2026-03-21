<?php
    $currentUser = $_SESSION['user'];
    $currentRole = $currentUser['active_role'] ?? ($currentUser['role'] ?? 'customer');
    $assignedRoles = $currentUser['roles'] ?? [$currentRole];
    $currentAccountPage = $currentAccountPage ?? '';
    $accountStatusClass = carzo_badge_class($currentUser['account_status'] ?? 'active');
    $verificationClass = carzo_badge_class($currentUser['verification_status'] ?? 'verified');
?>
<div class="profile-card card sticky">
    <div class="avatar">
        <img src="<?php echo carzo_e(carzo_profile_avatar_path($currentUser['avatar'] ?? 'avatar.png')); ?>" alt="avatar">
    </div>
    <h4><?php echo carzo_e($currentUser['name'] ?? ''); ?></h4>
    <span><?php echo carzo_e($currentUser['email'] ?? ''); ?></span>
    <span style="display:block; margin-top: 8px;">
        Active Role: <strong><?php echo carzo_e(carzo_role_label($currentRole)); ?></strong>
    </span>
    <div class="account-meta">
        <span class="<?php echo carzo_e($accountStatusClass); ?>">
            <?php echo carzo_e(ucfirst($currentUser['account_status'] ?? 'active')); ?>
        </span>
        <span class="<?php echo carzo_e($verificationClass); ?>">
            <?php echo carzo_e(ucfirst(str_replace('_', ' ', $currentUser['verification_status'] ?? 'verified'))); ?>
        </span>
    </div>
    <ul class="sidenav-list">
        <?php if (carzo_is_admin_panel_role($currentRole)) { ?>
            <li class="sidenav-item">
                <a href="admin/dashboard.php" class="nav-links">
                    <i class="ri-dashboard-line"></i>
                    Admin Dashboard
                </a>
            </li>
        <?php } ?>
        <?php if ($currentRole === 'driver') { ?>
            <li class="sidenav-item">
                <a href="driver-dashboard.php" class="nav-links <?php echo $currentAccountPage === 'driver-dashboard' ? 'active' : ''; ?>">
                    <i class="ri-roadster-line"></i>
                    Driver Dashboard
                </a>
            </li>
            <li class="sidenav-item">
                <a href="driver-vehicles.php" class="nav-links <?php echo $currentAccountPage === 'driver-vehicles' ? 'active' : ''; ?>">
                    <i class="ri-car-line"></i>
                    My Listings
                </a>
            </li>
            <li class="sidenav-item">
                <a href="driver-bookings.php" class="nav-links <?php echo $currentAccountPage === 'driver-bookings' ? 'active' : ''; ?>">
                    <i class="ri-bookmark-line"></i>
                    Booking Requests
                </a>
            </li>
            <li class="sidenav-item">
                <a href="driver-reviews.php" class="nav-links <?php echo $currentAccountPage === 'driver-reviews' ? 'active' : ''; ?>">
                    <i class="ri-star-line"></i>
                    Reviews
                </a>
            </li>
            <li class="sidenav-item">
                <a href="driver-disputes.php" class="nav-links <?php echo $currentAccountPage === 'driver-disputes' ? 'active' : ''; ?>">
                    <i class="ri-chat-3-line"></i>
                    Disputes
                </a>
            </li>
            <li class="sidenav-item">
                <a href="driver-earnings.php" class="nav-links <?php echo $currentAccountPage === 'driver-earnings' ? 'active' : ''; ?>">
                    <i class="ri-money-dollar-circle-line"></i>
                    Earnings
                </a>
            </li>
        <?php } ?>
        <li class="sidenav-item">
            <a href="my-profile.php" class="nav-links <?php echo $currentAccountPage === 'my-profile' ? 'active' : ''; ?>">
                <i class="ri-user-line"></i>
                My Profile
            </a>
        </li>
        <li class="sidenav-item">
            <a href="edit-profile.php" class="nav-links <?php echo $currentAccountPage === 'edit-profile' ? 'active' : ''; ?>">
                <i class="ri-user-settings-line"></i>
                Edit Profile
            </a>
        </li>
        <li class="sidenav-item">
            <a href="role-switch.php" class="nav-links <?php echo $currentAccountPage === 'role-switch' ? 'active' : ''; ?>">
                <i class="ri-shuffle-line"></i>
                Role Switch
            </a>
        </li>
        <li class="sidenav-item">
            <a href="role-activation.php" class="nav-links <?php echo $currentAccountPage === 'role-activation' ? 'active' : ''; ?>">
                <i class="ri-user-add-line"></i>
                Role Activation
            </a>
        </li>
        <li class="sidenav-item">
            <a href="update-password.php" class="nav-links <?php echo $currentAccountPage === 'password' ? 'active' : ''; ?>">
                <i class="ri-lock-line"></i>
                Change Password
            </a>
        </li>
        <?php if (in_array('customer', $assignedRoles, true)) { ?>
            <li class="sidenav-item">
                <a href="customer-profile.php" class="nav-links <?php echo $currentAccountPage === 'customer-profile' ? 'active' : ''; ?>">
                    <i class="ri-user-3-line"></i>
                    Customer Profile
                </a>
            </li>
        <?php } ?>
        <?php if (in_array('driver', $assignedRoles, true)) { ?>
            <li class="sidenav-item">
                <a href="driver-profile.php" class="nav-links <?php echo $currentAccountPage === 'driver-profile' ? 'active' : ''; ?>">
                    <i class="ri-steering-2-line"></i>
                    Driver Profile
                </a>
            </li>
        <?php } ?>
        <?php if (in_array('staff', $assignedRoles, true)) { ?>
            <li class="sidenav-item">
                <a href="staff-profile.php" class="nav-links <?php echo $currentAccountPage === 'staff-profile' ? 'active' : ''; ?>">
                    <i class="ri-store-2-line"></i>
                    Staff Profile
                </a>
            </li>
        <?php } ?>
        <?php if (in_array('admin', $assignedRoles, true)) { ?>
            <li class="sidenav-item">
                <a href="admin-profile.php" class="nav-links <?php echo $currentAccountPage === 'admin-profile' ? 'active' : ''; ?>">
                    <i class="ri-shield-user-line"></i>
                    Admin Profile
                </a>
            </li>
        <?php } ?>
        <?php if ($currentRole === 'customer') { ?>
            <li class="sidenav-item">
                <a href="my-booking.php" class="nav-links <?php echo $currentAccountPage === 'booking' ? 'active' : ''; ?>">
                    <i class="ri-book-line"></i>
                    My Booking
                </a>
            </li>
            <li class="sidenav-item">
                <a href="my-reviews.php" class="nav-links <?php echo $currentAccountPage === 'reviews' ? 'active' : ''; ?>">
                    <i class="ri-star-line"></i>
                    My Reviews
                </a>
            </li>
            <li class="sidenav-item">
                <a href="my-disputes.php" class="nav-links <?php echo $currentAccountPage === 'disputes' ? 'active' : ''; ?>">
                    <i class="ri-chat-3-line"></i>
                    My Disputes
                </a>
            </li>
            <li class="sidenav-item">
                <a href="payment-history.php" class="nav-links <?php echo $currentAccountPage === 'payments' ? 'active' : ''; ?>">
                    <i class="ri-bank-card-line"></i>
                    Payment History
                </a>
            </li>
            <li class="sidenav-item">
                <a href="promotions.php" class="nav-links <?php echo $currentAccountPage === 'promotions' ? 'active' : ''; ?>">
                    <i class="ri-coupon-3-line"></i>
                    Promotions
                </a>
            </li>
        <?php } ?>
        <li class="sidenav-item">
            <a href="logout.php" class="nav-links">
                <i class="ri-logout-box-line"></i>
                Logout
            </a>
        </li>
    </ul>
</div>
