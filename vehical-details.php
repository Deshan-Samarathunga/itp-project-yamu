<?php
$page_title = "Book Your Car";
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

    <?php

    // Collecting data from query string
    $vehicleID = $_GET['vehicle_id'];

    // Assuming you have a unique identifier for the record (e.g., $recordId)
    if ($vehicleID) {
        // Retrieve the record from the database based on the identifier
        $sql = "SELECT * FROM vehicles WHERE vehicle_id = '$vehicleID'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $record = mysqli_fetch_assoc($result);
            ?>

            <!-- Page Banner Section -->
            <section class="banner-page">
                <h2>
                    <?php echo $record['vehicle_title']; ?>
                </h2>
                <div class="banner-link">
                    <a href="index.html">Home</a> &gt; <a href="car-listing.php">Vehicle Listing</a> &gt; <a href="#">
                        <?php echo $record['vehicle_title']; ?>
                    </a>
                </div>
            </section>
            <?php
                include('includes/alert.php');
            ?>

            <!-- Cars List Section -->
            <section class="car-details">
                <div class="container">
                    <div class="row">
                        <div class="car-col-left">
                            <div class="imgBox">
                                <img src="admin/assets/images/uploads/vehicles/<?php echo $record['vImg1']; ?>" alt="car">
                            </div>
                            <ul class="thumb">
                                <li>
                                    <a href="admin/assets/images/uploads/vehicles/<?php echo $record['vImg1']; ?>"
                                        target="imgBox">
                                        <img src="admin/assets/images/uploads/vehicles/<?php echo $record['vImg1']; ?>"
                                            alt="car">
                                    </a>
                                </li>

                                <li>
                                    <a href="admin/assets/images/uploads/vehicles/<?php echo $record['vImg2']; ?>"
                                        target="imgBox">
                                        <img src="admin/assets/images/uploads/vehicles/<?php echo $record['vImg2']; ?>"
                                            alt="car">
                                    </a>
                                </li>

                                <li>
                                    <a href="admin/assets/images/uploads/vehicles/<?php echo $record['vImg3']; ?>"
                                        target="imgBox">
                                        <img src="admin/assets/images/uploads/vehicles/<?php echo $record['vImg3']; ?>"
                                            alt="car">
                                    </a>
                                </li>
                                <li>
                                    <a href="admin/assets/images/uploads/vehicles/<?php echo $record['vImg4']; ?>"
                                        target="imgBox">
                                        <img src="admin/assets/images/uploads/vehicles/<?php echo $record['vImg4']; ?>"
                                            alt="car">
                                    </a>
                                </li>
                            </ul>

                            <h3>Vehicle Specifications</h3>
                            <table>
                                <tr>
                                    <td>Brand:</td>
                                    <th>
                                        <?php echo $record['vehicle_brand']; ?>
                                    </th>
                                    <td>Model:</td>
                                    <th>
                                        <?php echo $record['vehicle_title']; ?>
                                    </th>
                                </tr>
                                <tr>
                                    <td>Fuel Type:</td>
                                    <th>
                                        <?php echo $record['fuel_type']; ?>
                                    </th>
                                    <td>Year:</td>
                                    <th>
                                        <?php echo $record['year']; ?>
                                    </th>
                                </tr>
                                <tr>
                                <td>Transmission:</td>
                                    <th>
                                        <?php echo $record['transmission']; ?>
                                    </th>
                                    <td>Engine:</td>
                                    <th>
                                        <?php echo $record['engine_capacity']; ?>
                                    </th>
                                </tr>
                                <tr>
                                    <td>Seats:</td>
                                    <th>
                                        <?php echo $record['capacity']; ?>
                                    </th>
                                </tr>
                            </table>

                            <h3>Vehicle Description</h3>
                            <p>
                                <?php echo $record['vehicle_desc']; ?>
                            </p>
                            <br>

                            <h3>Features & Options</h3>
                            <div class="column">
                                <ul>
                                    <li>
                                        <?php
                                        if ($record['airConditioner'] === '1') {
                                            echo "<i class='ri-check-fill'></i>";
                                        } else {
                                            echo "<i class='ri-close-fill'></i>";
                                        }
                                        ?>
                                        Air Conditioner
                                    </li>
                                    <li>
                                        <?php
                                        if ($record['powerdoorlocks'] === '1') {
                                            echo "<i class='ri-check-fill'></i>";
                                        } else {
                                            echo "<i class='ri-close-fill'></i>";
                                        }
                                        ?>
                                        Power Door Locks
                                    </li>
                                    <li>
                                        <?php
                                        if ($record['antilockbrakingsys'] === '1') {
                                            echo "<i class='ri-check-fill'></i>";
                                        } else {
                                            echo "<i class='ri-close-fill'></i>";
                                        }
                                        ?>
                                        AntiLock Braking System
                                    </li>
                                    <li>
                                        <?php
                                        if ($record['powersteering'] === '1') {
                                            echo "<i class='ri-check-fill'></i>";
                                        } else {
                                            echo "<i class='ri-close-fill'></i>";
                                        }
                                        ?>
                                        Power Steering
                                    </li>
                                    <li>
                                        <?php
                                        if ($record['driverairbag'] === '1') {
                                            echo "<i class='ri-check-fill'></i>";
                                        } else {
                                            echo "<i class='ri-close-fill'></i>";
                                        }
                                        ?>
                                        Driver Airbag
                                    </li>
                                </ul>
                                <ul>
                                    <li>
                                        <?php
                                        if ($record['passengerairbag'] === '1') {
                                            echo "<i class='ri-check-fill'></i>";
                                        } else {
                                            echo "<i class='ri-close-fill'></i>";
                                        }
                                        ?>
                                        Passenger Airbag
                                    </li>
                                    <li>
                                        <?php
                                        if ($record['cdplayer'] === '1') {
                                            echo "<i class='ri-check-fill'></i>";
                                        } else {
                                            echo "<i class='ri-close-fill'></i>";
                                        }
                                        ?>
                                        CD Player
                                    </li>
                                    <li>
                                        <?php
                                        if ($record['powerwindow'] === '1') {
                                            echo "<i class='ri-check-fill'></i>";
                                        } else {
                                            echo "<i class='ri-close-fill'></i>";
                                        }
                                        ?>
                                        Power Window
                                    </li>
                                    <li>
                                        <?php
                                        if ($record['brakeassist'] === '1') {
                                            echo "<i class='ri-check-fill'></i>";
                                        } else {
                                            echo "<i class='ri-close-fill'></i>";
                                        }
                                        ?>
                                        Brake Assist
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="car-col-right">
                            <div class="booking-card">
                                <div class="booking-card-title">
                                    <h3>Rs <h3 id="pricePerDay">
                                            <?php echo $record['price']; ?>
                                        </h3> <span>/ Day</span></h3>
                                </div>
                                <div class="booking-card-body">
                                    <h3>Booking this car</h3>
                                    <form action="includes/booking-process.php" method="POST" class="booking-form">
                                        <div class="form-group">
                                            <label for="startDate">Start Date:</label>
                                            <input type="date" name="startDate" id="startDate" required />
                                        </div>
                                        <div class="form-group">
                                            <label for="endDate">End Date:</label>
                                            <input type="date" name="endDate" id="endDate" required />
                                        </div>
                                        <div class="form-group row price-lable">
                                            <h4>Total</h4>
                                            <h4 id="priceText"></h4>
                                        </div>

                                        <?php if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) { ?>
                                            <!-- Additional data -->
                                            <input type="hidden" value="<?php echo $_SESSION['user']['user_ID'] ?>" name="userID">
                                            <input type="hidden" id="vehicleID" name="vehicleID" value="<?php echo $record['vehicle_id']; ?>">
                                            <input type="hidden" id="priceInput" name="priceInput">

                                            <div class="form-group">
                                                <input type="submit" value="Book Now" name="booking" class="btn main-btn" style="text-align: center; background: #F57C51;">
                                            </div>
                                        <?php } else { ?>
                                            <a href="signin.php" class="btn main-btn" style="text-align: center;">Login For Book</a>
                                        <?php } ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </section>

            <?php
        } else {
            echo "No record found.";
        }
    } else {
        echo "Invalid record identifier.";
    }
    ?>



    <!-- Footer Section -->
    <?php
    include('includes/footer.php');
    ?>

    <script src="assets/js/main.js"></script>
    <script>
        // Car images gallery
        document.addEventListener('DOMContentLoaded', function () {
            var thumbLinks = document.querySelectorAll('.thumb a');
            var imgBoxImg = document.querySelector('.imgBox img');

            thumbLinks.forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                });

                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    imgBoxImg.src = this.href;
                });
            });
        });

        // Calculate date and show price

        // Function to calculate the date range in days
        function calculateDateRange(startDate, endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const timeDiff = Math.abs(end.getTime() - start.getTime());
            const days = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
            return days; 
        }

        // Function to handle form submission
        function handleFormSubmit(event) {
            const startDate = document.getElementById("startDate").value;
            const endDate = document.getElementById("endDate").value;

            // Calculate the date range
            const numDaya = calculateDateRange(startDate, endDate);

            // Calculate the price based on the number of days (you can define your pricing logic here)
            const pricePerDay = document.getElementById("pricePerDay").textContent;
            const totalPrice = pricePerDay * numDaya;

            // Display the price
            document.getElementById('priceText').innerHTML = totalPrice;
            document.getElementById('priceInput').value = totalPrice;
        }

        // Add event listener to the form
        const endDateInput = document.getElementById('endDate');
        endDateInput.addEventListener('input', handleFormSubmit);

    </script>
</body>

</html>