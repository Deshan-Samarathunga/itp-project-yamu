<?php
    $page_title = "Blog"; 
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

    <!-- Blog Section -->
    <section class="blog">
        <div class="container">
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
                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam… <a href="blog-post.php">Read More</a></p>
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
</body>
</html>