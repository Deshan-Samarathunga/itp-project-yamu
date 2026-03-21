<aside class="sidenav">
            <!-- Side Navigation -->
            <img class="logo" src="../assets/images/logo/logo-full.png" alt="logo">
            <div class="user-info-box">
                <div class="user-info-pic">
                <img src="assets/images/avatar/<?php echo $_SESSION['admin']['avatar'] ?>" alt="avatar">
                </div>
                <div class="user-info-title">
                    <h3><?php echo $_SESSION['admin']['name'] ?></h3>
                    <span>Administrator</span>
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
                        <li><a href="booking-confirmed.php">Confirmed</a></li>
                        <li><a href="booking-canceled.php">Canceled</a></li>
                    </ul>
                </li>
                <li class="sidenav-item">
                    <a href="users.php" class="nav-links <?php if ($page_title === 'Users') echo 'active'; ?>">
                        <i class="ri-user-line"></i>
                        Users
                    </a>
                </li>
                <li class="sidenav-item">
                    <a href="profile-setting.php" class="nav-links <?php if ($page_title === 'Profile Setting') echo 'active'; ?>">
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