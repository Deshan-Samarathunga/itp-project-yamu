<?php
    include 'includes/config.php'; // Database Connection
    $page_title = "Vehicles-Edit"; 
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
            <h2>Post A Vehicle</h2>

            <div class="main-cards">
                <div class="card">
                    <?php
                    
                        if (isset($_GET['vehicle_id'])) {
                            $vehicleID = $_GET['vehicle_id'];

                            // Retrieve the existing brand data from the database
                            $sql = "SELECT * FROM vehicles WHERE vehicle_id = $vehicleID";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                $row = $result->fetch_assoc();

                            // Display the update form with pre-filled values
                    ?>
                    <form action="includes/vehicle-process.php" method="POST" enctype="multipart/form-data" class="signup-form">
                        <!-- Basic Information -->
                        <p>Basic Info</p>
                        <input type="hidden" name="vehicleId" value="<?php echo $row['vehicle_id']; ?>">
                        <div class="form-group">
                            <label for="vehicleTitle">Vehicle Title:</label>
                            <input type="text" name="vehicleTitle" value="<?php echo $row['vehicle_title']; ?>" id="vehicleTitle" placeholder="Enter Brand Name" required/>
                        </div>
                        <div class="form-group">
                            <label for="vehicleDesc">Vehicle Overview:</label>
                            <textarea name="vehicleDesc" id="vehicleDesc" cols="30" rows="5"><?php echo $row['vehicle_desc']; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="vehicleBrand">Brand: </label>
                            <?php   
                                // Retrieve brands from the database
                                $sql_brand = "SELECT * FROM brands";
                                $sql_brand_run = $conn->query($sql_brand);

                                // Check if there are any brands
                                if ($sql_brand_run->num_rows > 0) {
                                    ?>
                                    <select name="vehicleBrand" id="vehicleBrand">
                                        <option selected value="<?php echo $row['vehicle_brand']; ?>"><?php echo $row['vehicle_brand']; ?></option>
                                        <?php
                                        // Loop through each brand
                                        while($row_brand = $sql_brand_run->fetch_assoc()) {
                                            $brandName = $row_brand['brand_name'];
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
                                <option selected value="<?php echo $row['transmission']; ?>"><?php echo $row['transmission']; ?></option>
                                <option value="Automatic">Automatic</option>
                                <option value="Manual">Manual</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fuelType">Fuel Type: </label>
                            <select name="fuelType" id="fuelType">
                                <option selected value="<?php echo $row['fuel_type']; ?>"><?php echo $row['fuel_type']; ?></option>
                                <option value="Diesel">Diesel</option>
                                <option value="Petrol">Petrol</option>
                                <option value="Electric">Electric</option>
                                <option value="Hybrid">Hybrid</option>
                                <option value="Gas">Gas</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="modelYear">Model Year:</label>
                            <input type="number" name="modelYear" value="<?php echo $row['year']; ?>" id="modelYear" placeholder="Enter Brand Name" required/>
                        </div>
                        <div class="form-group">
                            <label for="engineCap">Engine Capacity (CC):</label>
                            <input type="number" name="engineCap" value="<?php echo $row['engine_capacity']; ?>" id="engineCap" placeholder="Enter engine capacity" required/>
                        </div>
                        <div class="form-group">
                            <label for="capacity">Capacity:</label>
                            <input type="number" name="capacity" value="<?php echo $row['capacity']; ?>" id="capacity" placeholder="Enter number of Seats" required/>
                        </div>
                        <div class="form-group">
                            <label for="price">Price:</label>
                            <input type="number" name="price" value="<?php echo $row['price']; ?>" id="price" placeholder="Enter Price Per Day(in LKR)" required/>
                        </div>

                        <!-- ACCESSORIES -->
                        <p>Accessories</p>
                        <div class="grid-4">
                            <div class="accessories">
                                <input type="checkbox" name="airConditioner" value="1" id="airConditioner" <?php echo ($row['airConditioner'] === '1') ? 'checked' : ''; ?> />
                                <label for="airConditioner">Air Conditioner</label>
                            </div>
                            <div class="accessories">
                                <input type="checkbox" name="powerdoorLocks" value="1" id="powerdoorLocks" <?php echo ($row['powerdoorlocks'] === '1') ? 'checked' : ''; ?>  />
                                <label for="powerdoorLocks">Power Door Locks</label>
                            </div>
                            <div class="accessories">
                                <input type="checkbox" name="antiLockBrakingSystem" value="1" id="antiLockBrakingSystem" <?php echo ($row['antilockbrakingsys'] === '1') ? 'checked' : ''; ?> />
                                <label for="antiLockBrakingSystem">AntiLock Braking System</label>
                            </div>
                            <div class="accessories">
                                <input type="checkbox" name="brakeAssist" value="1" id="brakeAssist" <?php echo ($row['brakeassist'] === '1') ? 'checked' : ''; ?> />
                                <label for="brakeAssist">Brake Assist</label>
                            </div>
                            <div class="accessories">
                                <input type="checkbox" name="powerSteering" value="1" id="powerSteering" <?php echo ($row['powersteering'] === '1') ? 'checked' : ''; ?> />
                                <label for="powerSteering">Power Steering</label>
                            </div>
                            <div class="accessories">
                                <input type="checkbox" name="driverAirbag" value="1" id="driverAirbag" <?php echo ($row['driverairbag'] === '1') ? 'checked' : ''; ?> />
                                <label for="driverAirbag">Driver Airbag</label>
                            </div>
                            <div class="accessories">
                                <input type="checkbox" name="passengerAirbag" value="1" id="passengerAirbag" <?php echo ($row['passengerairbag'] === '1') ? 'checked' : ''; ?> />
                                <label for="passengerAirbag">Passenger Airbag</label>
                            </div>
                            <div class="accessories">
                                <input type="checkbox" name="powerWindows" value="1" id="powerWindows" <?php echo ($row['powerwindow'] === '1') ? 'checked' : ''; ?> />
                                <label for="powerWindows">Power Windows</label>
                            </div>
                            <div class="accessories">
                                <input type="checkbox" name="CDPlayer" value="1" id="CDPlayer" <?php echo ($row['cdplayer'] === '1') ? 'checked' : ''; ?> />
                                <label for="CDPlayer">CD Player</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                                    <label for="vehicleStatus">Vehicle Status:</label>
                                    <select name="vehicleStatus" id="vehicleStatus">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                        <input type="reset" value="Cancel" class="btn second-btn" name="Cancel" id="submit" />
                        <input type="submit" value="Update Vehicle" class="btn main-btn" name="updateVehicle" id="updateVehicle" />
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