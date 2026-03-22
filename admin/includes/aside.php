<?php require_once __DIR__ . '/../../includes/auth.php'; ?>
<?php $adminProfileUrl = !empty($_SESSION['admin']['user_id']) ? 'user-edit.php?user_id=' . (int) $_SESSION['admin']['user_id'] : 'profile-setting.php'; ?>
<aside class="sidenav">
            <!-- Side Navigation -->
            <img class="logo" src="../assets/images/logo/logo-full.png" alt="logo">
            <div class="user-info-box">
                <div class="user-info-pic">
                <img src="../assets/images/uploads/avatar/<?php echo yamu_e($_SESSION['admin']['avatar']) ?>" alt="avatar">
                </div>
                <div class="user-info-title">
                    <h3><?php echo yamu_e($_SESSION['admin']['name']) ?></h3>
                    <span><?php echo ucfirst(yamu_e($_SESSION['admin']['role'] ?? 'admin')); ?></span>
                </div>
            </div>
            <div class="sidenav-list">
                <li class="sidenav-item">
                    <a href="dashboard.php" class="nav-links <?php if ($page_title === 'Dashboard') echo 'active'; ?>">
                        <i class="ri-dashboard-line"></i>
                        Dashboard
                    </a>
                </li>
                <li class="sidenav-item">
                    <div class="nav-links dropdown <?php if ($page_title === 'Brands') echo 'active'; ?>">
                        <i class="ri-file-copy-2-line"></i>
                        Brands
                        <i class="ri-arrow-right-s-line" id="down-icon"></i>
                    </div>
                    <ul class="dropdown-list">
                        <li><a href="brands.php">All Brands</a></li>
                        <li><a href="brand-add.php">Create Brand</a></li>
                    </ul>
                </li>
                <li class="sidenav-item">
                    <div class="nav-links dropdown <?php if ($page_title === 'Vehicles') echo 'active'; ?>">
                        <i class="ri-car-line"></i>
                        Vehicles
                        <i class="ri-arrow-right-s-line" id="down-icon"></i>
                    </div>
                    <ul class="dropdown-list">
                        <li><a href="vehicle.php">All Vehicless</a></li>
                        <li><a href="vehicle-add.php">Post a Vehicle</a></li>
                    </ul>
                </li>
                <li class="sidenav-item">
                    <div class="nav-links dropdown <?php if ($page_title === 'Booking') echo 'active'; ?>">
                        <i class="ri-bookmark-line"></i>
                        Booking
                        <i class="ri-arrow-right-s-line" id="down-icon"></i>
                    </div>
                    <ul class="dropdown-list">
                        <li><a href="bookings.php">All Bookings</a></li>
                        <li><a href="bookings.php?status=confirmed">Confirmed</a></li>
                        <li><a href="bookings.php?status=cancelled">Canceled</a></li>
                    </ul>
                </li>
                <li class="sidenav-item">
                    <div class="nav-links dropdown <?php if (in_array($page_title, ['User Management', 'User Details', 'User Roles', 'Verify User', 'User Status'], true)) echo 'active'; ?>">
                        <i class="ri-user-line"></i>
                        Users
                        <i class="ri-arrow-right-s-line" id="down-icon"></i>
                    </div>
                    <ul class="dropdown-list">
                        <li><a href="users.php">User Management</a></li>
                        <li><a href="user-add.php">Create User</a></li>
                    </ul>
                </li>
                <li class="sidenav-item">
                    <a href="reviews.php" class="nav-links <?php if ($page_title === 'Reviews') echo 'active'; ?>">
                        <i class="ri-star-line"></i>
                        Reviews
                    </a>
                </li>
                <li class="sidenav-item">
                    <a href="disputes.php" class="nav-links <?php if ($page_title === 'Disputes') echo 'active'; ?>">
                        <i class="ri-chat-3-line"></i>
                        Disputes
                    </a>
                </li>
                <li class="sidenav-item">
                    <a href="payments.php" class="nav-links <?php if ($page_title === 'Payments') echo 'active'; ?>">
                        <i class="ri-bank-card-line"></i>
                        Payments
                    </a>
                </li>
                <li class="sidenav-item">
                    <a href="promotions.php" class="nav-links <?php if ($page_title === 'Promotions') echo 'active'; ?>">
                        <i class="ri-coupon-3-line"></i>
                        Promotions
                    </a>
                </li>
                <li class="sidenav-item">
                    <a href="<?php echo $adminProfileUrl; ?>" class="nav-links <?php if ($page_title === 'Profile Setting' || (isset($_GET['user_id']) && !empty($_SESSION['admin']['user_id']) && (int) $_GET['user_id'] === (int) $_SESSION['admin']['user_id'])) echo 'active'; ?>">
                        <i class="ri-user-settings-line"></i>
                        Profile 
                    </a>
                </li>
                <li class="sidenav-item">
                    <a href="loguot.php" class="nav-links">
                        <i class="ri-logout-box-line"></i>
                        Loguot
                    </a>
                </li>
            </div>
        </aside>
