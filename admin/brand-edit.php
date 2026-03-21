<?php
    $page_title = "Brands"; 
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
            <h2>Manage Brands</h2>

            <div class="main-cards">
                <div class="card">
                <?php
                    
                    if (isset($_GET['brand_id'])) {
                        $brandId = $_GET['brand_id'];

                        // Retrieve the existing brand data from the database
                        $sql = "SELECT * FROM brands WHERE brand_id = $brandId";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();

                            // Display the update form with pre-filled values
                            ?>
                            <form action="includes/brand-process.php" method="GET" class="update-form" enctype="multipart/form-data">
                                <input type="hidden" name="brandId" value="<?php echo $row['brand_id']; ?>">

                                <div class="form-group">
                                    <label for="brandName">Brand Name:</label>
                                    <input type="text" name="brandName" id="brandName" value="<?php echo $row['brand_name']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="brandName">Brand Status:</label>
                                    <select name="brandStatus" id="brandStatus">
                                        <option selected>
                                            <?php
                                                if($row['brand_status'] === "1") {
                                                    echo "--Active--";
                                                } else {
                                                    echo "--Inactive--";
                                                }
                                            ?>
                                        </option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="brandLogo">Brand Logo:</label>
                                    <input type="file" name="brandLogo" id="brandLogo" value="" class="custom">
                                </div>
                                <input type="submit" value="Update" class="btn main-btn" name="updateBrand">
                            </form>
                            <?php
                        } else {
                            echo "Brand not found.";
                        }
                    }
                    ?>
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