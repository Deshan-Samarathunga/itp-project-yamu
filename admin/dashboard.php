<?php
    include 'includes/config.php'; // Database Connection   
    $page_title = "Dashboard"; 
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
            <!-- Allert Box -->
            <?php
                include('../includes/alert.php');
            ?>
            <h2>Overview</h2>
            <div class="main-overview">
                <div class="overviewcard">
                    <div class="overviewcard-info">
                        <h3>Listed Vehicles</h3>
                        <span>
                            <?php
                                $sql = "SELECT COUNT(*) AS total_vehicles FROM vehicles";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    $row = $result->fetch_assoc();
                                    $totalRows = $row['total_vehicles'];
                                    echo $totalRows; 

                                } else {
                                    echo "0 results";
                                }

                            ?>
                        </span>
                    </div>
                    <div class="overviewcard-icon">
                        <i class="ri-car-line"></i>
                    </div>  
                </div>
                <div class="overviewcard">
                    <div class="overviewcard-info">
                    <h3>Registered Users</h3>
                    <span>
                        <?php
                            $sql = "SELECT COUNT(*) AS total_user_id FROM users";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                $row = $result->fetch_assoc();
                                $totalRows = $row['total_user_id'];
                                echo $totalRows; 

                            } else {
                                echo "0 results";
                            }

                        ?>
                    </span>
                    </div>

                    <div class="overviewcard-icon">
                        <i class="ri-user-line"></i>
                    </div>
                </div>
                <div class="overviewcard">
                    <div class="overviewcard-info">
                        <h3>Listed Brands</h3>
                        <span>
                            <?php
                                $sql = "SELECT COUNT(*) AS total_brands FROM brands";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    $row = $result->fetch_assoc();
                                    $totalRows = $row['total_brands'];
                                    echo $totalRows; 

                                } else {
                                    echo "0 results";
                                }
                            ?>
                        </span>
                    </div>
                    <div class="overviewcard-icon">
                        <i class="ri-file-copy-2-line"></i>
                    </div>
                </div>
                <div class="overviewcard">
                    <div class="overviewcard-info">
                        <h3>Total Bookings</h3>
                        <span>
                        <?php
                                $sql = "SELECT COUNT(*) AS total_booking FROM booking";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    $row = $result->fetch_assoc();
                                    $totalRows = $row['total_booking'];
                                    echo $totalRows; 

                                } else {
                                    echo "0 results";
                                }
                            ?>
                        </span>
                    </div>
                    <div class="overviewcard-icon">
                        <i class="ri-edit-line"></i>
                    </div>
                </div>
                <div class="overviewcard">
                    <div class="overviewcard-info">
                        <h3>Revenue</h3>Rs
                        <span>
                        <?php
                                $sql = "SELECT SUM(total) AS revenue FROM booking";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    $row = $result->fetch_assoc();
                                    $totalRows = $row['revenue'];
                                    echo $totalRows; 

                                } else {
                                    echo "0 results";
                                }
                            ?>
                        </span> 
                    </div>
                    <div class="overviewcard-icon">
                        <i class="ri-money-dollar-circle-line"></i>
                    </div>
                </div>
            </div>
            <!-- <div class="main-cards">
                <div class="card">Card</div>
                <div class="card">Card</div>
                <div class="card">Card</div>
            </div> -->
        </main>

        <footer class="footer">
            <div class="footer__copyright">&copy; 2018 MTH</div>
            <div class="footer__signature">Made with love by pure genius</div>
        </footer>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>