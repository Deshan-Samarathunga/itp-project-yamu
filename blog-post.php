<?php
    $page_title = "Blog Post"; 
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
        <h2>Blog Post</h2>
            <div class="banner-link">
                <a href="index.html">Home</a> &gt; <a href="blog.php">Blogs</a> 
            </div>
    </section>
    
    <!-- Blog Section -->
    <section class="blog blog-psot">
        <div class="container">
            <div class="card">
                <div class="card-img">
                    <img src="assets/images/blog/blog-1.jpg" alt="blog-img">
                </div>
                <div class="card-body">
                    <div class="blog-txt">
                        <span>22 Oct 2018</span>
                        <span>Jack Morgan</hspan5>
                    </div>
                    <h3>Impact Of Extrinsic Motivation On</h3>
                    <p>
                        Lorem, ipsum dolor sit amet consectetur adipisicing elit. Quaerat fuga velit dolorum nobis doloribus soluta nisi alias tempora modi eveniet error aspernatur dolorem magnam non numquam, placeat, unde delectus eligendi.
                        Lorem, ipsum dolor sit amet consectetur adipisicing elit. Quaerat fuga velit dolorum nobis doloribus soluta nisi alias tempora modi eveniet error aspernatur dolorem magnam non numquam, placeat, unde delectus eligendi.
                    </p>
                    <br>
                    <p>
                        Lorem, ipsum dolor sit amet consectetur adipisicing elit. Quaerat fuga velit dolorum nobis doloribus soluta nisi alias tempora modi eveniet error aspernatur dolorem magnam non numquam, placeat, unde delectus eligendi.
                        Lorem, ipsum dolor sit amet consectetur adipisicing elit. Quaerat fuga velit dolorum nobis doloribus soluta nisi alias tempora modi eveniet error aspernatur dolorem magnam non numquam, placeat, unde delectus eligendi.
                        Lorem, ipsum dolor sit amet consectetur adipisicing elit. Quaerat fuga velit dolorum nobis doloribus soluta nisi alias tempora modi eveniet error aspernatur dolorem magnam non numquam, placeat, unde delectus eligendi.
                    </p>
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