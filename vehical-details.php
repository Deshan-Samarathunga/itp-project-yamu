<?php
$page_title = "Book Your Car";
require_once __DIR__ . '/includes/auth.php';
carzo_start_session();
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
    $vehicleID = isset($_GET['vehicle_id']) ? (int) $_GET['vehicle_id'] : 0;

    // Assuming you have a unique identifier for the record (e.g., $recordId)
    if ($vehicleID) {
        // Retrieve the record from the database based on the identifier
        $sql = "SELECT v.*, u.full_name AS owner_name, u.phone AS owner_phone
                FROM vehicles v
                LEFT JOIN users u ON u.user_id = v.owner_user_id
                WHERE v.vehicle_id = {$vehicleID}
                  AND v.listing_status = 'approved'
                LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $record = mysqli_fetch_assoc($result);
            $isBookable = (($record['availability_status'] ?? 'available') === 'available')
                && !in_array(($record['maintenance_status'] ?? 'good'), ['under maintenance', 'unavailable'], true);
            ?>

            <!-- Page Banner Section -->
            <section class="banner-page">
                <h2>
                    <?php echo carzo_e($record['vehicle_title']); ?>
                </h2>
                <div class="banner-link">
                    <a href="index.php">Home</a> &gt; <a href="car-listing.php">Vehicle Listing</a> &gt; <a href="#">
                        <?php echo carzo_e($record['vehicle_title']); ?>
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
                                        <?php echo carzo_e($record['vehicle_brand']); ?>
                                    </th>
                                    <td>Model:</td>
                                    <th>
                                        <?php echo carzo_e($record['vehicle_title']); ?>
                                    </th>
                                </tr>
                                <tr>
                                    <td>Fuel Type:</td>
                                    <th>
                                        <?php echo carzo_e($record['fuel_type']); ?>
                                    </th>
                                    <td>Year:</td>
                                    <th>
                                        <?php echo carzo_e($record['year']); ?>
                                    </th>
                                </tr>
                                <tr>
                                <td>Transmission:</td>
                                    <th>
                                        <?php echo carzo_e($record['transmission']); ?>
                                    </th>
                                    <td>Engine:</td>
                                    <th>
                                        <?php echo carzo_e($record['engine_capacity']); ?>
                                    </th>
                                </tr>
                                <tr>
                                    <td>Seats:</td>
                                    <th>
                                        <?php echo carzo_e($record['capacity']); ?>
                                    </th>
                                    <td>Location:</td>
                                    <th>
                                        <?php echo carzo_e($record['location']); ?>
                                    </th>
                                </tr>
                                <tr>
                                    <td>Availability:</td>
                                    <th>
                                        <?php echo carzo_e(ucfirst($record['availability_status'])); ?>
                                    </th>
                                    <td>Owner Contact:</td>
                                    <th>
                                        <?php echo carzo_e($record['owner_phone']); ?>
                                    </th>
                                </tr>
                            </table>

                            <h3>Vehicle Description</h3>
                            <p>
                                <?php echo carzo_e($record['vehicle_desc']); ?>
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
                                            <?php echo carzo_e($record['price']); ?>
                                        </h3> <span>/ Day</span></h3>
                                </div>
                                <div class="booking-card-body">
                                    <h3>Booking this car</h3>
                                    <p>
                                        Availability: <strong><?php echo carzo_e(ucfirst($record['availability_status'])); ?></strong><br>
                                        Maintenance: <strong><?php echo carzo_e(ucfirst($record['maintenance_status'])); ?></strong>
                                    </p>
                                    <form action="includes/booking-process.php" method="POST" class="booking-form">
                                        <div class="form-group">
                                            <label for="startDate">Start Date:</label>
                                            <input type="date" name="startDate" id="startDate" required <?php echo $isBookable ? '' : 'disabled'; ?> />
                                        </div>
                                        <div class="form-group">
                                            <label for="endDate">End Date:</label>
                                            <input type="date" name="endDate" id="endDate" required <?php echo $isBookable ? '' : 'disabled'; ?> />
                                        </div>
                                        <div class="form-group row price-lable">
                                            <h4>Total</h4>
                                            <h4 id="priceText"></h4>
                                        </div>

                                        <?php if (!$isBookable) { ?>
                                            <p>This vehicle is not available for booking right now.</p>
                                        <?php } elseif (carzo_is_user_authenticated() && carzo_current_user_role() === 'customer' && ($_SESSION['user']['account_status'] ?? 'active') === 'active') { ?>
                                            <!-- Additional data -->
                                            <input type="hidden" value="<?php echo (int) $_SESSION['user']['user_ID']; ?>" name="userID">
                                            <input type="hidden" id="vehicleID" name="vehicleID" value="<?php echo (int) $record['vehicle_id']; ?>">
                                            <input type="hidden" id="priceInput" name="priceInput">

                                            <div class="form-group">
                                                <input type="submit" value="Book Now" name="booking" class="btn main-btn" style="text-align: center; background: #F57C51;">
                                            </div>
                                        <?php } elseif (carzo_is_admin_authenticated()) { ?>
                                            <p>Admin accounts manage bookings, listings, payments, and disputes from the admin dashboard.</p>
                                            <a href="admin/dashboard.php" class="btn main-btn" style="text-align: center;">Open Admin Dashboard</a>
                                        <?php } elseif (carzo_is_user_authenticated() && carzo_current_user_role() === 'driver') { ?>
                                            <p>Driver accounts can manage listings from the driver dashboard, but bookings must be placed through a customer account.</p>
                                            <a href="driver-dashboard.php" class="btn main-btn" style="text-align: center;">Open Driver Dashboard</a>
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
            ?>
            <section class="banner-page">
                <h2>Vehicle Not Found</h2>
                <div class="banner-link">
                    <a href="index.php">Home</a> &gt; <a href="car-listing.php">Vehicle Listing</a>
                </div>
            </section>
            <section class="cars">
                <div class="container">
                    <p>The selected vehicle is not available.</p>
                    <a href="car-listing.php" class="btn main-btn">Back to listings</a>
                </div>
            </section>
            <?php
        }
    } else {
        ?>
        <section class="banner-page">
            <h2>Vehicle Not Found</h2>
            <div class="banner-link">
                <a href="index.php">Home</a> &gt; <a href="car-listing.php">Vehicle Listing</a>
            </div>
        </section>
        <section class="cars">
            <div class="container">
                <p>Please choose a valid vehicle from the listing page.</p>
                <a href="car-listing.php" class="btn main-btn">Back to listings</a>
            </div>
        </section>
        <?php
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
            if (!startDate || !endDate) {
                return 0;
            }

            const start = new Date(startDate);
            const end = new Date(endDate);
            if (end < start) {
                return 0;
            }

            const timeDiff = end.getTime() - start.getTime();
            const days = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
            return Math.max(1, days); 
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
            document.getElementById('priceText').innerHTML = totalPrice > 0 ? totalPrice : '';

            if (document.getElementById('priceInput')) {
                document.getElementById('priceInput').value = totalPrice > 0 ? totalPrice : '';
            }
        }

        // Add event listener to the form
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');
        if (startDateInput) {
            startDateInput.addEventListener('input', handleFormSubmit);
        }
        if (endDateInput) {
            endDateInput.addEventListener('input', handleFormSubmit);
        }

    </script>
</body>

</html>
