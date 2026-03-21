<?php
    require_once __DIR__ . '/auth.php';
    carzo_start_session();
    $signedInUser = carzo_current_user();
    $signedInRole = $signedInUser['active_role'] ?? ($signedInUser['role'] ?? 'customer');
    $signedInRoles = $signedInUser['roles'] ?? [$signedInRole];
    $signedInAdmin = $_SESSION['admin'] ?? null;
    $currentPage = basename($_SERVER['PHP_SELF'] ?? '');
?>
<!-- Top Navbar -->
        <div class="top-nav">
            <div class="container row">
                <div class="contact-info row">
                    <div>
                        <i class="ri-mail-line"></i>
                        <a href="mailto:yamu@contact.com">yamu@contact.com</a>
                    </div>
                    <div>
                        <i class="ri-phone-line"></i>
                        <a href="tel: 123456789">964-622-3903</a>
                    </div>
                </div>
                <div class="social-icon">
                    <a href="#" title="facebook"> 
                        <i class="ri-facebook-fill"></i>
                    </a>
                    <a href="#" title="instagram"> 
                        <i class="ri-instagram-fill"></i>
                    </a>
                    <a href="#" title="twitter"> 
                        <i class="ri-twitter-fill"></i>
                    </a>
                    <a href="#" title="linkedin"> 
                        <i class="ri-linkedin-fill"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <nav id="nav">
            <div class="container">
                <!-- Logo -->
                <a href="index.php" class="">
                    <img class="navbar-brand" src="assets/images/logo/logo-full.png" alt="Yamu logo">
                </a>
                <!-- Navigation -->
                <ul class="navbar" id="navbar">
                    <i class="ri-close-line" onclick="hideMenu()"></i>
                    <li class="navbar-item">
                        <a class="navbar-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a>
                    </li>
                    <li class="navbar-item">
                        <a class="navbar-link <?php echo isset($page_title) && $page_title === 'Car Listing' ? 'active' : ''; ?>" href="car-listing.php">Explore cars</a>
                    </li>
                    <li class="navbar-item">
                        <a class="navbar-link <?php echo in_array($page_title ?? '', ['Explore Drivers', 'Driver Details'], true) ? 'active' : ''; ?>" href="drivers.php">Explore drivers</a>
                    </li>
                    <li class="navbar-item">
                        <a class="navbar-link <?php echo isset($page_title) && $page_title === 'Blog' ? 'active' : ''; ?>" href="blog.php">Blog</a>
                    </li>
                    <li class="navbar-item">
                        <a class="navbar-link <?php echo isset($page_title) && $page_title === 'About' ? 'active' : ''; ?>" href="about.php">About Us</a>
                    </li>
                    <li class="navbar-item">
                        <a class="navbar-link <?php echo isset($page_title) && $page_title === 'Contact' ? 'active' : ''; ?>" href="contact.php">Contact Us</a>
                    </li>
                </ul>
                <ul class="sign-btn">
                    <?php
                        if (carzo_is_admin_authenticated()) {
                            ?>
                                <div class="login-menu subnav">
                                    <span><?php echo carzo_e($signedInAdmin['username'] ?? 'admin'); ?></span>
                                    <div class="avatar">
                                        <img src="<?php echo carzo_e(carzo_profile_avatar_path($signedInAdmin['avatar'] ?? 'avatar.png')); ?>" alt="avatar">
                                    </div>
                                    <ul class="subnav-content">
                                        <li><a href="admin/dashboard.php"><i class="ri-dashboard-line"></i> Admin Dashboard</a></li>
                                        <?php if (carzo_is_user_authenticated()) { ?>
                                            <li><a href="choose-role.php"><i class="ri-user-search-line"></i> Choose Role</a></li>
                                            <li><a href="role-switch.php"><i class="ri-shuffle-line"></i> Role Switch</a></li>
                                        <?php } ?>
                                        <li><a href="admin/users.php"><i class="ri-user-line"></i> Users</a></li>
                                        <li><a href="admin/bookings.php"><i class="ri-bookmark-line"></i> Bookings</a></li>
                                        <li><a href="admin/payments.php"><i class="ri-bank-card-line"></i> Payments</a></li>
                                        <li><a href="admin/promotions.php"><i class="ri-coupon-3-line"></i> Promotions</a></li>
                                        <li class="logout"><a href="admin/loguot.php"><i class="ri-login-box-line"></i> Log Out</a></li>
                                    </ul>
                                </div>
                            <?php
                        } elseif (carzo_is_user_authenticated()) {
                            // Display navbar for authenticated users
                            ?>
                                <div class="login-menu subnav">
                                    <span><?php echo carzo_e($signedInUser['username']); ?> (<?php echo carzo_e(carzo_role_label($signedInRole)); ?>)</span>
                                    <div class="avatar">
                                        <img src="<?php echo carzo_e(carzo_profile_avatar_path($signedInUser['avatar'] ?? 'avatar.png')); ?>" alt="avatar">
                                    </div>
                                    <ul class="subnav-content">
                                        <?php if ($signedInRole === 'driver') { ?>
                                            <li><a href="driver-dashboard.php"><i class="ri-roadster-line"></i> Driver Dashboard</a></li>
                                            <li><a href="driver-ads.php"><i class="ri-article-line"></i> My Tour Ads</a></li>
                                            <li><a href="driver-bookings.php"><i class="ri-bookmark-line"></i> Booking Requests</a></li>
                                            <li><a href="driver-reviews.php"><i class="ri-star-line"></i> Reviews</a></li>
                                            <li><a href="driver-disputes.php"><i class="ri-chat-3-line"></i> Disputes</a></li>
                                            <li><a href="driver-earnings.php"><i class="ri-money-dollar-circle-line"></i> Earnings</a></li>
                                        <?php } ?>
                                        <li><a href="choose-role.php"><i class="ri-user-search-line"></i> Choose Role</a></li>
                                        <li><a href="role-switch.php"><i class="ri-shuffle-line"></i> Role Switch</a></li>
                                        <li><a href="role-activation.php"><i class="ri-user-add-line"></i> Activate Role</a></li>
                                        <li><a href="my-profile.php"><i class="ri-user-line"></i> My Profile</a></li>
                                        <li><a href="edit-profile.php"><i class="ri-user-settings-line"></i> Edit Profile</a></li>
                                        <li><a href="update-password.php"><i class="ri-lock-line"></i> Change Password</a></li>
                                        <?php if (in_array('customer', $signedInRoles, true)) { ?>
                                            <li><a href="customer-profile.php"><i class="ri-user-3-line"></i> Customer Profile</a></li>
                                        <?php } ?>
                                        <?php if (in_array('driver', $signedInRoles, true)) { ?>
                                            <li><a href="driver-profile.php"><i class="ri-steering-2-line"></i> Driver Profile</a></li>
                                        <?php } ?>
                                        <?php if (in_array('staff', $signedInRoles, true)) { ?>
                                            <li><a href="staff-profile.php"><i class="ri-store-2-line"></i> Staff Profile</a></li>
                                        <?php } ?>
                                        <?php if (in_array('admin', $signedInRoles, true)) { ?>
                                            <li><a href="admin-profile.php"><i class="ri-shield-user-line"></i> Admin Profile</a></li>
                                        <?php } ?>
                                        <?php if ($signedInRole === 'customer') { ?>
                                            <li><a href="my-booking.php"><i class="ri-book-line"></i> My Bookings</a></li>
                                            <li><a href="my-reviews.php"><i class="ri-star-line"></i> My Reviews</a></li>
                                            <li><a href="my-disputes.php"><i class="ri-chat-3-line"></i> My Disputes</a></li>
                                            <li><a href="payment-history.php"><i class="ri-bank-card-line"></i> Payments</a></li>
                                            <li><a href="promotions.php"><i class="ri-coupon-3-line"></i> Promotions</a></li>
                                        <?php } ?>
                                        <li class="logout"><a href="logout.php"><i class="ri-login-box-line"></i> Log Out</a></li>
                                    </ul>
                                </div> 
                            <?php
                        } else {
                            echo "<li class='navbar-item'>
                                    <a class='navbar-link' href='signin.php'>Sign In</a>
                                </li>
                                <li class='navbar-item'>
                                    <a href='signup.php' class='btn main-btn'>Sign Up</a>
                                </li>";
                        }
                    ?>
                    
                </ul>
                <i class="ri-menu-3-line" onclick="showMenu()"></i>
            </div>
        </nav> 
