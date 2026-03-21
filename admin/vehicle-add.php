<?php
    $page_title = "Vehicles-add"; 
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
            <h2>Post A Vehicle</h2>

            <div class="main-cards">
                <div class="card">
                    <form action="includes/vehicle-process.php" method="POST" enctype="multipart/form-data" class="signup-form">
                        <!-- Basic Information -->
                        <p>Basic Info</p>
                        <div class="form-group">
                            <label for="vehicleTitle">Vehicle Title:</label>
                            <input type="text" name="vehicleTitle" id="vehicleTitle" placeholder="Enter Brand Name" required/>
                        </div>
                        <div class="form-group">
                            <label for="vehicleDesc">Vehicle Overview:</label>
                            <textarea name="vehicleDesc" id="vehicleDesc" cols="30" rows="5"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="vehicleBrand">Brand: </label>
                            <?php   
                                // Retrieve brands from the database
                                $sql = "SELECT * FROM brands";
                                $result = $conn->query($sql);

                                // Check if there are any brands
                                if ($result->num_rows > 0) {
                                    ?>
                                    <select name="vehicleBrand" id="vehicleBrand">
                                        <option selected>--Select a Brand-</option>
                                        <?php
                                        // Loop through each brand
                                        while($row = $result->fetch_assoc()) {
                                            $brandName = $row['brand_name'];
                                            ?>
                                            <option value="<?php echo $brandName; ?>"><?php echo $brandName; ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                    <?php
                                } else {
                                    echo "No brands found.";
                                }
                            ?>
                        </div>
                        <div class="form-group">
                            <label for="transmission">Transmission: </label>
                            <select name="transmission" id="transmission">
                                <option selected>--Select Transmission--</option>
                                <option value="Automatic">Automatic</option>
                                <option value="Manual">Manual</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fuelType">Fuel Type: </label>
                            <select name="fuelType" id="fuelType">
                                <option selected>--Select Fuel Type--</option>
                                <option value="Diesel">Diesel</option>
                                <option value="Petrol">Petrol</option>
                                <option value="Electric">Electric</option>
                                <option value="Hybrid">Hybrid</option>
                                <option value="Gas">Gas</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="modelYear">Model Year:</label>
                            <input type="number" name="modelYear" id="modelYear" placeholder="Enter Brand Name" required/>
                        </div>
                        <div class="form-group">
                            <label for="engineCap">Engine Capacity (CC):</label>
                            <input type="number" name="engineCap" id="engineCap" placeholder="Enter engine capacity" required/>
                        </div>
                        <div class="form-group">
                            <label for="capacity">Capacity:</label>
                            <input type="number" name="capacity" id="capacity" placeholder="Enter number of Seats" required/>
                        </div>
                        <div class="form-group">
                            <label for="price">Price:</label>
                            <input type="number" name="price" id="price" placeholder="Enter Price Per Day(in LKR)" required/>
                        </div>

                        <!-- ACCESSORIES -->
                        <p>Vehicle Features</p>
                        <div class="grid-4">
                            <div class="accessories">
                                <input type="checkbox" name="airConditioner" value="1" id="airConditioner" />
                                <label for="airConditioner">Air Conditioner</label>
                            </div>
                            <div class="accessories">
                                <input type="checkbox" name="powerdoorLocks" value="1" id="powerdoorLocks" />
                                <label for="powerdoorLocks">Power Door Locks</label>
                            </div>
                            <div class="accessories">
                                <input type="checkbox" name="antiLockBrakingSystem" value="1" id="antiLockBrakingSystem" />
                                <label for="antiLockBrakingSystem">AntiLock Braking System</label>
                            </div>
                            <div class="accessories">
                                <input type="checkbox" name="brakeAssist" value="1" id="brakeAssist" />
                                <label for="brakeAssist">Brake Assist</label>
                            </div>
                            <div class="accessories">
                                <input type="checkbox" name="powerSteering" value="1" id="powerSteering" />
                                <label for="powerSteering">Power Steering</label>
                            </div>
                            <div class="accessories">
                                <input type="checkbox" name="driverAirbag" value="1" id="driverAirbag" />
                                <label for="driverAirbag">Driver Airbag</label>
                            </div>
                            <div class="accessories">
                                <input type="checkbox" name="passengerAirbag" value="1" id="passengerAirbag" />
                                <label for="passengerAirbag">Passenger Airbag</label>
                            </div>
                            <div class="accessories">
                                <input type="checkbox" name="powerWindows" value="1" id="powerWindows" />
                                <label for="powerWindows">Power Windows</label>
                            </div>
                            <div class="accessories">
                                <input type="checkbox" name="CDPlayer" value="1" id="CDPlayer" />
                                <label for="CDPlayer">CD Player</label>
                            </div>
                        </div>

                        <!-- ACCESSORIES -->
                        <p>Upload Images</p>
                        <div class="grid-3">
                            <div class="accessories">
                                <label for="vehicleImg1">Image 1:</label>
                                <input type="file" name="vehicleImg1" id="vehicleImg1" required />
                            </div>
                            <div class="accessories">
                                <label for="vehicleImg2">Image 2:</label>
                                <input type="file" name="vehicleImg2" id="vehicleImg2" required />
                            </div>
                            <div class="accessoriesp">
                                <label for="vehicleImg3">Image 3:</label>
                                <input type="file" name="vehicleImg3" id="vehicleImg3" required />
                            </div>
                            <div class="accessories">
                                <label for="vehicleImg4">Image 4:</label>
                                <input type="file" name="vehicleImg4" id="vehicleImg4" required />
                            </div>
                        </div>
                        <input type="reset" value="Cancel" class="btn second-btn" name="Cancel" id="submit" />
                        <input type="submit" value="Submit" class="btn main-btn" name="carSubmit" id="carSubmit" />
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