<?php
    $page_title = "Carzo | Book Your Car"; 
    session_start(); // Start the session
    include 'includes/config.php'; // Database Connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        include('includes/header.php');
    ?>
</head>
<body>

    <?php
        include('includes/menu.php');
    ?>

    <!-- Hero Section -->
    <section class="hero">
            <!-- Allert Box -->
            <?php
                include('includes/alert.php');
            ?>
        <div class="container">
            <div class="text-box">
                <h3>Plan your trip now</h3>
                <h1>The <span>best</span> way to get a car</h1>
                <p>If you’re looking for the latest in wireless headphones, look no further. 
                    These are perfect for TV, stereo, home, and cell phone.</p>
                <a href="#" class="btn main-btn">Book Ride</a>
                <a href="#" class="btn second-btn">Book Ride</a>
            </div>
        </div>

    </section>
    <!-- About Section -->
    <section class="about">
        <div class="container">
            <div class="row">
                <div class="col">
                    <div class="about-img">
                        <img src="assets/images/about.jpg" alt="about">
                    </div>
                </div>
                <div class="col">
                    <div class="section-header text-box">
                        <h3>About Us</h3>
                        <h2>More than 150+ special collection cars</h2>
                        <p>Certain but she but shyness why cottage. Guy the put instrument sir entreaties affronting. Pretended exquisite see cordially the you. Weeks quiet do vexed or whose. Motionless if no to affronting imprudence no precaution. My indulged as disposal strongly attended.</p>
                        <a href="#" class="btn main-btn">See all cars</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How it works Section -->
    <section class="how">
        <div class="container">
            <div class="section-header">
                <h3>Helps you to find your next car easily</h3>
                <h2>How it works</h2>
            </div>
            <div class="grid-3">
                <div class="process-item">
                    <h1 class="process-number">1</h1>
                    <h3 class="process-title">Create Account</h3>
                    <p class="process-des">
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna.
                    </p>
                    <div class="process-img">
                        <img src="assets/images/Sign up-pana.png" alt="">
                    </div>
                </div>
                <div class="process-item">
                    <h1 class="process-number">2</h1>
                    <h3 class="process-title">Contact Operator</h3>
                    <p class="process-des">
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna.
                    </p>
                    <div class="process-img">
                        <img src="assets/images/Car rental-pana.png" alt="">
                    </div>
                </div>
                <div class="process-item">
                    <h1 class="process-number">3</h1>
                    <h3 class="process-title">Let’s Drive</h3>
                    <p class="process-des">
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna.
                    </p>
                    <div class="process-img">
                        <img src="assets/images/how-1.png" alt="">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Cars List Section -->
    <section class="cars">
        <div class="container">
            <div class="section-header">
                <h3>Helps you to find your next car easily</h3>
                <h2>Our rental fleet</h2>
            </div>
            <div class="grid-4">
            <?php
                $sql = "SELECT * FROM vehicles";
                $result = mysqli_query($conn, $sql);

                $itemLimit = 6; // Maximum number of items to display
                $itemCount = 0; // Counter variable

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        if ($itemCount >= $itemLimit) {
                            break; // Break the loop after reaching the item limit
                        }
                        ?>
                        <div class="card">
                            <div class="card-img">
                                <div class="card-tag">
                                    <span><?php echo $row['vehicle_brand']; ?></span>
                                </div>
                                <img src="admin/assets/images/uploads/vehicles/<?php echo $row['vImg1']; ?>" alt="car">
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <h4 class="card-title"><?php echo $row['vehicle_title']; ?></h4>
                                    <h5 class="card-title"><?php echo $row['year']; ?></h5>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <p><i class="ri-group-line"></i><?php echo $row['capacity']; ?></p>
                                    </div>
                                    <div class="col">
                                        <p><i class="ri-gas-station-line"></i> <?php echo $row['fuel_type']; ?></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <p><i class="ri-car-line"></i><?php echo $row['year']; ?></p>
                                    </div>
                                    <div class="col">
                                        <p><i class="ri-steering-2-fill"></i><?php echo $row['transmission']; ?></p>
                                    </div>
                                </div>
                                <hr />
                                <div class="row">
                                    <h3>Rs.4500 <span>/ Day</span></h3>
                                    <a href="vehical-details.php?vehicle_id=<?php echo $row['vehicle_id']; ?>" class="btn main-btn">View More</a>
                                </div>
                            </div>
                        </div>
                        <?php
                        $itemCount++; // Increment the counter variable
                    }
                }
            ?>
            </div>
            <a href="#" class="btn main-btn">Show All</a>
        </div>
    </section>

    <!-- Services Section -->
    <section class="service">
        <div class="container">
            <div class="section-header">
                <h3>Our Services</h3>
                <h2>We have best services for the rent cars</h2>
            </div>

            <div class="row">
                <div class="service-tab">
                    <div class="tab-item active" onclick="getService(event, 's1')" id="defaultOpen">
                        <span>01</span>
                        <i class="ri-apps-line"></i>
                        <h3>Mobile App</h3>
                    </div>
                    <div class="tab-item" onclick="getService(event, 's2')">
                        <span>02</span>
                        <i class="ri-gas-station-line"></i>
                        <h3>Fuel Plans</h3>
                    </div>
                    <div class="tab-item" onclick="getService(event, 's3')">
                        <span>03</span>
                        <i class="ri-calendar-2-line"></i>
                        <h3>Long Car Rental</h3>
                    </div>  
                    <div class="tab-item" onclick="getService(event, 's4')">
                        <span>04</span>
                        <i class="ri-map-pin-line"></i>
                        <h3>One-Way Car Rental</h3>
                    </div>
                    <div class="tab-item" onclick="getService(event, 's5')">
                        <span>05</span>
                        <i class="ri-bus-line"></i>
                        <h3>Meetings and Groups</h3>
                    </div>
                    <div class="tab-item" onclick="getService(event, 's6')">
                        <span>06</span>
                        <i class="ri-book-open-line"></i>
                        <h3>Student Car Rental</h3>
                    </div>  
                  </div>

                <div class="service-dec">
                    <div class="tab-content" id="s1">
                        <span>HELPS YOU TO FIND YOUR NEXT CAR EASILY</span>
                        <h2>Manage <span>Rentals</span></h2>
                        <div class="tab-img">
                            <img src="assets/images/services/service_01.jpg">
                        </div>
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Tenetur temporibus veniam natus quia placeat, nemo exercitationem non dolorum qui dolor similique harum illo voluptatem est dolores architecto nobis quis aliquid.</p>
                    </div>
                    <div class="tab-content" id="s2">
                        <span>HELPS YOU TO FIND YOUR NEXT CAR EASILY</span>
                        <h2>Refueling <span>Options</span></h2>
                        <div class="tab-img">
                            <img src="assets/images/services/service_02.jpg">
                        </div>
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Tenetur temporibus veniam natus quia placeat, nemo exercitationem non dolorum qui dolor similique harum illo voluptatem est dolores architecto nobis quis aliquid.</p>
                    </div>
                    <div class="tab-content" id="s3">
                        <span>HELPS YOU TO FIND YOUR NEXT CAR EASILY</span>
                        <h2>Monthly We <span>Car Rental</span></h2>
                        <div class="tab-img">
                            <img src="assets/images/services/service_03.jpg">
                        </div>
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Tenetur temporibus veniam natus quia placeat, nemo exercitationem non dolorum qui dolor similique harum illo voluptatem est dolores architecto nobis quis aliquid.</p>
                    </div>
                    <div class="tab-content" id="s4">
                        <span>HELPS YOU TO FIND YOUR NEXT CAR EASILY</span>
                        <h2>Airport We <span>Rates</span></h2>
                        <div class="tab-img">
                            <img src="assets/images/services/service_04.jpg">
                        </div>
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Tenetur temporibus veniam natus quia placeat, nemo exercitationem non dolorum qui dolor similique harum illo voluptatem est dolores architecto nobis quis aliquid.</p>
                    </div>
                    <div class="tab-content" id="s5">
                        <span>HELPS YOU TO FIND YOUR NEXT CAR EASILY</span>
                        <h2>Groups  <span>Car Rentals</span></h2>
                        <div class="tab-img">
                            <img src="assets/images/services/service_05.jpg">
                        </div>
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Tenetur temporibus veniam natus quia placeat, nemo exercitationem non dolorum qui dolor similique harum illo voluptatem est dolores architecto nobis quis aliquid.</p>
                    </div>
                    <div class="tab-content" id="s6">
                        <span>HELPS YOU TO FIND YOUR NEXT CAR EASILY</span>
                        <h2>Under 25 We <span>Car Rental</span></h2>
                        <div class="tab-img">
                            <img src="assets/images/services/service_06.jpg">
                        </div>
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Tenetur temporibus veniam natus quia placeat, nemo exercitationem non dolorum qui dolor similique harum illo voluptatem est dolores architecto nobis quis aliquid.</p>
                    </div>
                </div>
            </div>
        </div>
    </section> 

    <!-- Testimonial Section -->
    <section class="testimonial">
        <div class="container">
            <div class="section-header">
                <h3>Reviewed by People</h3>
                <h2>Why people love Form </h2>
            </div>

            <div class="grid-3">
                <div class="card">
                    <div class="card-body">
                        <i class="ri-double-quotes-l"></i>
                        <p>Lorem ipsum dolor adipisicing elit. Asperiores quia dicta, perspiciatis provident ut hic illo non dolores odit eveniet.</p>
                        <hr>
                        <div class="avatar">
                            <img src="assets/images/avatar/avatar1.jpg" alt="avatar">
                        </div>
                        <span>Julia Roberts</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <i class="ri-double-quotes-l"></i>
                        <p>Lorem ipsum dolor adipisicing elit. Asperiores quia dicta, perspiciatis provident ut hic illo non dolores odit eveniet.</p>
                        <hr>
                        <div class="avatar">
                            <img src="assets/images/avatar/avatar2.jpg" alt="avatar">
                        </div>
                        <span>Julia Roberts</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <i class="ri-double-quotes-l"></i>
                        <p>Lorem ipsum dolor adipisicing elit. Asperiores quia dicta, perspiciatis provident ut hic illo non dolores odit eveniet.</p>
                        <hr>
                        <div class="avatar">
                            <img src="assets/images/avatar/avatar3.jpg" alt="avatar">
                        </div>
                        <span>Julia Roberts</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Banner Section -->
    <section class="banner">
        <div class="container">
            <div class="row">
                <div class="banner-col">
                    <h1>Save big with our cheap car rental!</h1>
                    <h3>Top Airports. Local Suppliers. 24/7 Support.</h3>
                </div>
                <div class="banner-col">
                    <a href="#" class="btn main-btn">Book Ride</a>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq">
        <div class="container">
            <div class="section-header">
                <h3>FAQ</h3>
                <h2>Frequently Asked Questions</h2>
            </div>

            <div class="row">
                <div class="col">
                    <img src="assets/images/FAQs-pana.png" alt="faq">
                </div>
                <div class="col">
                    <div class="faqs">
                        <details>
                            <summary>What is about rental car deals?</summary>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                        </details>
                        <details>
                            <summary>In which areas do you operate?</summary>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                        </details>
                        <details>
                            <summary>What is about rental car deals?</summary>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                        </details>
                        <details>
                            <summary>What is about rental car deals?</summary>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                        </details>
                        <details>
                            <summary>What is about rental car deals?</summary>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                        </details>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Blog Section -->
    <section class="blog">
        <div class="container">
            <div class="section-header">
                <h3>Our Blogs</h3>
                <h2>Latest News And Insights</h2>
            </div>

            <div class="grid-3">
                <div class="card">
                    <div class="card-img">
                        <img src="assets/images//blog/blog-1.jpg" alt="blog-img">
                    </div>
                    <div class="card-body">
                        <div class="blog-txt">
                            <span>22 Oct 2018</span>
                            <span>Jack Morgan</hspan5>
                        </div>
                        <h3>Impact Of Extrinsic Motivation On</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam… <a href="#">Read More</a></p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-img">
                        <img src="assets/images//blog/blog-2.jpg" alt="blog-img">
                    </div>
                    <div class="card-body">
                        <div class="blog-txt">
                            <span>22 Oct 2018</span>
                            <span>Jack Morgan</hspan5>
                        </div>
                        <h3>Impact Of Extrinsic Motivation On</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam… <a href="#">Read More</a></p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-img">
                        <img src="assets/images//blog/blog-3.jpg" alt="blog-img">
                    </div>
                    <div class="card-body">
                        <div class="blog-txt">
                            <span>22 Oct 2018</span>
                            <span>Jack Morgan</hspan5>
                        </div>
                        <h3>Impact Of Extrinsic Motivation On</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam… <a href="#">Read More</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <?php
        include('includes/footer.php');
    ?>

    <script src="assets/js/main.js"></script>
    <script>
        // Services Tabs ==========================================================================
        function getService(evt, serviceName) {
            // Declare all variables
            var i, tabContent, tabItem;

            // Get all elements with class="tab-content" and hide them
            tabContent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabContent.length; i++) {
                tabContent[i].style.display = "none";
            }

            // Get all elements with class="tablinks" and remove the class "active"
            tabItem = document.getElementsByClassName("tab-item");
            for (i = 0; i < tabItem.length; i++) {
                tabItem[i].className = tabItem[i].className.replace(" active", "");
            }

            // Show the current tab, and add an "active" class to the button that opened the tab
            document.getElementById(serviceName).style.display = "block";
            evt.currentTarget.className += " active";
        }

        // Get the element with id="defaultOpen" and click on it
        document.getElementById("defaultOpen").click();
    </script>

</body>
</html>