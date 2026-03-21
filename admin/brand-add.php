<?php
    $page_title = "Brands"; 
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

    <div class="grid-container">
        <!-- Navbar -->
        <?php
            include('includes/menu.php');
        ?>

        <!-- Aside Section -->
        <?php
            include('includes/aside.php');
        ?>

        <main class="main">
            <h2>Create Brand</h2>

            <div class="main-cards">
                <div class="card">
                    <form action="includes/brand-process.php" method="POST" enctype="multipart/form-data" class="signup-form">
                        <div class="form-group">
                            <label for="brandName">Brand Name:</label>
                            <input type="text" name="brandName" id="brandName" placeholder="Enter Brand Name" required/>
                        </div>
                        <div class="form-group">
                            <label for="brandLogo">Brand Logo:</label>
                            <input type="file" name="brandLogo" id="brandLogo" class="custom" required/>
                        </div>
                        <input type="submit" value="Submit" class="btn main-btn" name="brandAdd" id="submit"/>
                    </form>
                </div>
            </div>
        </main>

        <footer class="footer">
            <div class="footer__copyright">&copy; 2023 EM</div>
            <div class="footer__signature">Made with love by pure genius</div>
        </footer>
    </div>

    <script src="assets/js/main.js"></script>
    
</body>
</html>