<?php
    $page_title = "About"; 
    session_start(); // Start the session
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

     <!-- Page Banner Section -->
     <section class="banner-page">
        <h2>About Us</h2>
        <div class="banner-link">
                <a href="index.php">Home</a> &gt; <a href="blog.php">About Us</a>
         </div>
    </section>

    <!-- About Section -->
    <section class="about">
        <div class="container">
            <div class="row">
                <div class="section-header text-box">
                        <h3>About Us</h3>
                        <h2>More than 150+ special collection cars</h2>
                        <p>Our fleet of vehicles ranges from the small to the large. All of our vehicles have power steering, electric windows, and air conditioning.
                            Only authorized dealerships are used to purchase and maintain any of our vehicles. In every reservation class, automatic transmission vehicles are available.
                        </p>
                        <br>
                        <p>
                            We are able to offer a range of vehicle makes and models for customers to rent because we are not connected to any one particular automaker.
                            By collaborating with our clients to deliver the best and most efficient Cab Rental solutions and to achieve service excellence,
                            our mission is to be recognized as the global leader in Car Rental for businesses, the public sector, and the private sector.
                        </p>
                        <a href="#" class="btn main-btn">See all cars</a>
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

    <!-- Footer Section -->
    <?php
        include('includes/footer.php');
    ?>

    <script src="assets/js/main.js"></script>
</body>
</html>