<!-- Top Navbar -->
        <div class="top-nav">
            <div class="container row">
                <div class="contact-info row">
                    <div>
                        <i class="ri-mail-line"></i>
                        <a href="mailto: abc@example.com">carzo@contact.com</a>
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
                    <img class="navbar-brand" src="assets/images/logo/logo-full.png" alt="logo">
                </a>
                <!-- Navigation -->
                <ul class="navbar" id="navbar">
                    <i class="ri-close-line" onclick="hideMenu()"></i>
                    <li class="navbar-item">
                        <a class="navbar-link <?php echo isset($page_title) && $page_title === 'Carzo | Book Your Car' ? 'active' : ''; ?>" href="index.php">Home</a>
                    </li>
                    <li class="navbar-item">
                        <a class="navbar-link <?php echo isset($page_title) && $page_title === 'Car Listing' ? 'active' : ''; ?>" href="car-listing.php">Explore cars</a>
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
                        if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
                            // Display navbar for authenticated users
                            ?>
                                <div class="login-menu subnav">
                                    <span><?php echo $_SESSION['user']['username']; ?></span>
                                    <div class="avatar">
                                        <img src="assets/images/uploads/avatar/<?php echo $_SESSION['user']['avatar'] ?>" alt="avatar">
                                    </div>
                                    <ul class="subnav-content">
                                        <li><a href="profile.php"><i class="ri-user-line"></i> Profile Setting</a></li>
                                        <li><a href="update-password.php"><i class="ri-lock-line"></i> Change Password</a></li>
                                        <li><a href="my-booking.php"><i class="ri-book-line"></i> My Bookings</a></li>
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